<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="meal_optout_report_' . $date . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV header
fputcsv($output, ['Meal', 'Time', 'Opted Out Roll Number', 'Opted Out Name']);

$sql = "SELECT m.meal_type, m.start_time, m.end_time, u.roll_number, u.full_name
        FROM meal_optouts mo
        JOIN meals m ON mo.meal_id = m.meal_id
        JOIN users u ON mo.user_id = u.user_id
        WHERE mo.date = ? AND mo.status = 'confirmed'
        ORDER BY m.meal_type, u.roll_number";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        ucfirst($row['meal_type']),
        $row['start_time'] . ' - ' . $row['end_time'],
        $row['roll_number'],
        $row['full_name']
    ]);
}

fclose($output);
exit();
?>