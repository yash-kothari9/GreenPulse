<?php
session_start();
include_once("../config/db_connect.php");

$email = $password = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Validation
    if (empty($email) || empty($password)) {
        $errors[] = "Both fields are required.";
    }
    if (!preg_match("/@srmap\.edu\.in$/", $email)) {
        $errors[] = "Email must end with @srmap.edu.in";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, name, password, gender FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $name, $hashed_password, $gender);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                // Set session variables
                $_SESSION["user_id"] = $id;
                $_SESSION["name"] = $name;
                $_SESSION["email"] = $email;
                $_SESSION["gender"] = $gender; // Ensure gender is set
                header("Location: index.php");
                exit;
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No account found with this email.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Carpool Portal</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="main-container">    
    <h2>Login</h2>
    <?php
    if (!empty($errors)) {
        echo '<div style="color: red;">' . implode("<br>", $errors) . '</div>';
    }
    ?>
    <form method="POST" action="">
        <label>Email (@srmap.edu.in):</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
    <br>
    <a href="register.php">Don't have an account? Register</a>
    </div>
</body>
</html>