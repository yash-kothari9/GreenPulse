<?php
session_start();
require_once 'includes/db.php';

$message = "";

function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_valid_roll($roll) {
    return preg_match('/^[A-Za-z0-9]{4,}$/', $roll); // At least 4 alphanumeric characters
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $roll_number = trim($_POST['roll_number']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Server-side validation
    if (
        empty($roll_number) || empty($full_name) ||
        empty($email) || empty($password)
    ) {
        $message = "All fields are required!";
    } elseif (!is_valid_roll($roll_number)) {
        $message = "Roll number must be at least 4 alphanumeric characters.";
    } elseif (!is_valid_email($email)) {
        $message = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } else {
        $password_hashed = password_hash($password, PASSWORD_BCRYPT);

        // Check if email or roll_number already exists
        $check = $conn->query("SELECT * FROM users WHERE email='$email' OR roll_number='$roll_number'");
        if ($check->num_rows > 0) {
            $message = "Email or Roll Number already registered!";
        } else {
            $sql = "INSERT INTO users (roll_number, full_name, email, password) VALUES ('$roll_number', '$full_name', '$email', '$password_hashed')";
            if ($conn->query($sql) === TRUE) {
                $message = "Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $message = "Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Meal Opt-out Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="main-container">
        <h2>Student Registration</h2>
        <?php if ($message) echo "<p style='color:#b71c1c;'>$message</p>"; ?>
        <form name="regForm" method="POST" onsubmit="return validateForm();">
            <label>Roll Number:</label><br>
            <input type="text" name="roll_number" required><br>
            <label>Full Name:</label><br>
            <input type="text" name="full_name" required><br>
            <label>Email:</label><br>
            <input type="email" name="email" required><br>
            <label>Password:</label><br>
            <input type="password" name="password" required><br>
            <button type="submit">Register</button>
        </form>
        <p>Already registered? <a href="login.php" style="color:#2a5298;background:none;padding:0;">Login here</a></p>
    </div>
    <script>
    function validateForm() {
        var roll = document.forms["regForm"]["roll_number"].value.trim();
        var name = document.forms["regForm"]["full_name"].value.trim();
        var email = document.forms["regForm"]["email"].value.trim();
        var password = document.forms["regForm"]["password"].value;
        if (!roll || !name || !email || !password) {
            alert("All fields are required!");
            return false;
        }
        if (!/^[A-Za-z0-9]{4,}$/.test(roll)) {
            alert("Roll number must be at least 4 alphanumeric characters.");
            return false;
        }
        if (!/^\S+@\S+\.\S+$/.test(email)) {
            alert("Invalid email format.");
            return false;
        }
        if (password.length < 6) {
            alert("Password must be at least 6 characters.");
            return false;
        }
        return true;
    }
    </script>
</body>
</html>