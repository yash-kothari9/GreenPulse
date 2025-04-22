<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="refund_summary_' . $month . '_' . $year . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV header
fputcsv($output, ['Roll Number', 'Name', 'Meals Opted Out', 'Total Refund (₹)']);

// Get all students
$students = [];
$sql = "SELECT user_id, roll_number, full_name FROM users WHERE role = 'student'";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// For each student, calculate meals opted out and refund
foreach ($students as $student) {
    $stmt = $conn->prepare(
        "SELECT COUNT(*) as count, COALESCE(SUM(m.base_price),0) as refund
         FROM meal_optouts mo
         JOIN meals m ON mo.meal_id = m.meal_id
         WHERE mo.user_id = ? AND mo.status = 'confirmed'
         AND MONTH(mo.date) = ? AND YEAR(mo.date) = ?"
    );
    $stmt->bind_param("iii", $student['user_id'], $month, $year);
    $stmt->execute();
    $stmt->bind_result($count, $refund);
    $stmt->fetch();
    $stmt->close();

    fputcsv($output, [
        $student['roll_number'],
        $student['full_name'],
        $count,
        number_format($refund, 2)
    ]);
}

fclose($output);
exit();
?>