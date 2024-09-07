<?php
session_start();
include('database.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the user's information
$sql = "SELECT username, email FROM users WHERE id='$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST["name"];
        $email = $_POST["email"];
        $message = $_POST["message"];

        // Fetch the administrator's email from your database or configuration
        $admin_email = 'geraldgeonzon@gmail.com'; // Replace this with the actual admin email

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'tessnarval11@gmail.com'; // Your Gmail email address
            $mail->Password   = 'swtw itkl hpuz oqvw';  // Your Gmail password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom($email, $name);
            $mail->addAddress($admin_email); // Recipient email address

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Contact Form Submission';
            $mail->Body    = "Name: $name<br>Email: $email<br>Message: $message";

            // Send email
            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    // Display the banned message
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #1a1a1a;
                color: #fff;
                padding: 20px;
                text-align: center;
            }
            h1 {
                color: #dc3545;
                font-size: 2rem;
                margin-bottom: 20px;
            }
            p {
                margin-bottom: 20px;
            }
            .btn {
                display: inline-block;
                padding: 8px 20px;
                background-color: #d9534f;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
                border: 1px solid transparent;
                transition: all 0.3s;
            }
            .btn:hover {
                background-color: #c9302c;
                color: white;
            }
            .modal {
                display: none;
                position: fixed;
                z-index: 1;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0,0,0,0.7);
            }
            .modal-content {
                background-color: #292929;
                margin: 15% auto;
                padding: 20px;
                border: 1px solid #444;
                width: 80%;
                max-width: 600px;
                border-radius: 5px;
                position: relative;
            }
            .close {
                color: #aaa;
                position: absolute;
                top: 10px;
                right: 20px;
                font-size: 30px;
                cursor: pointer;
            }
            .close:hover {
                color: #fff;
            }
            label {
                color: #aaa;
                display: block;
                text-align: left;
                margin-bottom: 5px;
            }
            input[type="text"],
            input[type="email"],
            textarea {
                width: 100%;
                padding: 8px;
                margin-bottom: 10px;
                background-color: #333;
                color: #fff;
                border: none;
                border-radius: 3px;
            }
            input[type="submit"] {
                background-color: #d9534f;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
            }
            input[type="submit"]:hover {
                background-color: #c9302c;
            }
        </style>
        <title>Account Banned</title>
    </head>
    <body>
        <h1>Account Banned</h1>
        <p>Your account has been banned.</p>
        <p>Please contact the administrator for more information.</p>
        <p>Username: <?php echo $user['username']; ?></p>
        <p>Email: <?php echo $user['email']; ?></p>
        <a href="index.php" class="btn"><i class="fas fa-arrow-left"></i> Back</a>
        <button id="contactBtn" class="btn">Contact Administrator</button>

        <div id="contactModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div id="modalContent">
                    <form id="contactForm" method="POST">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo $user['username']; ?>" required><br>
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required><br>
                        <label for="message">Message:</label>
                        <textarea id="message" name="message" required></textarea><br>
                        <input type="submit" value="Send" class="btn">
                    </form>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function(){
                var modal = $("#contactModal");
                var contactBtn = $("#contactBtn");
                var closeBtn = $(".close");

                contactBtn.click(function(){
                    modal.css("display", "block");
                });

                closeBtn.click(function(){
                    modal.css("display", "none");
                });

                $(window).click(function(event){
                    if (event.target == modal[0]) {
                        modal.css("display", "none");
                    }
                });
            });
        </script>
    </body>
    </html>
    <?php
} else {
    // Redirect to a generic error page if user not found
    header("Location: error.php");
    exit;
}
?>
