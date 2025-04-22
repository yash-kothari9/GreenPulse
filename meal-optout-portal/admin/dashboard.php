<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch all meals
$meals = [];
$sql = "SELECT * FROM meals";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $meals[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Meal Opt-out Portal</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="main-container">
        <h2>Admin Dashboard</h2>
        <form method="get" action="">
            <label for="date">Select Date:</label>
            <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($selected_date); ?>">
            <button type="submit">View</button>
        </form>
        <form method="get" action="export_report.php" style="margin-top:10px;">
            <input type="hidden" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
            <button type="submit">Download CSV Report</button>
        </form>
        <form method="get" action="export_refund_summary.php" style="margin-top:10px;">
            <label for="month">Month:</label>
            <select name="month" id="month">
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $selected = ($m == intval(date('m'))) ? 'selected' : '';
                    echo "<option value='$m' $selected>" . date('F', mktime(0,0,0,$m,1)) . "</option>";
                }
                ?>
            </select>
            <label for="year">Year:</label>
            <select name="year" id="year">
                <?php
                $currentYear = intval(date('Y'));
                for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++) {
                    $selected = ($y == $currentYear) ? 'selected' : '';
                    echo "<option value='$y' $selected>$y</option>";
                }
                ?>
            </select>
            <button type="submit">Download Refund Summary</button>
        </form>
        <br>
        <?php
        foreach ($meals as $meal) {
            echo "<h3>" . ucfirst($meal['meal_type']) . " (" . htmlspecialchars($meal['start_time']) . " - " . htmlspecialchars($meal['end_time']) . ")</h3>";

            // Count opted-out students
            $stmt = $conn->prepare("SELECT u.roll_number, u.full_name FROM meal_optouts mo JOIN users u ON mo.user_id = u.user_id WHERE mo.meal_id = ? AND mo.date = ? AND mo.status = 'confirmed'");
            $stmt->bind_param("is", $meal['meal_id'], $selected_date);
            $stmt->execute();
            $result = $stmt->get_result();
            $opted_out_students = [];
            while ($row = $result->fetch_assoc()) {
                $opted_out_students[] = $row;
            }
            $stmt->close();

            // Count total students
            $result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'student'");
            $total_students = $result->fetch_assoc()['total'];

            $opted_out_count = count($opted_out_students);
            $opted_in_count = $total_students - $opted_out_count;

            echo "<table>";
            echo "<tr><th>Opted In</th><th>Opted Out</th></tr>";
            echo "<tr><td>$opted_in_count</td><td>$opted_out_count</td></tr>";
            echo "</table>";

            if ($opted_out_count > 0) {
                echo "<details><summary style='margin-bottom:8px;'>View Opted-out Students</summary>";
                echo "<ul style='margin-bottom:18px;'>";
                foreach ($opted_out_students as $student) {
                    echo "<li>" . htmlspecialchars($student['roll_number']) . " - " . htmlspecialchars($student['full_name']) . "</li>";
                }
                echo "</ul></details>";
            }
        }
        ?>
        <a href="../logout.php" class="logout-link">Logout</a>
    </div>
</body>
</html>