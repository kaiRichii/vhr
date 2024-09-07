<?php
// Include your database connection file
include 'database.php';

// Variable to store messages for the user
$message = '';
$error_message = ''; // Define the error message variable

// Initialize the email variable
$email = '';

if(isset($_POST['submit'])){
    // $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Not needed if only checking the code
    $code = $_POST['code'];

    // Check if the code matches in the database
    $check_code_query = "SELECT * FROM users WHERE code = ?";
    $check_code_stmt = $conn->prepare($check_code_query);
    $check_code_stmt->bind_param('s', $code);
    $check_code_stmt->execute();
    $check_code_result = $check_code_stmt->get_result();
    $row = $check_code_result->fetch_assoc();

    if($row){
        // If code matches, store the email in a session variable and redirect to changepassword.php
        session_start();
        $_SESSION['email'] = $row['email']; // Store the email in a session variable
        header("Location: changepassword.php");
        exit();
    } else {
        // If the code doesn't match, display an error message
        $error_message = 'Invalid verification code.'; // Set the error message
    }
    $check_code_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code Verification</title>
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
            margin-left: 25px;
        }
        .container {
            max-width: 400px;
            height: 290px;
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
            font-size: 18px;
            margin-left: 25px;
        }
        input[type="text"] {
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
        <h2>Code Verification</h2>
        <?php if(!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <!-- <input type="hidden" name="email" value="<?php echo $email; ?>"> -->
            <label for="code">Verification Code:</label><br>
            <input type="text" id="code" name="code" placeholder="code" required><br>
            <input type="submit" name="submit" value="Verify">
        </form>
    </div>
</body>
</html>
