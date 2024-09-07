<?php
session_start();
include('database.php');

// Include PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "vehicle_owner") {
    header("Location: index.php");
    exit();
}

// Send email to renters with due rent for today and tomorrow
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["send_email"])) {
    
    // Retrieve renters with due rent for today and tomorrow
    $get_renters_query = "SELECT u.id, u.email, u.username, b.vehicle_id
                        FROM bookings b
                        JOIN users u ON b.renter_id = u.id
                        JOIN vehicles v ON b.vehicle_id = v.id
                        WHERE v.owner_id = ? AND b.end_date IN (CURDATE(), CURDATE() + INTERVAL 1 DAY)";
    $get_renters_stmt = $conn->prepare($get_renters_query);
    $get_renters_stmt->bind_param("i", $_SESSION["user_id"]);
    $get_renters_stmt->execute();
    $renters_result = $get_renters_stmt->get_result();

    // Send email to each renter with due rent
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                      
        $mail->Host       = 'smtp.gmail.com';             
        $mail->SMTPAuth   = true;                           
        $mail->Username   = 'tessnarval11@gmail.com';       
        $mail->Password   = 'swtw itkl hpuz oqvw';                
        $mail->SMTPSecure = 'tls';                          
        $mail->Port       = 587;                            

        while ($row = $renters_result->fetch_assoc()) {
            // Send email to renter
            $mail->setFrom('tessnarval11@example.com', 'Vehicle Rental Service');  
            $mail->addAddress($row["email"]);                    

            // Content
            $mail->isHTML(true);                                 
            $mail->Subject = 'Reminder: Return Vehicle';
            $mail->Body    = "Dear {$row['username']},<br><br>This is a reminder that your rental for vehicle {$row['vehicle_id']} ends {$row['end_date']}. Please return the vehicle on time.<br><br>Best regards,<br>Your Rental Service";

            // Send email
            $mail->send();
        }

        $_SESSION["success_message"] = "Email sent successfully";
    } catch (Exception $e) {
        $_SESSION["error_message"] = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

    // Redirect to the same page
    header("Location: bookings.php");
    exit();
}

// Fetch bookings for the owner
$owner_id = $_SESSION["user_id"];
$fetch_bookings_query = "SELECT b.id, b.start_date, b.end_date, b.status, v.model, v.type, v.price, u.username AS renter_name, u.id AS renter_id, b.vehicle_id, b.total_amount, b.additional_payment
                        FROM bookings b
                        JOIN vehicles v ON b.vehicle_id = v.id
                        JOIN users u ON b.renter_id = u.id
                        WHERE v.owner_id = ? AND b.status = 'confirmed'";
$fetch_bookings_stmt = $conn->prepare($fetch_bookings_query);
$fetch_bookings_stmt->bind_param("i", $owner_id);
$fetch_bookings_stmt->execute();
$result = $fetch_bookings_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
         body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
            /* padding-top: 70px; */
        }

        header {
            background-color: #343a40;
        }
        .navbar{
            background-color: #343a40;
        }

        .navbar-brand,
        .navbar-nav .nav-link {
            color: #ffffff;
        }
        .btn-primary {
            background-color: #f0ad4e;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            font-weight: bold;
            color: white;
        }

        .btn-primary:hover {
            background-color: #eea236;
        }

        .jumbotron {
            margin-top: 80px; /* Adjust based on navbar height and margin */
            background-color: #f8f9fa;
        }

        h1 {
            margin-bottom: 20px;
            font-weight: bold;
        }
        select, option, .btn{
            display: block;
            width: 100px;
            height: 35px;
            padding: 5px;
            text-align: center;
            border: none;
            outline: none;
            /* border: 1px solid red; */
        }
        .action{
            /* border: 1px solid red; */
            align-items: center;
            gap: 5px;
        }
        .form-select{
            width: fit-content;
            text-align: center;
        }

        .table {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table th,
        .table td {
            border-top: 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 12px;
            vertical-align: middle;
        }

        .table th {
            background-color: #f8f9fa;
            color: #333;
            border-color: rgba(0, 0, 0, 0.1);
        }

        .table tbody tr:nth-of-type(even) {
            background-color: rgba(0, 0, 0, 0.03);
        }
        .due-label {
            background-color: #dc3545;
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
        }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">Vehicle Rental Service</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="owner_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_vehicles.php"><i class="fas fa-car"></i> Manage Vehicles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="requests.php"><i class="fas fa-list"></i> View Requests</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bookings.php"><i class="fas fa-calendar-alt"></i> Bookings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rental_history.php"><i class="fas fa-history"></i> Rental History</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-warning text-dark" href="home.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
    <div class="container mt-5">
        <h2>Bookings</h2>
        <?php if(isset($_SESSION["success_message"])): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $_SESSION["success_message"]; ?>
            </div>
            <?php unset($_SESSION["success_message"]); ?>
        <?php endif; ?>
        <?php if(isset($_SESSION["error_message"])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $_SESSION["error_message"]; ?>
            </div>
            <?php unset($_SESSION["error_message"]); ?>
        <?php endif; ?>
        <form action="bookings.php" method="post">
            <button type="submit" name="send_email" class="btn btn-danger">Notify</button>
        </form>
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th></th>
                    <th>Vehicle Model</th>
                    <th>Renter</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Price</th>
                    <th>Total Amount</th>
                    <th>Additional Payment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()) : ?>
                    <?php
                        $end_date = strtotime($row["end_date"]);
                        $due_today_tomorrow = (date('Y-m-d', $end_date) == date('Y-m-d', strtotime('today')) || date('Y-m-d', $end_date) == date('Y-m-d', strtotime('tomorrow'))) ? true : false;
                        $overdue = (date('Y-m-d', $end_date) < date('Y-m-d')) ? true : false;
                    ?>
                    <tr>
                        <td>
                            <?php if ($due_today_tomorrow): ?>
                                <span class="due-label">Due</span>
                            <?php endif; ?>
                            <?php if ($overdue): ?>
                                <span class="badge badge-danger">Overdue</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row["model"] . " (" . $row["type"] . ")"; ?></td>
                        <td><?php echo $row["renter_name"]; ?></td>
                        <td><?php echo $row["start_date"]; ?></td>
                        <td><?php echo $row["end_date"]; ?></td>
                        <td>₱<?php echo $row["price"]; ?></td>
                        <td>₱<?php echo $row["total_amount"]; ?></td>
                        <td>₱<?php echo $row["additional_payment"]; ?></td>
                        <td><?php echo $row["status"]; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
