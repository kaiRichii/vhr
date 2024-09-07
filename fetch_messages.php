<?php
session_start();
include('database.php');

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$conversation_with = $_GET["conversation_with"];

$fetch_messages_query = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY timestamp ASC";
$fetch_messages_stmt = $conn->prepare($fetch_messages_query);
$fetch_messages_stmt->bind_param("iiii", $user_id, $conversation_with, $conversation_with, $user_id);
$fetch_messages_stmt->execute();
$result = $fetch_messages_stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);
?>
