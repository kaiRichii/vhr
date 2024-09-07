<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "administrator") {
    header("Location: index.php");
    exit();
}

include('database.php');

$log_id = $_GET['log_id'];

// Fetch fraud log details
$sql_fraud_log = "SELECT * FROM fraud_logs WHERE id = ?";
$stmt = $conn->prepare($sql_fraud_log);
$stmt->bind_param("i", $log_id);
$stmt->execute();
$result = $stmt->get_result();
$fraud_log = $result->fetch_assoc();

// Fetch detailed user information
$user_id = $fraud_log['user_id'];
$sql_user_details = "SELECT name, username, role, email, phone FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user_details);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_details = $result_user->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Fraud Log</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Montserrat', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #343a40;
            color: #ffffff;
            border-bottom: none;
            font-size: 1.25rem;
        }
        .card-body {
            font-size: 1rem;
        }
        .btn-primary {
            background-color: #f0ad4e;
            border: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #eea236;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            border: none;
        }
        .btn-back:hover {
            background-color: #5a6268;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Review Fraud Log</h1>
            <a href="admin.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
        <div class="card">
            <div class="card-header">
                <i class="fas fa-exclamation-triangle"></i> Fraud Details
            </div>
            <div class="card-body">
                <p><strong>Admin ID:</strong> <?php echo $fraud_log['admin_id']; ?></p>
                <p><strong>User ID:</strong> <?php echo $fraud_log['user_id']; ?></p>
                <p><strong>Message:</strong> <?php echo $fraud_log['message']; ?></p>
                <p><strong>Date:</strong> <?php echo $fraud_log['created_at']; ?></p>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user"></i> User Details
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo $user_details['name']; ?></p>
                <p><strong>Username:</strong> <?php echo $user_details['username']; ?></p>
                <p><strong>Role:</strong> <?php echo $user_details['role']; ?></p>
                <p><strong>Email:</strong> <?php echo $user_details['email']; ?></p>
                <p><strong>Phone:</strong> <?php echo $user_details['phone']; ?></p>
                <a href="manage_users.php" class="btn btn-primary mt-3"><i class="fas fa-users"></i> Manage User</a>
            </div>
        </div>
    </div>
</body>
</html>
