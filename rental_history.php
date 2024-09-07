<?php
session_start();
include('database.php');

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "vehicle_owner") {
    header("Location: index.php");
    exit();
}

$owner_id = $_SESSION["user_id"];

// Fetch rental history for the owner
$sql = "SELECT b.id, b.start_date, b.end_date, b.status, v.model, v.type, v.price, r.username AS renter_name, b.total_amount, b.additional_payment
        FROM bookings b
        JOIN vehicles v ON b.vehicle_id = v.id
        JOIN users r ON b.renter_id = r.id
        WHERE v.owner_id = ?
        ORDER BY b.start_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    $_SESSION["error_message"] = "Error fetching rental history: " . mysqli_error($conn);
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
    <title>Rental History</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
        }

        .navbar {
            background-color: #343a40;
        }

        .navbar-brand,
        .navbar-nav .nav-link {
            color: #ffffff;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background-color: #f0ad4e;
            color: white;
            font-weight: bold;
            border-radius: 10px 10px 0 0;
        }

        .card-body {
            padding: 20px;
        }

        .status-label {
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
        }

        .status-pending {
            background-color: #f0ad4e;
        }

        .status-confirmed {
            background-color: #f0ad4e;
        }
        .status-completed {
            background-color: #28a745;
        }

        .status-cancelled {
            background-color: #dc3545;
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
        <h2>Rental History</h2>
        <div class="card-columns">
            <?php while ($row = $result->fetch_assoc()) : ?>
                <div class="card">
                    <div class="card-header">
                        Booking ID: <?php echo $row["id"]; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Vehicle: <?php echo $row["model"] . " (" . $row["type"] . ")"; ?>
                        </h5>
                        <p class="card-text">Renter: <?php echo $row["renter_name"]; ?></p>
                        <p class="card-text">Start Date: <?php echo $row["start_date"]; ?></p>
                        <p class="card-text">End Date: <?php echo $row["end_date"]; ?></p>
                        <p class="card-text">Price: ₱<?php echo $row["price"]; ?></p>
                        <p class="card-text">Total Amount: ₱<?php echo $row["total_amount"]; ?></p>
                        <p class="card-text">Additional Payment: ₱<?php echo $row["additional_payment"]; ?></p>
                        <span class="status-label status-<?php echo strtolower($row["status"]); ?>"><?php echo
                            $row["status"]; ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
