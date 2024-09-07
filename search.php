
<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "vehicle_owner") {
    header("Location: index.php");
    exit();
}

include('database.php');

$owner_id = $_SESSION["user_id"];

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$search_condition = $search ? " AND model LIKE '%$search%'" : '';

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM vehicles WHERE owner_id = $owner_id $search_condition LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>';
        echo '<td>' . $row['type'] . '</td>';
        echo '<td>' . $row['model'] . '</td>';
        echo '<td>' . $row['price'] . '</td>';
        echo '<td>' . ($row['availability'] ? 'Available' : 'Unavailable') . '</td>';
        echo '<td style="text-align: center">';
        echo '<a href="edit_vehicle.php?id=' . $row['id'] . '" class="btn btn-dark btn-sm me-1">Edit</a>';
        echo '<a style="width: 190px;" href="toggle_availability.php?id=' . $row['id'] . '&available=' . ($row['availability'] ? '0' : '1') . '" class="btn btn-dark btn-' . ($row['availability'] ? 'danger' : 'success') . ' btn-sm" onclick="return confirm(\'Are you sure?\');">';
        echo ($row['availability'] ? 'Mark as Unavailable' : 'Mark as Available');
        echo '</a>';
        echo '<a href="delete_vehicle.php?id=' . $row['id'] . '" class="btn btn-danger btn-sm ms-1" onclick="return confirm(\'Are you sure you want to delete this vehicle?\');">Delete</a>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5">No vehicles found.</td></tr>';
}
?>
