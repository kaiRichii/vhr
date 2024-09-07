<?php
session_start();
include('database.php');

if (!isset($_GET['id'])) {
    header("Location: renter_dashboard.php");
    exit();
}

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "renter") {
    header("Location: index.php");
    exit();
}

$renter_id = $_SESSION["user_id"];
$vehicle_id = $_GET['id'];

$fetch_username_query = "SELECT username FROM users WHERE id = ?";
$fetch_username_stmt = $conn->prepare($fetch_username_query);
$fetch_username_stmt->bind_param("i", $renter_id);
$fetch_username_stmt->execute();
$fetch_username_result = $fetch_username_stmt->get_result();
$username_row = $fetch_username_result->fetch_assoc();
$username = $username_row["username"];

// Fetch the vehicle details
$sql_vehicle = "SELECT * FROM vehicles WHERE id = ?";
$stmt_vehicle = $conn->prepare($sql_vehicle);
$stmt_vehicle->bind_param("i", $vehicle_id);
$stmt_vehicle->execute();
$result_vehicle = $stmt_vehicle->get_result();

if ($result_vehicle->num_rows === 0) {
    header("Location: renter_dashboard.php");
    exit();
}

$row_vehicle = $result_vehicle->fetch_assoc();

$sql_images = "SELECT * FROM vehicle_images WHERE vehicle_id = ?";
$stmt_images = $conn->prepare($sql_images);
$stmt_images->bind_param("i", $vehicle_id);
$stmt_images->execute();
$result_images = $stmt_images->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
            /* padding-top: 70px; */
        }

        .jumbotron {
            background-image: url('img/dashboard.jpg');
            background-size: cover;
            color: #ffffff;
            text-align: center;
            padding: 100px 0;
            margin-top: 20px;
        }
        /* .jumbotron {
            background-image: url('img/dashboard.jpg');
            background-size: cover;
            color: #ffffff;
            text-align: center;
            padding: 100px 0;
            margin-top: 20px;
        } */
        .card {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-img-top {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card-body {
            padding: 20px;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .card-text {
            color: #555;
            margin-bottom: 10px;
        }

        .form-control {
            border-radius: 20px;
        }

        .btn-primary {
            background-color: #f0ad4e;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: #eea236;
        }

        .footer {
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }
                /* Button style */
        #viewFeedbackBtn {
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #fff;
            background-color: #4285f4;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #viewFeedbackBtn:hover {
            background-color: #3367d6;
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
        <div class="jumbotron">
            <h1 class="display-4">Vehicle Details</h1>
            <!-- <p>Welcome, <?php echo $username; ?>!</p> -->
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <img src="<?php echo $row_vehicle['picture']; ?>" class="card-img-top" alt="Vehicle Image">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $row_vehicle['model']; ?></h5>
                        <p class="card-text">Type: <?php echo $row_vehicle['type']; ?></p>
                        <p class="card-text">Price: ₱<?php echo $row_vehicle['price']; ?>/day</p>
                        <p class="card-text">Availability: <?php echo $row_vehicle['availability'] ? 'Available' : 'Not Available'; ?></p>
                        <p class="card-text">Total Bookings: <?php echo $row_vehicle['total_bookings']; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="card-text">Average Rating: <?php echo $row_vehicle['average_rating']; ?></p>
                            <button id="viewFeedbackBtn" class="btn btn-primary"  data-toggle="modal" data-target="#feedbackModal">Feedbacks</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Book Vehicle</h3>
                        <form action="book_vehicle.php" method="post">
                            <input type="hidden" name="vehicle_id" value="<?php echo $row_vehicle['id']; ?>">
                            <div class="form-group">
                                <label for="start_date">Start Date:</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date:</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                            <div class="form-group">
                                <label for="pickup_location">Pick-up Location:</label>
                                <input type="text" class="form-control" id="pickup_location" name="pickup_location"
                                    required>
                            </div>
                            <button type="submit" class="btn btn-primary">Book Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <h3>Additional Images</h3>
        <div class="row">
            <?php while ($row_image = mysqli_fetch_assoc($result_images)) : ?>
            <div class="col-md-3">
                <img src="<?php echo $row_image['image_url']; ?>" alt="Vehicle Image" class="img-thumbnail">
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <div class="modal fade" id="feedbackModal" tabindex="-1" role="dialog" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">Feedbacks for <?php echo $row_vehicle['model']; ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
    <div class="row">
        <?php
        $sql_feedbacks = "SELECT * FROM bookings WHERE vehicle_id = ? AND feedback IS NOT NULL";
        $stmt_feedbacks = $conn->prepare($sql_feedbacks);
        $stmt_feedbacks->bind_param("i", $vehicle_id);
        $stmt_feedbacks->execute();
        $result_feedbacks = $stmt_feedbacks->get_result();

        while ($row_feedback = $result_feedbacks->fetch_assoc()) {
            ?>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Rating: <?php echo $row_feedback['rating']; ?></h5>
                        <p class="card-text"><?php echo $row_feedback['feedback']; ?></p>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
