<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "vehicle_owner") {
    header("Location: index.php");
    exit();
}

include('database.php');
// Include PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$user_id = $_SESSION["user_id"];

// Select the email of the user
$sql_email = "SELECT email FROM users WHERE id = $user_id";
$result_email = mysqli_query($conn, $sql_email);
$row_email = mysqli_fetch_assoc($result_email);
$email = $row_email['email'];

$owner_id = $_SESSION["user_id"];

    $sql_username = "SELECT username FROM users WHERE id = $user_id";
    $result_username = mysqli_query($conn, $sql_username);
    $row_username = mysqli_fetch_assoc($result_username);
    $username = $row_username['username'];


    $sql_verification_status = "SELECT verified FROM users WHERE id = $user_id";
    $result_verification_status = mysqli_query($conn, $sql_verification_status);
    $row_verification_status = mysqli_fetch_assoc($result_verification_status);
    $verification_status = $row_verification_status['verified'];

    
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM vehicles WHERE owner_id = $owner_id LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

$total_count_sql = "SELECT COUNT(*) AS count FROM vehicles WHERE owner_id = $owner_id";
$total_count_result = mysqli_query($conn, $total_count_sql);
$total_count_row = mysqli_fetch_assoc($total_count_result);
$total_count = $total_count_row['count'];
$total_pages = ceil($total_count / $limit);

// Fetch total number of vehicles owned by the user
$sql_total_vehicles = "SELECT COUNT(*) AS total_vehicles FROM vehicles WHERE owner_id = $user_id";
$result_total_vehicles = mysqli_query($conn, $sql_total_vehicles);
$row_total_vehicles = mysqli_fetch_assoc($result_total_vehicles);
$total_vehicles = $row_total_vehicles['total_vehicles'];

$average_rental_duration_query = "SELECT AVG(CEIL(UNIX_TIMESTAMP(end_date) - UNIX_TIMESTAMP(start_date)) / 86400) AS avg_duration FROM bookings WHERE status = 'completed'";
$average_rental_duration_result = mysqli_query($conn, $average_rental_duration_query);
$average_rental_duration_row = mysqli_fetch_assoc($average_rental_duration_result);
$average_rental_duration = $average_rental_duration_row['avg_duration'];

// Fetch total bookings for the user with status 'confirmed'
$sql_total_bookings = "SELECT COUNT(*) AS total_bookings FROM bookings WHERE vehicle_id IN (SELECT id FROM vehicles WHERE owner_id = $user_id) AND status = 'confirmed'";
$result_total_bookings = mysqli_query($conn, $sql_total_bookings);
$row_total_bookings = mysqli_fetch_assoc($result_total_bookings);
$total_bookings = $row_total_bookings['total_bookings'];


// Fetch total revenue for the user
$sql_total_revenue = "SELECT SUM(total_revenue) AS total_revenue FROM vehicles WHERE owner_id = $user_id";
$result_total_revenue = mysqli_query($conn, $sql_total_revenue);
$row_total_revenue = mysqli_fetch_assoc($result_total_revenue);
$total_revenue = $row_total_revenue['total_revenue'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["verify_email"])) {
        // Assuming you have received the email from the form
        $verification_code = md5(uniqid(rand(), true));

        $mail = new PHPMailer;

        // Configure PHPMailer
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'tessnarval11@gmail.com'; // SMTP username
        $mail->Password = 'swtw itkl hpuz oqvw'; // SMTP password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('no-reply@example.com', 'Vehicle Rental Service');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Email Verification';
        $mail->Body    = "Your verification code is: $verification_code. Please enter this code on the <a href='http://yourdomain.com/verify.php'>verification page</a> to verify your email.";

        if (!$mail->send()) {
            $error = 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
        } else {

            $sql = "UPDATE users SET verification_code = '$verification_code' WHERE email = '$email'";

            if ($conn->query($sql) === TRUE) {
                $_SESSION['success'] = 'Verification email sent successfully!';
                header("Location: email_verification.php");
                exit();
            } else {
                $error = "Error updating record: " . $conn->error;
            }

            $conn->close();
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
    <title>Vehicle Rental Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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

        .jumbotron {
            background-image: url('img/dashboard.jpg');
            background-size: cover;
            color: #ffffff;
            text-align: center;
            padding: 100px 0;
            margin-top: 20px;
        }

        .card {
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 0 6px 0 rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-body {
            padding: 16px;
            text-align: center;
        }

        .card-title {
            font-weight: 500;
            margin-bottom: 8px;
        }

        .card-text {
            color: #666666;
            margin-bottom: 12px;
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

        footer {
            background-color: #343a40;
            color: #ffffff;
            text-align: center;
            padding: 20px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
       
        .disabled {
            pointer-events: none;
            opacity: 0.6;
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
                <li class="nav-item <?php if ($verification_status == 0) echo 'disabled'; ?>">
                    <a class="nav-link" href="<?php if ($verification_status == 1) echo 'manage_vehicles.php'; ?>"><i class="fas fa-car"></i> Manage Vehicles</a>
                </li>
                <li class="nav-item <?php if ($verification_status == 0) echo 'disabled'; ?>">
                    <a class="nav-link" href="<?php if ($verification_status == 1) echo 'requests.php'; ?>"><i class="fas fa-list"></i> View Requests</a>
                </li>
                <li class="nav-item <?php if ($verification_status == 0) echo 'disabled'; ?>">
                    <a class="nav-link" href="<?php if ($verification_status == 1) echo 'bookings.php'; ?>"><i class="fas fa-calendar-alt"></i> Bookings</a>
                </li>
                <li class="nav-item <?php if ($verification_status == 0) echo 'disabled'; ?>">
                    <a class="nav-link" href="<?php if ($verification_status == 1) echo 'rental_history.php'; ?>"><i class="fas fa-history"></i> Rental History</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-warning text-dark" href="home.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
        </ul>
        </div>
    </div>
</nav>
<?php if ($verification_status == 0) : ?>
    <p style="text-align: center; margin-top: 10px" class="text-danger">Please verify your email before accessing the system's features.</p>
    <div style="text-align: center;">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <button type="submit" name="verify_email" class="btn btn-primary">Verify Email</button>
</form>
    </div>
<?php endif; ?>

    <div class="container">
        <div class="jumbotron">
            <div class="container">
                <h1 class="display-4" style="text-transform: capitalize;">Welcome, <?php echo $username; ?>!</h1>
                <p class="lead">View your rental statistics and manage your vehicles here.</p>
            </div>
        </div>
       

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Vehicles</h5>
                        <p class="card-text"><?php echo $total_vehicles; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Bookings</h5>
                        <p class="card-text"><?php echo $total_bookings; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Revenue</h5>
                        <p class="card-text"><?php echo $total_revenue; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction:row; gap: 5px; width:fit-content; margin: 0 auto; margin-bottom: 20px">
            <div class="notifications">
                <!-- Display notifications here -->
            </div>
            <div class="performance-metrics">
                <!-- <p>Average Rental Duration: <?php echo $average_rental_duration; ?></p> -->
                <!-- Add more performance metrics here -->
            </div>
        </div>

        <div class="row">
            <?php while ($row = $result->fetch_assoc()) : ?>
                <div class="col-md-4">
                    <div class="card">
                        <img src="<?php echo $row['picture']; ?>" class="card-img-top" alt="Vehicle Image">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['model']; ?></h5>
                            <p class="card-text">Type: <?php echo $row['type']; ?></p>
                            <p class="card-text">Price: ₱<?php echo $row['price']; ?>/day</p>
                            <p class="card-text">Availability: <?php echo $row['availability'] ? 'Available' : 'Not Available'; ?></p>
                            <p class="card-text"><?php echo $row['approved'] == 1 ? 'Approved' : 'Approval: Pending'; ?></p>
                            <a href="view_vehicle.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if ($total_pages > 1) : ?>
            <div aria-label="Page navigation">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="owner_dashboard.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <!-- <footer>
        <div class="container">
            <p>&copy; 2024 Vehicle Rental Service. All Rights Reserved.</p>
        </div>
    </footer> -->

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
