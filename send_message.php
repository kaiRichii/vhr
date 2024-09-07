<?php
session_start();
include('database.php');

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$sender_id = $_SESSION["user_id"];
$receiver_id = $_POST["receiver_id"];
$message = $_POST["message"];

$send_message_query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
$send_message_stmt = $conn->prepare($send_message_query);
$send_message_stmt->bind_param("iis", $sender_id, $receiver_id, $message);
$send_message_stmt->execute();

header("Location: " . $_SERVER["HTTP_REFERER"]);
exit();
?>
