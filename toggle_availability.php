<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "vehicle_owner") {
    header("Location: index.php");
    exit();
}

include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"]) && isset($_GET["available"])) {
    $vehicle_id = $_GET["id"];
    $available = $_GET["available"];

    // Update the availability status in the database
    $sql = "UPDATE vehicles SET availability = $available WHERE id = $vehicle_id";
    if ($conn->query($sql) === TRUE) {
        $success = "Vehicle availability updated successfully";
    } else {
        $error = "Error updating vehicle availability: " . $conn->error;
    }
}

// Redirect back to manage_vehicles.php
header("Location: manage_vehicles.php");
exit();
?>
