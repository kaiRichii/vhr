<?php
session_start();
include('database.php');

if (!isset($_GET['booking_id'])) {
    header("Location: renter_dashboard.php");
    exit();
}

$booking_id = $_GET['booking_id'];

// Retrieve booking details
$booking_query = "SELECT * FROM bookings WHERE id = ?";
$booking_stmt = $conn->prepare($booking_query);
$booking_stmt->bind_param("i", $booking_id);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();

if ($booking_result && $booking_result->num_rows > 0) {
    $booking = $booking_result->fetch_assoc();

    // Check if the booking belongs to the current user
    if ($booking['renter_id'] != $_SESSION['user_id']) {
        $_SESSION["error_message"] = "Unauthorized access.";
        header("Location: renter_dashboard.php");
        exit();
    }

    // Process payment logic here
    // Confirm rental details logic here

    // Update booking status to confirmed
    $update_status_query = "UPDATE bookings SET status = 'confirmed' WHERE id = ?";
    $update_status_stmt = $conn->prepare($update_status_query);
    $update_status_stmt->bind_param("i", $booking_id);
    $update_status_result = $update_status_stmt->execute();

    if ($update_status_result) {
        $_SESSION["success_message"] = "Rental confirmed!";
    } else {
        $_SESSION["error_message"] = "Failed to confirm rental. Please try again later.";
    }
} else {
    $_SESSION["error_message"] = "Booking not found.";
}

header("Location: renter_dashboard.php");
exit();
?>
