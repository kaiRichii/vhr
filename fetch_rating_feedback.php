<?php
// fetch_rating_feedback.php

session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "renter") {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

include('database.php');

$bookingId = $_POST['booking_id'];

$stmt = $conn->prepare("SELECT rating, feedback FROM bookings WHERE id = ?");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $response = array('success' => true, 'rating' => $row['rating'], 'feedback' => $row['feedback']);
    echo json_encode($response);
} else {
    echo json_encode(array('success' => false));
}

$stmt->close();
$conn->close();
?>
