<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home - Carpool Portal</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="main-container">
    <h2>Welcome to the Carpool Portal</h2>
    <?php if (isset($_SESSION["user_id"])): ?>
        <p>Hello, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</p>
        <ul>
            <li><a href="create_ride.php">Create a Ride</a></li>
            <li><a href="search_rides.php">Search Rides</a></li> <!-- New link added here -->
            <li><a href="logout.php">Logout</a></li>
            <li><a href="my_rides.php">My Rides</a></li> <!-- New link added here -->
        </ul>
    <?php else: ?>
        <ul>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
    <?php endif; ?>
    </div>
</body>
</html>