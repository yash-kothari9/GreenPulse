<?php
session_start();
require_once 'includes/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($email) || empty($password)) {
        $message = "Both fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Fetch user from DB
        $stmt = $conn->prepare("SELECT user_id, full_name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $full_name, $email_db, $password_hash, $role);
            $stmt->fetch();
            if (password_verify($password, $password_hash)) {
                // Login success
                $_SESSION['user_id'] = $user_id;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email_db;
                $_SESSION['role'] = $role;

                // Redirect based on role
                if ($role == 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "No user found with this email.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Meal Opt-out Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="main-container">
        <h2>Login</h2>
        <?php if ($message) echo "<p style='color:#b71c1c;'>$message</p>"; ?>
        <form name="loginForm" method="POST" onsubmit="return validateLoginForm();">
            <label>Email:</label><br>
            <input type="email" name="email" required><br>
            <label>Password:</label><br>
            <input type="password" name="password" required><br>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php" style="color:#2a5298;background:none;padding:0;">Register here</a></p>
    </div>
    <script>
    function validateLoginForm() {
        var email = document.forms["loginForm"]["email"].value.trim();
        var password = document.forms["loginForm"]["password"].value;
        if (!email || !password) {
            alert("Both fields are required!");
            return false;
        }
        if (!/^\S+@\S+\.\S+$/.test(email)) {
            alert("Invalid email format.");
            return false;
        }
        return true;
    }
    </script>
</body>
</html>