<?php
session_start();
include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["verify_code"])) {
        // Handle verification code submission
        $verification_code = $_POST["verification_code"];
        
        // Check if the verification code matches the one stored in the database for the user
        $user_id = $_SESSION["user_id"];
        $sql = "SELECT * FROM users WHERE id = ? AND verification_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $verification_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Verification successful
            $update_sql = "UPDATE users SET verified = 1, verification_code = NULL WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user_id);
            $update_stmt->execute();
            
            // Redirect to dashboard or any other page
            $_SESSION['verified'] = true;
            header("Location: index.php");
            exit();
        } else {
            // Verification failed
            $_SESSION['error'] = "Invalid verification code. Please try again.";
            header("Location: email_verification.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            color: #333333;
        }

        p {
            color: #666666;
            line-height: 1.6;
        }

        .btn, .form-input {
            display: block;
            width: fit-content;
            margin: 20px auto 0;
            padding: 10px 20px;
            background-color: #f0ad4e;
            color: white;
            border: none;
            border-radius: 20px;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
        }

        .btn:hover, .form-input:hover {
            background-color: #eea236;
        }

        .form-input {
            background-color: white;
            color: #333333;
            border: 1px solid #cccccc;
            width: 100%;
            max-width: 300px;
            text-align: center;
        }

        .form-input:focus {
            border-color: #f0ad4e;
            outline: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo $_SESSION['error']; ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <form action="email_verification.php" method="post">
            <div class="form-group">
                <label for="verification_code">Verification Code:</label>
                <input type="text" id="verification_code" name="verification_code" required>
            </div>
            <button type="submit" name="verify_code">Verify</button>
        </form>
    </div>
</body>
</html>
