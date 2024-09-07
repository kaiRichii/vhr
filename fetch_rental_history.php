<?php
session_start();
include('database.php');

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "renter") {
    http_response_code(401);
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch rental history from the database
$sql = "SELECT * FROM rental_history WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);

// Process the result into an array
$rentalHistory = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rentalHistory[] = [
        'vehicle' => $row['vehicle'],
        'start_date' => $row['start_date'],
        'end_date' => $row['end_date']
    ];
}

// Return the rental history as JSON
echo json_encode($rentalHistory);
?>
