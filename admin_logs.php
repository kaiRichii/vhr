<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "administrator") {
    header("Location: index.php");
    exit();
}

include('database.php');

// Pagination logic
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Number of entries per page
$offset = ($page - 1) * $limit;

// Fetch admin logs with pagination
$sql_admin_logs = "SELECT * FROM admin_logs ORDER BY timestamp DESC LIMIT ? OFFSET ?";
$stmt_admin_logs = $conn->prepare($sql_admin_logs);
$stmt_admin_logs->bind_param("ii", $limit, $offset);
$stmt_admin_logs->execute();
$result_admin_logs = $stmt_admin_logs->get_result();

// Fetch total number of admin logs for pagination
$sql_total_logs = "SELECT COUNT(*) AS total_logs FROM admin_logs";
$result_total_logs = $conn->query($sql_total_logs);
$total_logs = $result_total_logs->fetch_assoc()['total_logs'];
$total_pages = ceil($total_logs / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Fraud Log</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
    <div class="container mt-5">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Admin Logs</h5>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <!-- <th>Admin ID</th> -->
                            <th>Action</th>
                            <th>Details</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row_admin_logs = $result_admin_logs->fetch_assoc()): ?>
                            <tr>
                                <!-- <td><?php echo $row_admin_logs['admin_id']; ?></td> -->
                                <td><?php echo $row_admin_logs['action']; ?></td>
                                <td><?php echo $row_admin_logs['details']; ?></td>
                                <td><?php echo $row_admin_logs['timestamp']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Pagination controls -->
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                                <a class="page-link" href="admin_logs.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</body>
</html>
