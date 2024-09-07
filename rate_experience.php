<?php
session_start();
include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = $_POST["booking_id"];
    $rating = $_POST["rating"];
    $feedback = $_POST["feedback"];

    // Update the rating and feedback for the booking
    $update_rating_query = "UPDATE bookings SET rating = ?, feedback = ? WHERE id = ?";
    $update_rating_stmt = $conn->prepare($update_rating_query);
    $update_rating_stmt->bind_param("isi", $rating, $feedback, $booking_id);
    $update_rating_result = $update_rating_stmt->execute();

    if ($update_rating_result) {
        // Calculate the average rating for the vehicle
        $calculate_avg_rating_query = "UPDATE vehicles SET average_rating = (
                                            SELECT AVG(rating) FROM bookings WHERE vehicle_id = ? AND rating BETWEEN 1 AND 5
                                        ) WHERE id = ?";
        $calculate_avg_rating_stmt = $conn->prepare($calculate_avg_rating_query);
        $calculate_avg_rating_stmt->bind_param("ii", $vehicle_id, $vehicle_id);

        // Get the vehicle_id for the booking
        $fetch_vehicle_id_query = "SELECT vehicle_id FROM bookings WHERE id = ?";
        $fetch_vehicle_id_stmt = $conn->prepare($fetch_vehicle_id_query);
        $fetch_vehicle_id_stmt->bind_param("i", $booking_id);
        $fetch_vehicle_id_stmt->execute();
        $fetch_vehicle_id_result = $fetch_vehicle_id_stmt->get_result();
        $vehicle_id_row = $fetch_vehicle_id_result->fetch_assoc();
        $vehicle_id = $vehicle_id_row["vehicle_id"];

        // Begin a transaction
        $conn->begin_transaction();

        // Update the average rating
        $calculate_avg_rating_stmt->execute();

        // Commit the transaction
        $conn->commit();

        $_SESSION["success_message"] = "Rating submitted successfully.";
    } else {
        $_SESSION["error_message"] = "Failed to submit rating. Error: " . $conn->error;
    }

    header("Location: renter_dashboard.php");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>
