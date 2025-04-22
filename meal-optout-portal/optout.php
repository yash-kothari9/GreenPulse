<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meal_id = intval($_POST['meal_id']);
    $date = $_POST['date'];

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        header("Location: dashboard.php?msg=Invalid date format.");
        exit();
    }

    // Fetch meal start time
    $stmt = $conn->prepare("SELECT start_time FROM meals WHERE meal_id = ?");
    $stmt->bind_param("i", $meal_id);
    $stmt->execute();
    $stmt->bind_result($start_time);
    if (!$stmt->fetch()) {
        $stmt->close();
        header("Location: dashboard.php?msg=Invalid meal.");
        exit();
    }
    $stmt->close();

    // Check 6-hour restriction
    $meal_datetime = strtotime($date . ' ' . $start_time);
    if ($meal_datetime - time() <= 21600) { // 6 hours = 21600 seconds
        header("Location: dashboard.php?msg=Opt-out window closed for this meal.");
        exit();
    }

    // Prevent duplicate opt-out
    $stmt = $conn->prepare("SELECT optout_id FROM meal_optouts WHERE user_id = ? AND meal_id = ? AND date = ?");
    $stmt->bind_param("iis", $user_id, $meal_id, $date);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: dashboard.php?msg=Already opted out for this meal.");
        exit();
    }
    $stmt->close();

    // Insert opt-out record
    $stmt = $conn->prepare("INSERT INTO meal_optouts (user_id, meal_id, date, status) VALUES (?, ?, ?, 'confirmed')");
    $stmt->bind_param("iis", $user_id, $meal_id, $date);
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: dashboard.php?msg=Opt-out successful!");
        exit();
    } else {
        $stmt->close();
        header("Location: dashboard.php?msg=Error during opt-out.");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>