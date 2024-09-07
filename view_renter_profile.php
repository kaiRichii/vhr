<?php
session_start();
include('database.php');

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "vehicle_owner") {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["renter_id"])) {
    $renter_id = $_GET["renter_id"];

    $fetch_renter_query = "SELECT r.*, u.name AS renter_name
                           FROM renters r
                           JOIN users u ON r.user_id = u.id
                           WHERE r.user_id = ?";
    $fetch_renter_stmt = $conn->prepare($fetch_renter_query);
    $fetch_renter_stmt->bind_param("i", $renter_id);
    $fetch_renter_stmt->execute();
    $renter_result = $fetch_renter_stmt->get_result();

    if ($renter_result->num_rows > 0) {
        $renter_row = $renter_result->fetch_assoc();
    } else {
        $_SESSION["error_message"] = "Renter not found.";
        header("Location: requests.php");
        exit();
    }
} else { 
    $_SESSION["error_message"] = "Invalid request.";
    header("Location: requests.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renter Profile</title>
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

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .alert-danger {
            margin-bottom: 20px;
        }

        .row {
            margin-bottom: 20px;
        }

        .col-md-6 {
            padding: 10px 20px;
        }

        a {
            color: #007bff;
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
    <div class="container">
        <h2>Renter Profile</h2>
        <?php if (isset($_SESSION["error_message"])) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $_SESSION["error_message"]; ?>
            </div>
            <?php unset($_SESSION["error_message"]); ?>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-6">
                <strong>Full Name:</strong> <?php echo $renter_row["renter_name"]; ?><br>
                <strong>ID Type:</strong> <?php echo $renter_row["id_type"]; ?><br>
                <strong>ID Number:</strong> <?php echo $renter_row["id_number"]; ?><br>
                <strong>License Number:</strong> <?php echo $renter_row["license_number"]; ?><br>
            </div>
            <div class="col-md-6">
                <strong>Emergency Contact Person:</strong> <?php echo $renter_row["emergency_contact"]; ?><br>
                <strong>Emergency Contact Number:</strong> <?php echo $renter_row["emergency_contact_number"]; ?><br>
                <strong>ID Document:</strong> <a href="uploads/<?php echo $renter_row["id_document"]; ?>" target="_blank">View Document</a><br>
            </div>
        </div>
    </div>
</body>
</html>
