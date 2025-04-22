<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/db.php';

// Get user info
$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

// Get today's and tomorrow's dates
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Fetch meals
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
    <link rel="stylesheet" href="css/style.css">
    <title>Dashboard - Meal Opt-out Portal</title>
</head>
<body>
    <div class="main-container">
    <?php if (isset($_GET['msg'])) echo "<p>" . htmlspecialchars($_GET['msg']) . "</p>"; ?>
    <h2>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h2>
    <h3>Upcoming Meals</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>Date</th>
            <th>Meal</th>
            <th>Time</th>
            <th>Opt-Out</th>
        </tr>
        <?php
        foreach ([$today, $tomorrow] as $date) {
            foreach ($meals as $meal) {
                // Calculate meal datetime for opt-out restriction
                $meal_start = strtotime($date . ' ' . $meal['start_time']);
                $now = time();
                $can_opt_out = ($meal_start - $now) > 21600; // 6 hours = 21600 seconds

                echo "<tr>";
                echo "<td>" . htmlspecialchars($date) . "</td>";
                echo "<td>" . ucfirst($meal['meal_type']) . "</td>";
                echo "<td>" . htmlspecialchars($meal['start_time']) . " - " . htmlspecialchars($meal['end_time']) . "</td>";
                echo "<td>";
                if ($can_opt_out) {
                    echo "<form method='POST' action='optout.php' style='display:inline;'>
                        <input type='hidden' name='meal_id' value='" . $meal['meal_id'] . "'>
                        <input type='hidden' name='date' value='" . $date . "'>
                        <button type='submit'>Opt Out</button>
                    </form>";
                } else {
                    echo "Opt-out closed";
                }
                echo "</td>";
                echo "</tr>";
            }
        }
        ?>
    </table>
    <br>
    <?php
// Calculate refund summary for current month
$current_month = date('m');
$current_year = date('Y');
$refund_count = 0;
$refund_total = 0.0;

$sql = "SELECT mo.*, m.base_price 
        FROM meal_optouts mo 
        JOIN meals m ON mo.meal_id = m.meal_id 
        WHERE mo.user_id = ? 
          AND mo.status = 'confirmed'
          AND MONTH(mo.date) = ? 
          AND YEAR(mo.date) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $current_month, $current_year);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $refund_count++;
    $refund_total += $row['base_price'];
}
$stmt->close();
?>

<h3>This Month's Opt-Out Summary</h3>
<ul>
    <li>Meals opted out: <strong><?php echo $refund_count; ?></strong></li>
    <li>Estimated refund: <strong>₹<?php echo number_format($refund_total, 2); ?></strong></li>
</ul>
<?php
// Calculate number of children that can be fed
$children_fed = floor($refund_total / 60);
?>

<?php if ($children_fed > 0): ?>
    <div class="banner-sdg">
        <h3>SDG Impact: End Poverty</h3>
        <p>
            With your monthly refund, you could feed <strong style="font-size: 1.5em;"><?php echo $children_fed; ?></strong>
            hungry child<?php echo $children_fed > 1 ? 'ren' : ''; ?> for a day!
        </p>
        <?php if (!isset($_GET['donated'])): ?>
            <form method="get" action="">
                <input type="hidden" name="donated" value="1">
                <button type="submit" style="background:#4caf50;color:white;padding:10px 20px;border:none;border-radius:5px;font-size:1em;cursor:pointer;">
                    Donate my refund to charity
                </button>
            </form>
        <?php else: ?>
            <p style="color: #2e7d32; font-weight: bold;">
                Thank you for your generosity! Your refund will be donated to charity.
            </p>
        <?php endif; ?>
    </div>
<?php endif; ?>
    <a href="logout.php">Logout</a>
</body>
</html>