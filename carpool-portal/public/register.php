<?php
session_start();
include_once("../config/db_connect.php");

$name = $email = $password = $gender = $phone = "";
$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $gender = $_POST["gender"];
    $phone = trim($_POST["phone"]);

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($gender) || empty($phone)) {
        $errors[] = "All fields are required.";
    }
    if (!preg_match("/@srmap\.edu\.in$/", $email)) {
        $errors[] = "Email must end with @srmap.edu.in";
    }
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Phone must be 10 digits.";
    }
    if ($gender !== "male" && $gender !== "female") {
        $errors[] = "Gender must be selected.";
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "An account with this email already exists.";
        }
        $stmt->close();
    }

    // Insert user if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, gender, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $hashed_password, $gender, $phone);
        if ($stmt->execute()) {
            $success = "Registration successful! You can now <a href='login.php'>login</a>.";
            $name = $email = $password = $gender = $phone = "";
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Carpool Portal</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="main-container">
    <h2>Register</h2>
    <?php
    if (!empty($errors)) {
        echo '<div style="color: red;">' . implode("<br>", $errors) . '</div>';
    }
    if ($success) {
        echo '<div style="color: green;">' . $success . '</div>';
    }
    ?>
    <form method="POST" action="">
        <label>Name:</label><br>
        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required><br><br>

        <label>Email (@srmap.edu.in):</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Gender:</label><br>
        <select name="gender" required>
            <option value="">Select</option>
            <option value="male" <?php if($gender=="male") echo "selected"; ?>>Male</option>
            <option value="female" <?php if($gender=="female") echo "selected"; ?>>Female</option>
        </select><br><br>

        <label>Phone (10 digits):</label><br>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required><br><br>

        <button type="submit">Register</button>
    </form>
    <br>
    <a href="login.php">Already have an account? Login</a>
    </div>
</body>
</html>