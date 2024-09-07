<?php
session_start();
include('database.php');

if (!isset($_GET['id'])) {
    header("Location: owner_dashboard.php");
    exit();
}

$vehicle_id = $_GET['id'];
$user_id = $_SESSION["user_id"];

// Fetch the vehicle details
$sql_vehicle = "SELECT * FROM vehicles WHERE id = ? AND owner_id = ?";
$stmt_vehicle = $conn->prepare($sql_vehicle);
$stmt_vehicle->bind_param("ii", $vehicle_id, $user_id);
$stmt_vehicle->execute();
$result_vehicle = $stmt_vehicle->get_result();

if ($result_vehicle->num_rows === 0) {
    header("Location: owner_dashboard.php");
    exit();
}

$row_vehicle = $result_vehicle->fetch_assoc();

// Fetch additional statistics for the vehicle
$total_bookings = $row_vehicle['total_bookings'];
$total_revenue = $row_vehicle['total_revenue'];
$average_rating = $row_vehicle['average_rating'];

// Fetch additional images for the vehicle
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
    <?php include('font_css.php')?>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
            /* padding-top: 70px; */
        }

        header {
            background-color: #343a40;
        }

        .navbar {
            background-color: #343a40;
        }

        .navbar-brand,
        .navbar-nav .nav-link {
            color: #ffffff;
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
            /* text-align: center; */
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

    <div class="container" style="margin-top: 100px;">
        <div class="card">
            <img src="<?php echo $row_vehicle['picture']; ?>" class="card-img-top" alt="Vehicle Image">
            <div class="card-body">
                <h5 class="card-title"><?php echo $row_vehicle['model']; ?></h5>
                <p class="card-text">Type: <?php echo $row_vehicle['type']; ?></p>
                <p class="card-text">Price: <?php echo $row_vehicle['price']; ?>/day</p>
                <p class="card-text">Availability: <?php echo $row_vehicle['availability'] ? 'Available' : 'Not Available'; ?></p>
                <p class="card-text"><?php echo $row_vehicle['approved'] == 1 ? 'Approved' : 'Approval: Pending'; ?></p>
                <p class="card-text">Total Bookings: <?php echo $total_bookings; ?></p>
                <p class="card-text">Total Revenue: ₱<?php echo $total_revenue; ?></p>
                <div class="d-flex justify-content-between align-items-center">
                    <p class="card-text">Average Rating: <?php echo $average_rating; ?>/5</p>
                    <button type="button" id="viewFeedbackBtn" class="btn btn-primary" data-toggle="modal" data-target="#feedbackModal">
                    Feedbacks
                    </button>
                </div>
                <a href="edit_vehicle.php?id=<?php echo $row_vehicle['id']; ?>" class="btn btn-primary">Edit</a>
                <a style="width: 190px;" href="toggle_availability.php?id=<?php echo htmlspecialchars($row_vehicle['id']); ?>&available=<?php echo $row_vehicle['availability'] ? '0' : '1'; ?>" class="btn btn-<?php echo $row_vehicle['availability'] ? 'danger' : 'success'; ?>" onclick="return confirmAction('<?php echo $row_vehicle['availability'] ? 'Mark as Unavailable' : 'Mark as Available'; ?>');">
                    <?php echo $row_vehicle['availability'] ? 'Mark as Unavailable' : 'Mark as Available'; ?>
                </a>
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
    <script>
        function confirmAction(action) {
            return confirm("Are you sure you want to " + action + " this vehicle?");
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
