<?php
session_start();
include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["reset_password"])) {
        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];

        if ($new_password == $confirm_password) {
            // Reset the password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $username = $_SESSION['username'];

            $sql = "UPDATE users SET password = ? WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $hashed_password, $username);
            if ($stmt->execute()) {
                $success = "Password reset successful";
                // Clear the verification code and username from the session
                unset($_SESSION['verification_code']);
                unset($_SESSION['username']);
            } else {
                $error = "Error resetting password: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Passwords do not match";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>
    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p style='color: green;'>$success</p>"; ?>
    <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
        <label>New Password:</label><br>
        <input type="password" name="new_password" required><br>
        <label>Confirm Password:</label><br>
        <input type="password" name="confirm_password" required><br>
        <button type="submit" name="reset_password">Reset Password</button>
    </form>
</body>
</html>
