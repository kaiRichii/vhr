<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "renter") {
    header("Location: index.php");
    exit();
}

include('database.php');
$renter_id = $_SESSION["user_id"];

$fetch_username_query = "SELECT username FROM users WHERE id = ?";
$fetch_username_stmt = $conn->prepare($fetch_username_query);
$fetch_username_stmt->bind_param("i", $renter_id);
$fetch_username_stmt->execute();
$fetch_username_result = $fetch_username_stmt->get_result();
$username_row = $fetch_username_result->fetch_assoc();
$username = $username_row["username"];

$fetch_bookings_query = "SELECT b.id, b.start_date, b.end_date, b.status, b.total_amount, b.pickup_location, v.model, v.type, v.price, v.picture, b.additional_payment, b.rating, b.feedback
                        FROM bookings b
                        JOIN vehicles v ON b.vehicle_id = v.id
                        WHERE b.renter_id = ?";
$fetch_bookings_stmt = $conn->prepare($fetch_bookings_query);
$fetch_bookings_stmt->bind_param("i", $renter_id);
$fetch_bookings_stmt->execute();
$result = $fetch_bookings_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renter Dashboard</title>
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
        h1 {
            margin-bottom: 20px;
            font-weight: bold;
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
            font-weight: bold;
            font-size: 14px; /* Adjust font size as needed */
            text-transform: uppercase; /* Uppercase text */
            color: #333; /* Text color */
        }

        .table th {
            background-color: #f8f9fa;
            color: #333; /* Text color */
        }

        .table tbody tr:nth-of-type(even) {
            background-color: #f8f9fa; /* Alternate row color */
        }

        .due-label {
            background-color: #dc3545;
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
        }
                /* Modal style */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; 
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4); 
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        /* Close button style */
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .rating {
            display: inline-block;
            unicode-bidi: bidi-override;
            direction: rtl;
        }

        .rating input {
            display: none;
        }

        .rating label {
            float: right;
            color: #ddd;
        }

        .rating label i {
            font-size: 1.5em;
            transition: color 0.2s;
        }

        .rating input:checked ~ label {
            color: #f0ad4e; /* Change to your desired color */
        }

        /* Feedback textarea style */
        #feedback {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }
    
        .btn-primary {
            background-color: #f0ad4e;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #eea236;
        }
    </style>
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
                <!-- <li class="nav-item">
                    <a class="nav-link" href="messages.php"><i class="fas fa-calendar-check mr-1"></i>Messages</a>
                </li> -->
                <li class="nav-item">
                    <a class="btn btn-warning text-dark" href="home.php"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <!-- <h1>Renter Dashboard</h1> -->
    <p>Welcome, <?php echo $username; ?>!</p>
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
                    <th>Additional Payment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <?php
                        if ($row["status"] === "confirmed" && strtotime($row["end_date"]) < strtotime("today")) {
                            $additional_payment = $row["price"] * (strtotime("today") - strtotime($row["end_date"])) / (60 * 60 * 24);
                            // Update additional_payment in database
                            $update_additional_payment_query = "UPDATE bookings SET additional_payment = ? WHERE id = ?";
                            $update_additional_payment_stmt = $conn->prepare($update_additional_payment_query);
                            $update_additional_payment_stmt->bind_param("di", $additional_payment, $row["id"]);
                            $update_additional_payment_stmt->execute();
                            $overdue = true;
                        } else {
                            $additional_payment = $row["additional_payment"];
                            $overdue = false;
                        }
                        ?>
                        <tr>
                            <td><?php if ($overdue): ?>
                                <span class="badge badge-danger">Overdue</span>
                            <?php endif; ?>
                            </td>
                            <td><?php echo $row["model"]; ?></td>
                            <td><?php echo $row["type"]; ?></td>
                            <td><?php echo $row["start_date"]; ?></td>
                            <td><?php echo $row["end_date"]; ?></td>
                            <td><?php echo $row["pickup_location"]; ?></td>
                            <td><?php echo $row["status"]; ?></td>
                            <td>₱<?php echo $row["price"]; ?>/day</td>
                            <td>₱<?php echo $row["total_amount"]; ?></td>
                            <td>₱<?php echo number_format($additional_payment, 2); ?></td>
                            <td>
                            <?php if ($row["status"] === "completed" && (!empty($row["rating"]) && !empty($row["feedback"]))): ?>
                                <button class="btn btn-primary rateButton" data-booking-id="<?php echo $row['id']; ?>">Rated</button>
                            <?php elseif ($row["status"] === "completed"): ?>
                                <button class="btn btn-primary rateButton" data-booking-id="<?php echo $row['id']; ?>">Rate</button>
                            <?php endif; ?>
                        </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
        </table>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<div id="rateModal" class="modal">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Rate Your Experience</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="ratingForm" action="rate_experience.php" method="post">
          <div class="form-group">
          <div class="rating">
            <input type="radio" id="star5" name="rating" value="5" required>
            <label for="star5"><i class="fas fa-star"></i></label>
            <input type="radio" id="star4" name="rating" value="4">
            <label for="star4"><i class="fas fa-star"></i></label>
            <input type="radio" id="star3" name="rating" value="3">
            <label for="star3"><i class="fas fa-star"></i></label>
            <input type="radio" id="star2" name="rating" value="2">
            <label for="star2"><i class="fas fa-star"></i></label>
            <input type="radio" id="star1" name="rating" value="1">
            <label for="star1"><i class="fas fa-star"></i></label>
        </div>
          </div>
          <div class="form-group">
            <label for="feedback">Feedback:</label>
            <textarea id="feedback" name="feedback" rows="3" class="form-control" required></textarea>
          </div>
          <input type="hidden" id="bookingId" name="booking_id">
          <button type="submit" class="btn btn-primary btn-block">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
     document.addEventListener("DOMContentLoaded", function() {
        var modal = document.getElementById("rateModal");
        var buttons = document.querySelectorAll(".rateButton");

        buttons.forEach(function(button) {
            button.onclick = function() {
                var bookingId = button.getAttribute('data-booking-id');
                document.getElementById('bookingId').value = bookingId;
                modal.style.display = "block";
            };
        });

        var span = document.getElementsByClassName("close")[0];
        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        modal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var bookingId = button.getAttribute('data-booking-id');
            document.getElementById('bookingId').value = bookingId;
        });
    });
</script>

<script>
        // Add 'active' class to active link
        $(document).ready(function() {
            var currentLocation = window.location.href;
            $('.navbar-nav .nav-link').each(function() {
                var $this = $(this);
                // Check if the current path is the same as the link path
                if ($this.attr('href') === currentLocation) {
                    $this.addClass('active-link');
                }
            });
        });
    </script>
</body>
</html>
