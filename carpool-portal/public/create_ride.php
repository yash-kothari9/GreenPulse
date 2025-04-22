<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include_once("../config/db_connect.php");

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Fetch locations for dropdowns
$locations = [];
$result = $conn->query("SELECT id, name FROM locations ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
    $locations[] = $row;
}

$success = $errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $driver_id = $_SESSION["user_id"];
    $vehicle_type = $_POST["vehicle_type"];
    $vehicle_model = trim($_POST["vehicle_model"]);
    $vehicle_number = trim($_POST["vehicle_number"]);
    $passenger_seats = intval($_POST["passenger_seats"]); // Now only passenger seats
    $start_location_id = intval($_POST["start_location_id"]);
    $end_location_id = intval($_POST["end_location_id"]);
    $start_time = $_POST["start_time"];
    $gender = $_SESSION["gender"]; // Enforce gender rule

    // Validation
    if ($vehicle_type !== "2_wheeler" && $vehicle_type !== "4_wheeler") {
        $errors[] = "Invalid vehicle type.";
    }
    if ($start_location_id == $end_location_id) {
        $errors[] = "Start and end locations must be different.";
    }
    if ($vehicle_type == "2_wheeler" && $passenger_seats != 1) {
        $errors[] = "2-wheeler rides can only offer 1 available seat.";
    }
    if ($vehicle_type == "4_wheeler" && ($passenger_seats < 1 || $passenger_seats > 3)) {
        $errors[] = "4-wheeler rides can only offer 1 to 3 available seats.";
    }
    if (empty($vehicle_model) || empty($vehicle_number) || empty($start_time)) {
        $errors[] = "All fields are required.";
    }

    if (empty($errors)) {
        $capacity = ($vehicle_type == "2_wheeler") ? 2 : 4;
        $stmt = $conn->prepare("INSERT INTO rides (driver_id, vehicle_type, vehicle_model, vehicle_number, capacity, passenger_seats, start_location_id, end_location_id, start_time, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "isssiiiiss",
            $driver_id,
            $vehicle_type,
            $vehicle_model,
            $vehicle_number,
            $capacity,
            $passenger_seats,
            $start_location_id,
            $end_location_id,
            $start_time,
            $gender
        );
        if ($stmt->execute()) {
            $success[] = "Ride created successfully!";
        } else {
            echo "SQL Error: " . $stmt->error . "<br>";
            $errors[] = "Failed to create ride. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Ride - Carpool Portal</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
    function updateSeatsField() {
        var type = document.getElementById('vehicle_type').value;
        var seats = document.getElementById('passenger_seats');
        seats.innerHTML = "";
        if (type === "2_wheeler") {
            seats.innerHTML = '<option value="1">1</option>';
        } else if (type === "4_wheeler") {
            seats.innerHTML = '<option value="1">1</option><option value="2">2</option><option value="3">3</option>';
        } else {
            seats.innerHTML = '<option value="">Select vehicle type first</option>';
        }
    }
    </script>
</head>
<body>
<div class="main-container">
    <h2>Create a Ride</h2>
    <?php
    if (!empty($errors)) {
        echo '<div style="color: red;">' . implode("<br>", $errors) . '</div>';
    }
    if (!empty($success)) {
        echo '<div style="color: green;">' . implode("<br>", $success) . '</div>';
    }
    ?>
    <form method="POST" action="">
        <label>Vehicle Type:</label><br>
        <select name="vehicle_type" id="vehicle_type" required onchange="updateSeatsField()">
            <option value="">Select</option>
            <option value="2_wheeler">2 Wheeler</option>
            <option value="4_wheeler">4 Wheeler</option>
        </select><br><br>

        <label>Vehicle Model:</label><br>
        <input type="text" name="vehicle_model" required><br><br>

        <label>Vehicle Number:</label><br>
        <input type="text" name="vehicle_number" required><br><br>

        <label>Available Seats (excluding driver):</label><br>
        <select name="passenger_seats" id="passenger_seats" required>
            <option value="">Select vehicle type first</option>
        </select><br><br>

        <label>Start Location:</label><br>
        <select name="start_location_id" required>
            <option value="">Select</option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?php echo $loc['id']; ?>"><?php echo htmlspecialchars($loc['name']); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>End Location:</label><br>
        <select name="end_location_id" required>
            <option value="">Select</option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?php echo $loc['id']; ?>"><?php echo htmlspecialchars($loc['name']); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Start Time:</label><br>
        <input type="datetime-local" name="start_time" required><br><br>

        <button type="submit">Create Ride</button>
    </form>
    <br>
    <a href="index.php">Back to Home</a>
</div>
    <script>
        // Initialize seats dropdown on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSeatsField();
        });
    </script>
</body>
</html>