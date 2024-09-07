<?php
// Include your database connection file
include 'database.php';

// Variable to store messages for the user
$message = '';
$error_message = ''; // Define the error message variable

// Retrieve the email address from the session variable
session_start();
$email = $_SESSION['email'];

if(isset($_POST['submit'])){
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if($password !== $confirm_password){
        $error_message = 'Passwords do not match.';
    } else {
        // Check if password meets the criteria for a strong password
        if(strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)){
            $error_message = 'Password should be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.';
        } else {
            // Update the password in the database
            $update_password = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_password->bind_param("ss", $hashed_password, $email); // Bind parameters
            if($update_password->execute()){ // Execute without arguments
                $message = 'Password changed successfully.';
            } else {
                $error_message = 'Failed to change password. Please try again.';
            }
            $update_password->close(); // Close the prepared statement
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link href="style.css" rel="stylesheet" type="text/css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" 
    integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" 
    crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            /* background-color: #f4f4f4; */
            margin: 0;
            padding: 0;
        }
        h2{
            width: fit-content;
            margin: 0 auto;
            font-size: 18px;
            margin-bottom: 25px;
        }
        .container {
            max-width: 400px;
            height: 400px;
            margin: 0 auto;
            margin-top: 150px;
            padding: 20px;
            border-radius: 5px;
            backdrop-filter: blur(4px);
            background-color: #f7f7f7; 
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.3); 
        }
        .message {
            color: green; /* Change color to indicate success */
            margin-bottom: 10px;
        }
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
        label {
            width: fit-content;
            display: block;
            /* font-size: 18px; */
            margin-left: 25px;
        }
        input[type="password"] {
            display: block;
            width: 310px;
            height: 50px;
            padding: 0 15px;
            border: 1px solid #262525;
            border-radius: 6px;
            margin: 0 auto;
            margin-top: 0px;
            outline: none;
        }
        input[type="submit"] {
            border: none;
            width: 310px;
            height: 50px;
            padding: 10px;
            border-radius: 25px;
            background-color: #262525;
            color: white;
            display: block;
            margin: 0 auto;
            font-size: large;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
        <h2>Change Password</h2>
        <?php if(!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <div id="error_message" class="error-message" style="display: none;"></div>
        <form action="" method="post" onsubmit="return validateForm()">
            <input type="hidden" name="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">

            <label for="password">New Password:</label><br>
            <input type="password" id="password" oninput="checkPasswordStrength(this.value)" name="password" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number."><br>
            <div id="passwordError" style="color: red;"></div>
            <label for="confirm_password">Confirm Password:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required><br>
            <input type="submit" name="submit" value="Confirm">
        </form>
    </div>

    <script>
        // JavaScript validation function
    </script>

    <?php
    // Redirect to user_login.php after successful password change
    if(!empty($message)) {
        header("Location: index.php");
        exit;
    }
    ?>
</body>
</html>