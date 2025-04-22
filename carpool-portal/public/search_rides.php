<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once("../config/db_connect.php");

$join_success = "";
$join_error = "";

// Get all locations for dropdowns
$locations = [];
$res = $conn->query("SELECT id, name FROM locations ORDER BY name ASC");
while ($row = $res->fetch_assoc()) {
    $locations[] = $row;
}

// Handle join ride
if (isset($_GET["join_ride_id"])) {
    $ride_id = intval($_GET["join_ride_id"]);
    $user_id = $_SESSION["user_id"];

    // Check if already joined
    $stmt = $conn->prepare("SELECT id FROM ride_passengers WHERE ride_id = ? AND passenger_id = ?");
    $stmt->bind_param("ii", $ride_id, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        $stmt->close();
        // Add passenger
        $stmt2 = $conn->prepare("INSERT INTO ride_passengers (ride_id, passenger_id, status) VALUES (?, ?, 'confirmed')");
        $stmt2->bind_param("ii", $ride_id, $user_id);
        if ($stmt2->execute()) {
            $join_success = "Successfully joined the ride!";
        } else {
            $join_error = "Could not join ride. Try again.";
        }
        $stmt2->close();
    } else {
        $join_error = "You have already joined this ride.";
    }
    $stmt->close();
}

// Search logic
$search_results = [];
$start_location = $end_location = $date = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_location = $_POST["start_location"];
    $end_location = $_POST["end_location"];
    $date = $_POST["date"];
    $sql = "SELECT r.*, u.name AS driver_name, u.phone AS driver_phone, u.email AS driver_email, u.id AS driver_id
            FROM rides r
            JOIN users u ON r.driver_id = u.id
            WHERE r.start_location_id = ? AND r.end_location_id = ? AND DATE(r.start_time) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $start_location, $end_location, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $search_results[] = $row;
    }
    $stmt->close();
}

// Helper functions
function haversine_distance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // in km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return round($earth_radius * $c, 2);
}

function get_location_info($conn, $location_id) {
    $stmt = $conn->prepare("SELECT name, latitude, longitude FROM locations WHERE id=?");
    $stmt->bind_param("i", $location_id);
    $stmt->execute();
    $stmt->bind_result($name, $lat, $lng);
    if ($stmt->fetch()) {
        $stmt->close();
        return [$name, $lat, $lng];
    }
    $stmt->close();
    return [null, null, null];
}

function calculate_carbon_saved($vehicle_type, $distance, $total_people) {
    $factors = [
        "Car" => 0.12,
        "Bike" => 0.08,
        "Auto" => 0.1,
        "Bus" => 0.07
    ];
    $factor = isset($factors[$vehicle_type]) ? $factors[$vehicle_type] : 0.12;
    return round(($factor * $distance) / $total_people, 2);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Rides - Carpool Portal</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="main-container wide-container">
        <h2>Search for a Ride</h2>
        <?php
        if ($join_success) echo '<div class="alert-success">' . $join_success . '</div>';
        if ($join_error) echo '<div class="alert-error">' . $join_error . '</div>';
        ?>
        <form method="POST">
            <label>Start Location:</label><br>
            <select name="start_location" required>
                <option value="">--Select--</option>
                <?php foreach ($locations as $loc): ?>
                    <option value="<?php echo $loc['id']; ?>" <?php if ($start_location == $loc['id']) echo "selected"; ?>>
                        <?php echo htmlspecialchars($loc['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>End Location:</label><br>
            <select name="end_location" required>
                <option value="">--Select--</option>
                <?php foreach ($locations as $loc): ?>
                    <option value="<?php echo $loc['id']; ?>" <?php if ($end_location == $loc['id']) echo "selected"; ?>>
                        <?php echo htmlspecialchars($loc['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Date (optional):</label><br>
            <input type="date" name="date" value="<?php echo isset($date) ? htmlspecialchars($date) : ""; ?>"><br><br>

            <button type="submit">Search</button>
        </form>
        <br>
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <h3>Available Rides</h3>
            <?php if (count($search_results) == 0): ?>
                <p>No rides found for your search.</p>
            <?php else: ?>
                <table border="1" cellpadding="6">
                    <tr>
                        <th>Route</th>
                        <th>Date/Time</th>
                        <th>Driver</th>
                        <th>Contact</th>
                        <th>Vehicle</th>
                        <th>Seats</th>
                        <th>Distance</th>
                        <th>CO₂ Saved</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($search_results as $ride):
                        list($start_name, $lat1, $lon1) = get_location_info($conn, $ride['start_location_id']);
                        list($end_name, $lat2, $lon2) = get_location_info($conn, $ride['end_location_id']);
                        $distance = haversine_distance($lat1, $lon1, $lat2, $lon2);

                        $stmt4 = $conn->prepare("SELECT COUNT(*) FROM ride_passengers WHERE ride_id = ?");
                        $stmt4->bind_param("i", $ride['id']);
                        $stmt4->execute();
                        $stmt4->bind_result($passenger_count);
                        $stmt4->fetch();
                        $stmt4->close();
                        $total_people = 1 + $passenger_count;

                        $carbon_saved = calculate_carbon_saved($ride['vehicle_type'], $distance, $total_people);
                    ?>
                    <tr>
                        <td class='route-info'><strong><?php echo htmlspecialchars($start_name); ?></strong> <span style='color:#888'>(<?php echo $lat1; ?>, <?php echo $lon1; ?>)</span> &rarr; <strong><?php echo htmlspecialchars($end_name); ?></strong> <span style='color:#888'>(<?php echo $lat2; ?>, <?php echo $lon2; ?>)</span></td>
                        <td><?php echo date("d M Y, h:i A", strtotime($ride['start_time'])); ?></td>
                        <td><?php echo htmlspecialchars($ride['driver_name']); ?></td>
                        <td>
                            Phone: <?php echo htmlspecialchars($ride['driver_phone']); ?><br>
                            Email: <?php echo htmlspecialchars($ride['driver_email']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($ride['vehicle_type'] . " - " . $ride['vehicle_model'] . " (" . $ride['vehicle_number'] . ")"); ?>
                        </td>
                        <td><?php echo $ride['passenger_seats']; ?></td>
                        <td><?php echo $distance; ?> km</td>
                        <td><?php echo $carbon_saved; ?> kg CO₂</td>
                        <td>
                            <a href="?join_ride_id=<?php echo $ride['id']; ?>" onclick="return confirm('Join this ride?');">Join Ride</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        <?php endif; ?>
        <?php include("sos_button.php"); ?>
    </div>
</body>
</html>