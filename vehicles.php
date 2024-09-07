<?php
session_start();
include('database.php');

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "renter") {
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

$renter_id = $_SESSION["user_id"];
// if (!$renter_id) {
//     echo "User ID not fetched!";
//     exit();
// }
// Select the email of the user
$sql_email = "SELECT email FROM users WHERE id = $renter_id";
$result_email = mysqli_query($conn, $sql_email);
$row_email = mysqli_fetch_assoc($result_email);
$email = $row_email['email'];

$fetch_username_query = "SELECT username FROM users WHERE id = ?";
$fetch_username_stmt = $conn->prepare($fetch_username_query);
$fetch_username_stmt->bind_param("i", $renter_id);
$fetch_username_stmt->execute();
$fetch_username_result = $fetch_username_stmt->get_result();
$username_row = $fetch_username_result->fetch_assoc();
$username = $username_row["username"];

$sql_verification_status = "SELECT verified FROM users WHERE id = $renter_id";
$result_verification_status = mysqli_query($conn, $sql_verification_status);
$row_verification_status = mysqli_fetch_assoc($result_verification_status);
$verification_status = $row_verification_status['verified'];


$search = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM vehicles WHERE approved = 1";
if (!empty($search)) {
    $sql .= " WHERE model LIKE '%$search%'";
}
$sql .= " LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);

$total_count_sql = "SELECT COUNT(*) AS count FROM vehicles";
if (!empty($search)) {
    $total_count_sql .= " WHERE model LIKE '%$search%'";
}
$total_count_result = mysqli_query($conn, $total_count_sql);
$total_count_row = mysqli_fetch_assoc($total_count_result);
$total_count = $total_count_row['count'];
$total_pages = ceil($total_count / $limit);

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
    <title>Vehicles</title>
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

        .card {
            text-align: center;
            padding: 20px;
            box-shadow: 0 4px 10px 0 rgba(31, 38, 135, 0.15);
            border-radius: 0.5rem;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .card img {
            display: block;
            margin: 0 auto;
            max-height: 150px;
            width: 150px;
            margin-bottom: 10px;
        }

        .card-body {
            padding: 20px;
        }

        .card-title {
            font-size: 1.25rem;
            margin-bottom: 10px;
        }

        .card-text {
            margin-bottom: 10px;
        }

        /* .btn-primary {
            background-color: #f0ad4e;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            font-weight: bold;
            color: white;
        }

        .btn-primary:hover {
            background-color: #eea236;
        } */

        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
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
                    <a class="nav-link" href="renter_dashboard.php"><i class="fas fa-tachometer-alt mr-1"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vehicles.php"><i class="fas fa-car mr-1"></i>Vehicles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rental_status.php"><i class="fas fa-calendar-check mr-1"></i>Rental Status</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-warning text-dark" href="home.php"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-5">
    <h1>Vehicles</h1>
    <p>Welcome, <?php echo $username; ?>!</p>
    <?php if (isset($_SESSION["success_message"])) : ?>
        <div class="alert alert-success" role="alert">
            <?php echo $_SESSION["success_message"]; ?>
        </div>
        <?php unset($_SESSION["success_message"]); ?>
    <?php endif; ?>
    <form class="mb-3" method="get" id="searchForm" action="vehicles.php">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Search by model" name="search" id="searchInput" value="<?php echo $search; ?>">
        </div>
    </form>
    <?php if ($verification_status == 0) : ?>
    <p class="text-danger">Please verify your email before accessing vehicle details.</p>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <button type="submit" name="verify_email" class="btn btn-primary">Verify Email</button>
<?php endif; ?>

    <div class="row">
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <div class="col-md-4 mb-4">
                <!-- <a href="vehicle_details.php?id=<?php echo $row['id']; ?>" class="card-link"> -->
                    <div class="card">
                        <img src="<?php echo $row['picture']; ?>" class="card-img-top" alt="Vehicle Image">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['model']; ?></h5>
                            <p class="card-text">Type: <?php echo $row['type']; ?></p>
                            <p class="card-text">Price: <?php echo $row['price']; ?>/day</p>
                            <a href="<?php echo $verification_status == 1 ? 'vehicle_details.php?id=' . $row['id'] : '#'; ?>" class="btn btn-primary<?php echo $verification_status == 0 ? ' disabled' : ''; ?>">Vehicle Details</a>
                        </div>
                    </div>
                <!-- </a> -->
            </div>
        <?php endwhile; ?>
    </div>
    <?php if ($total_pages > 1) : ?>
        <div aria-label="Page navigation">
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="vehicles.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
                </div>
    <?php endif; ?>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#bookingModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        modal.find('.modal-title').text('Confirm Booking for ' + button.data('model'));
        modal.find('#confirmBookingBtn').attr('href', button.data('target'));
    });

    function validateDates() {
        var startDate = document.getElementById('start_date').value;
        var endDate = document.getElementById('end_date').value;

        if (startDate > endDate) {
            alert('End date must be after start date');
            return false;
        }
        return true;
    }
</script>
<script>
    $(document).ready(function() {
        $('#searchInput').on('input', function() {
            loadPage(1);
        });

        function loadPage(page) {
            console.log('Loading page:', page);
            var search = $('#searchInput').val();
            $.ajax({
                type: 'GET',
                url: 'searchRenter.php',
                data: { search: search, page: page },
                success: function(response) {
                    $('.row').html(response);
                    updatePagination(page);
                },
                error: function(xhr, status, error) {
                    console.log('Error loading page:', error);
                    console.log(xhr.responseText);
                }
            });
        }
    });
    $('#searchForm').submit(function(event) {
        event.preventDefault(); // Prevent form submission
        loadPage(1); // Load the first page of search results
    });
</script>
</body>
</html>
