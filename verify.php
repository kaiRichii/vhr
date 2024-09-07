<?php
session_start();
include('database.php');
$verification_status = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['code'])) {
        $code = $_POST['code'];

        $sql = "UPDATE users SET verified = 1 WHERE verification_code = ? AND verified = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $code);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $verification_status = "Email verified successfully. You can now <a href='index.php'>login</a>.";
        } else {
            $verification_status = "Invalid verification code or email already verified.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
        <h1>Email Verification</h1>
        <p><?php echo $verification_status; ?></p>
        <form method="POST" action="verify.php">
            <input type="text" name="code" class="form-input" placeholder="Enter verification code" required>
            <button type="submit" class="btn">Verify Email</button>
        </form>
        <a href="index.php" class="btn">Back to Home</a>
    </div>
</body>
</html>
