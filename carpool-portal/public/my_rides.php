<?php
session_start();
include_once("../config/db_connect.php");

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

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
    <title>My Rides - Carpool Portal</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .route-info { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="main-container">
    <h2>My Rides</h2>
    <a href="index.php">Back to Home</a>
    <hr>

    <!-- Rides Joined as Passenger -->
    <h3>Rides I've Joined</h3>
    <?php
    $sql = "SELECT r.*, u.name AS driver_name, u.phone AS driver_phone, u.email AS driver_email
            FROM ride_passengers rp
            JOIN rides r ON rp.ride_id = r.id
            JOIN users u ON r.driver_id = u.id
            WHERE rp.passenger_id = ? AND rp.status = 'confirmed'
            ORDER BY r.start_time DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        echo "<p>You have not joined any rides as a passenger.</p>";
    } else {
        echo '<table border="1" cellpadding="6">';
        echo '<tr><th>Route</th><th>Date/Time</th><th>Driver</th><th>Contact</th><th>Vehicle</th><th>Distance</th><th>CO₂ Saved</th></tr>';
        while ($row = $result->fetch_assoc()) {
            list($start_name, $lat1, $lon1) = get_location_info($conn, $row['start_location_id']);
            list($end_name, $lat2, $lon2) = get_location_info($conn, $row['end_location_id']);
            $distance = haversine_distance($lat1, $lon1, $lat2, $lon2);

            // Count confirmed passengers for this ride
            $stmt4 = $conn->prepare("SELECT COUNT(*) FROM ride_passengers WHERE ride_id=? AND status='confirmed'");
            $stmt4->bind_param("i", $row['id']);
            $stmt4->execute();
            $stmt4->bind_result($passenger_count);
            $stmt4->fetch();
            $stmt4->close();
            $total_people = 1 + $passenger_count;

            $carbon_saved = calculate_carbon_saved($row['vehicle_type'], $distance, $total_people);

            echo "<tr>";
            echo "<td class='route-info'><strong>" . htmlspecialchars($start_name) . "</strong> <span style='color:#888'>($lat1, $lon1)</span> &rarr; <strong>" . htmlspecialchars($end_name) . "</strong> <span style='color:#888'>($lat2, $lon2)</span></td>";
            echo "<td>" . date("d M Y, h:i A", strtotime($row['start_time'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['driver_name']) . "</td>";
            echo "<td>Phone: " . htmlspecialchars($row['driver_phone']) . "<br>Email: " . htmlspecialchars($row['driver_email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['vehicle_type'] . " - " . $row['vehicle_model'] . " (" . $row['vehicle_number'] . ")") . "</td>";
            echo "<td>{$distance} km</td>";
            echo "<td>{$carbon_saved} kg CO₂</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    $stmt->close();
    ?>

    <hr>
    <!-- Rides Created as Driver -->
    <h3>Rides I've Created (as Driver)</h3>
    <?php
    $sql = "SELECT r.*, sl.name AS start_loc, el.name AS end_loc
            FROM rides r
            JOIN locations sl ON r.start_location_id = sl.id
            JOIN locations el ON r.end_location_id = el.id
            WHERE r.driver_id = ?
            ORDER BY r.start_time DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        echo "<p>You have not created any rides as a driver.</p>";
    } else {
        while ($row = $result->fetch_assoc()) {
            list($start_name, $lat1, $lon1) = get_location_info($conn, $row['start_location_id']);
            list($end_name, $lat2, $lon2) = get_location_info($conn, $row['end_location_id']);
            $distance = haversine_distance($lat1, $lon1, $lat2, $lon2);

            // Count confirmed passengers for this ride
            $stmt4 = $conn->prepare("SELECT COUNT(*) FROM ride_passengers WHERE ride_id=? AND status='confirmed'");
            $stmt4->bind_param("i", $row['id']);
            $stmt4->execute();
            $stmt4->bind_result($passenger_count);
            $stmt4->fetch();
            $stmt4->close();
            $total_people = 1 + $passenger_count;

            $carbon_saved = calculate_carbon_saved($row['vehicle_type'], $distance, $total_people);

            echo "<div style='margin-bottom:20px;'>";
            echo "<strong>Route:</strong> <strong>" . htmlspecialchars($start_name) . "</strong> <span style='color:#888'>($lat1, $lon1)</span> &rarr; <strong>" . htmlspecialchars($end_name) . "</strong> <span style='color:#888'>($lat2, $lon2)</span>";
            echo "<br><strong>Date/Time:</strong> " . date("d M Y, h:i A", strtotime($row['start_time']));
            echo "<br><strong>Vehicle:</strong> " . htmlspecialchars($row['vehicle_type'] . " - " . $row['vehicle_model'] . " (" . $row['vehicle_number'] . ")");
            echo "<br><strong>Distance:</strong> {$distance} km";
            echo "<br><strong>CO₂ Saved:</strong> {$carbon_saved} kg";
            echo "<br><strong>Passengers:</strong><br>";

            // Fetch passengers for this ride
            $sql2 = "SELECT u.name, u.phone, u.email FROM ride_passengers rp
                     JOIN users u ON rp.passenger_id = u.id
                     WHERE rp.ride_id = ? AND rp.status = 'confirmed'";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("i", $row['id']);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            if ($res2->num_rows == 0) {
                echo "&nbsp;&nbsp;No passengers yet.";
            } else {
                echo "<ul>";
                while ($passenger = $res2->fetch_assoc()) {
                    echo "<li>" . htmlspecialchars($passenger['name']) . " | Phone: " . htmlspecialchars($passenger['phone']) . " | Email: " . htmlspecialchars($passenger['email']) . "</li>";
                }
                echo "</ul>";
            }
            $stmt2->close();
            echo "</div><hr>";
        }
    }
    $stmt->close();
    ?>
    <?php include("sos_button.php"); ?>
    </div>
</body>
</html>