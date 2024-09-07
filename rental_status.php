<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "renter") {
    header("Location: index.php");
    exit();
}

include('database.php');

// Fetch rental status for the logged-in renter
$sql = "SELECT v.*, b.id as booking_id, b.status FROM vehicles v
        INNER JOIN bookings b ON v.id = b.vehicle_id
        WHERE b.renter_id = {$_SESSION["user_id"]}";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Status</title>
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

        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .container {
            /* margin-top: 90px; */
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fit, 25rem);
            justify-content: center;
        }

        .card {
            width: 370px;
            height: 450px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            /* overflow: hidden; */
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

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
           
        }
        .badge-complete{
            background-color: #f0ad4e;
            color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .vehicle-img {
            display: block;
            margin: 0 auto;
            max-height: 250px;
            width: 250px;
            /* margin-bottom: 10px; */
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
    <div class="container" style="margin-top: 50px;">
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <div class="card">
                <img src="<?php echo $row['picture']; ?>" class="card-img-top vehicle-img" alt="Vehicle Image">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $row['model']; ?></h5>
                    <p class="card-text">Type: <?php echo $row['type']; ?></p>
                    <p class="card-text">Price: ₱<?php echo $row['price']; ?>/day</p>
                    <p class="card-text">Status: 
                        <?php if ($row['status'] === 'pending') : ?>
                            <span class="badge badge-warning">Pending</span>
                        <?php elseif ($row['status'] === 'confirmed') : ?>
                            <span class="badge badge-success">Confirmed</span>
                        <?php elseif ($row['status'] === 'completed') : ?>
                            <span class="badge badge-complete">Completed</span>
                        <?php elseif ($row['status'] === 'cancelled') : ?>
                            <span class="badge badge-danger">Rejected</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
