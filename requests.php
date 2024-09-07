<?php
session_start();
include('database.php');

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "vehicle_owner") {
    header("Location: index.php");
    exit();
}

// Update booking status if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["booking_id"]) && isset($_POST["status"])) {
    $booking_id = $_POST["booking_id"];
    $status = $_POST["status"];

    // Update the booking status in the database
    $update_status_query = "UPDATE bookings SET status = ? WHERE id = ?";
    $update_status_stmt = $conn->prepare($update_status_query);
    $update_status_stmt->bind_param("si", $status, $booking_id);
    $update_status_stmt->execute();

    if ($status === "confirmed") {
        // Calculate the total amount based on rental duration and vehicle price
        $booking_details_query = "SELECT vehicle_id, start_date, end_date FROM bookings WHERE id = ?";
        $booking_details_stmt = $conn->prepare($booking_details_query);
        $booking_details_stmt->bind_param("i", $booking_id);
        $booking_details_stmt->execute();
        $booking_details_result = $booking_details_stmt->get_result();
        $booking_details = $booking_details_result->fetch_assoc();

        $vehicle_id = $booking_details["vehicle_id"];
        $start_date = strtotime($booking_details["start_date"]);
        $end_date = strtotime($booking_details["end_date"]);
        $rental_duration = ceil(abs($end_date - $start_date) / 86400); // Number of days rented

        $get_vehicle_price_query = "SELECT price FROM vehicles WHERE id = ?";
        $get_vehicle_price_stmt = $conn->prepare($get_vehicle_price_query);
        $get_vehicle_price_stmt->bind_param("i", $vehicle_id);
        $get_vehicle_price_stmt->execute();
        $vehicle_price_result = $get_vehicle_price_stmt->get_result();
        $vehicle_price = $vehicle_price_result->fetch_assoc()["price"];

        $total_amount = $rental_duration * $vehicle_price;

        // Store the total amount in the database
        $update_total_amount_query = "UPDATE bookings SET total_amount = ? WHERE id = ?";
        $update_total_amount_stmt = $conn->prepare($update_total_amount_query);
        $update_total_amount_stmt->bind_param("di", $total_amount, $booking_id);
        $update_total_amount_stmt->execute();

        // Update total_bookings and total_revenue in the vehicles table
        $update_vehicle_stats_query = "UPDATE vehicles SET total_bookings = total_bookings + 1, total_revenue = total_revenue + ? WHERE id = ?";
        $update_vehicle_stats_stmt = $conn->prepare($update_vehicle_stats_query);
        $update_vehicle_stats_stmt->bind_param("di", $total_amount, $vehicle_id);
        $update_vehicle_stats_stmt->execute();
    }

    // Redirect to the same page to reflect changes
    header("Location: requests.php" . ($filter ? "?filter=$filter" : ""));
    exit();
}


// Fetch booked vehicles for the owner with optional filtering
$owner_id = $_SESSION["user_id"];
$fetch_bookings_query = "SELECT b.id, b.start_date, b.end_date, b.status, b.compliance, v.model, v.type, v.price, r.username AS renter_name, r.id AS renter_id
                        FROM bookings b
                        JOIN vehicles v ON b.vehicle_id = v.id
                        JOIN users r ON b.renter_id = r.id
                        WHERE v.owner_id = ?";
if (isset($_GET['filter']) && in_array($_GET['filter'], ['all', 'car', 'motorcycle'])) {
    $filter = $_GET['filter'];
    if ($filter != 'all') {
        $fetch_bookings_query .= " AND v.type = ?";
    }
} else {
    $filter = 'all';
}
$fetch_bookings_stmt = $conn->prepare($fetch_bookings_query);
if ($filter != 'all') {
    $fetch_bookings_stmt->bind_param("is", $owner_id, $filter);
} else {
    $fetch_bookings_stmt->bind_param("i", $owner_id);
}
$fetch_bookings_stmt->execute();
$result = $fetch_bookings_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Requests</title>
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
</style>
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
        <h2>Booking Requests</h2>
        <?php if (isset($_SESSION["success_message"])) : ?>
            <div class="alert alert-success" role="alert">
                <?php echo $_SESSION["success_message"]; ?>
            </div>
            <?php unset($_SESSION["success_message"]); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION["error_message"])) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $_SESSION["error_message"]; ?>
            </div>
            <?php unset($_SESSION["error_message"]); ?>
        <?php endif; ?>
        <form id="filterForm" method="post">
            <select class="form-select mb-3" name="filter">
                <option value="all" <?php echo (!isset($_POST['filter']) || $_POST['filter'] == 'all') ? 'selected' : ''; ?>>All</option>
                <option value="car" <?php echo (isset($_POST['filter']) && $_POST['filter'] == 'car') ? 'selected' : ''; ?>>Car</option>
                <option value="motorcycle" <?php echo (isset($_POST['filter']) && $_POST['filter'] == 'motorcycle') ? 'selected' : ''; ?>>Motor</option>
            </select>
        </form>
        <div id="table-content">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Vehicle Model</th>
                        <th>Renter</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Requirements</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $row["model"] . " (" . $row["type"] . ")"; ?></td>
                            <td><?php echo $row["renter_name"]; ?></td>
                            <td><?php echo $row["start_date"]; ?></td>
                            <td><?php echo $row["end_date"]; ?></td>
                            <td>₱<?php echo $row["price"]; ?>/day</td>
                            <td><?php echo $row["status"]; ?></td>
                             <td><?php echo $row["compliance"] == 1 ? '<a href="view_renter_profile.php?renter_id=' . $row["renter_id"] . '" class="btn btn-primary">Complied</a>' : "None"; ?></td>
                            <td>
                                <form style="text-align: center;" action="requests.php" method="post">
                                    <input type="hidden" name="booking_id" value="<?php echo $row["id"]; ?>">
                                    <div class="action" style="display: flex;">
                                        <select class="form-select" name="status">
                                            <option value="confirmed" <?php echo ($row["status"] === "confirmed") ? "selected" : ""; ?>>Confirm</option>
                                            <option value="completed" <?php echo ($row["status"] === "completed") ? "selected" : ""; ?>>Complete</option>
                                            <option value="cancelled" <?php echo ($row["status"] === "cancelled") ? "selected" : ""; ?>>Cancel</option>
                                        </select>
                                        <button type="submit" class="btn btn-dark btn-hover">Update</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Modal -->
<div class="modal fade" id="renterProfileModal" tabindex="-1" role="dialog" aria-labelledby="renterProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renterProfileModalLabel">Renter Profile</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="renterProfileModalBody">
                <!-- Renter profile information will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
    <script>
        $(document).ready(function() {
            loadPage(1);

            $('#filterForm').on('change', function() {
                loadPage(1);
            });

            function loadPage(page) {
                var filter = $('#filterForm select').val();
                $.ajax({
                    type: 'POST',
                    url: 'load_page.php',
                    data: $('#filterForm').serialize(),
                    success: function(response) {
                        $('#table-content').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.log('Error loading page:', error);
                        console.log(xhr.responseText);
                    }
                });
            }
        });
    </script>
</body>
</html>
