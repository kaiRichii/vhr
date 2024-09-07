<?php
// database.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicle_rental_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// function log_admin_action($admin_id, $action, $details) {
//     global $conn;
//     $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, ?, ?)");
//     $stmt->bind_param("iss", $admin_id, $action, $details);
//     $stmt->execute();
//     $stmt->close();
// }
?>