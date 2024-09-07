<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "renter") {
    header("Location: index.php");
    exit();
}

include('database.php');

$renter_id = $_SESSION["user_id"];
$fetch_bookings_query = "SELECT b.id, b.start_date, b.end_date, b.status, b.total_amount, b.pickup_location, v.model, v.type, v.price, v.picture
                        FROM bookings b
                        JOIN vehicles v ON b.vehicle_id = v.id
                        WHERE b.renter_id = ?";
$fetch_bookings_stmt = $conn->prepare($fetch_bookings_query);
$fetch_bookings_stmt->bind_param("i", $renter_id);
$fetch_bookings_stmt->execute();
$result = $fetch_bookings_stmt->get_result();
?>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding-top: 56px; /* Adjust based on navbar height */
            background-color: #f8f9fa;
        }

        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .jumbotron {
            margin-top: 80px; /* Adjust based on navbar height and margin */
            text-align: center;
            background-color: #f8f9fa;
        }

        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #f8f9fa;
            text-align: center;
            padding: 10px 0;
        }

        /* Table Styling */
        .table-responsive {
            margin-top: 40px; /* Adjust margin as needed */
        }

        .table {
            background-color: #fff;
            border-radius: 8px;
        }

        .table th,
        .table td {
            border-top: 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 12px;
        }

        .table th {
            background-color: #f8f9fa;
            color: black;
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
<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Logo</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="renter_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vehicles.php">Vehicles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rental_status.php">Rental Status</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
</header>
<div class="container mt-5">
    <h1>Renter Dashboard</h1>
    <p>Welcome, <?php echo $_SESSION["username"]; ?>!</p>
    <?php if (isset($_SESSION["success_message"])) : ?>
        <div class="alert alert-success" role="alert">
            <?php echo $_SESSION["success_message"]; ?>
        </div>
        <?php unset($_SESSION["success_message"]); ?>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table">
            <thead class="table-dark">
                <tr>
                    <th></th>
                    <th>Vehicle Model</th>
                    <th>Vehicle Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Pickup Location</th>
                    <th>Status</th>
                    <th>Price</th>
                    <th>Total Amount</th>
                    <!-- Add more table headers if needed -->
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <?php
                        $end_date = strtotime($row["end_date"]);
                        $due_today_tomorrow = (date('Y-m-d', $end_date) == date('Y-m-d', strtotime('today')) || date('Y-m-d', $end_date) == date('Y-m-d', strtotime('tomorrow'))) ? true : false;
                    ?>
                    <tr>
                        <td><?php if ($due_today_tomorrow): ?><span class="due-label">Due</span><?php endif; ?></td>
                        <td><?php echo $row["model"]; ?></td>
                        <td><?php echo $row["type"]; ?></td>
                        <td><?php echo $row["start_date"]; ?></td>
                        <td><?php echo $row["end_date"]; ?></td>
                        <td><?php echo $row["pickup_location"]; ?></td>
                        <td><?php echo $row["status"]; ?></td>
                        <td><?php echo $row["price"]; ?></td>
                        <td><?php echo $row["total_amount"]; ?></td>
                        <!-- Add more table cells if needed -->
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
