<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "vehicle_owner") {
    header("Location: index.php");
    exit();
}

include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
    $id = $_GET["id"];

    // Delete the vehicle from the database
    $delete_query = "DELETE FROM vehicles WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $id);
    $delete_stmt->execute();

    // Redirect back to the manage vehicles page
    header("Location: manage_vehicles.php");
    exit();
}
?>
