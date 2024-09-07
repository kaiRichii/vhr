<?php
session_start();
include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    $vehicle_id = $_POST["vehicle_id"];
    $renter_id = $_SESSION["user_id"];
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];
    $pickup_location = $_POST["pickup_location"];

    if (empty($start_date) || empty($end_date) || empty($pickup_location)) {
        $_SESSION["error_message"] = "Please select start and end dates and provide a pick-up location.";
        header("Location: renter_dashboard.php");
        exit();
    }

    // Check vehicle availability
    $check_availability_query = "SELECT availability FROM vehicles WHERE id = ? AND availability = 1";
    $check_availability_stmt = $conn->prepare($check_availability_query);
    $check_availability_stmt->bind_param("i", $vehicle_id);
    $check_availability_stmt->execute();
    $result = $check_availability_stmt->get_result();
    if (!$result || $result->num_rows === 0) {
        $_SESSION["error_message"] = "The selected vehicle is not available for booking.";
        header("Location: renter_dashboard.php");
        exit();
    }

    // Start a transaction
    $conn->begin_transaction();

    // Book the vehicle with status pending
    $book_vehicle_query = "INSERT INTO bookings (vehicle_id, renter_id, start_date, end_date, pickup_location, status) VALUES (?, ?, ?, ?, ?, 'pending')";
    $book_vehicle_stmt = $conn->prepare($book_vehicle_query);
    $book_vehicle_stmt->bind_param("iisss", $vehicle_id, $renter_id, $start_date, $end_date, $pickup_location);
    $book_vehicle_result = $book_vehicle_stmt->execute();

    if ($book_vehicle_result) {
        // Commit the transaction
        $conn->commit();

        // Redirect to complete_profile.php after successful booking
        header("Location: complete_profile.php?user_id=" . $renter_id);
        exit();
    } else {
        // Rollback the transaction
        $conn->rollback();

        $_SESSION["error_message"] = "Failed to send booking request. Please try again later.";
        header("Location: renter_dashboard.php");
        exit();
    }
} else {
    header("Location: renter_dashboard.php");
    exit();
}
?>
