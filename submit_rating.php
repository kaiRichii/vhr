<?php
session_start();
include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["booking_id"]) && isset($_POST["rating"]) && isset($_POST["feedback"])) {
    $booking_id = $_POST["booking_id"];
    $rating = $_POST["rating"];
    $feedback = $_POST["feedback"];

    $update_rating_query = "UPDATE bookings SET rating = ?, feedback = ? WHERE id = ?";
    $update_rating_stmt = $conn->prepare($update_rating_query);
    $update_rating_stmt->bind_param("isi", $rating, $feedback, $booking_id);
    $update_rating_result = $update_rating_stmt->execute();

    if ($update_rating_result) {
        // Update the average rating and total ratings for the vehicle
        $fetch_vehicle_id_query = "SELECT vehicle_id FROM bookings WHERE id = ?";
        $fetch_vehicle_id_stmt = $conn->prepare($fetch_vehicle_id_query);
        $fetch_vehicle_id_stmt->bind_param("i", $booking_id);
        $fetch_vehicle_id_stmt->execute();
        $fetch_vehicle_id_result = $fetch_vehicle_id_stmt->get_result();
        $vehicle_id_row = $fetch_vehicle_id_result->fetch_assoc();
        $vehicle_id = $vehicle_id_row["vehicle_id"];

        $update_vehicle_ratings_query = "UPDATE vehicles SET average_rating = (SELECT AVG(rating) FROM bookings WHERE vehicle_id = ?), total_ratings = (SELECT COUNT(*) FROM bookings WHERE vehicle_id = ? AND rating IS NOT NULL) WHERE id = ?";
        $update_vehicle_ratings_stmt = $conn->prepare($update_vehicle_ratings_query);
        $update_vehicle_ratings_stmt->bind_param("iii", $vehicle_id, $vehicle_id, $vehicle_id);
        $update_vehicle_ratings_stmt->execute();

        $_SESSION["success_message"] = "Thank you for your rating!";
    } else {
        $_SESSION["error_message"] = "Failed to submit your rating. Please try again later.";
    }

    // Redirect to the dashboard after processing the rating
    header("Location: renter_dashboard.php");
    exit();
}
?>
