<?php
include('database.php');

$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get the start date and end date values
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Modify the SQL query to include start date and end date conditions
$sql = "SELECT * FROM vehicles WHERE availability = 1 AND approved = 1 AND model LIKE '%$search%'";

// Check if start date and end date are provided
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND id NOT IN (SELECT vehicle_id FROM bookings WHERE (start_date BETWEEN '$start_date' AND '$end_date' OR end_date BETWEEN '$start_date' AND '$end_date') AND status = 'confirmed')";
}

$sql .= " LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    echo '<div class="col-md-4 mb-4">';
    echo '<div class="card">';
    echo '<img src="' . $row['picture'] . '" class="card-img-top" alt="Vehicle Image">';
    echo '<div class="card-body">';
    echo '<h5 class="card-title">' . $row['model'] . '</h5>';
    echo '<p class="card-text">Type: ' . $row['type'] . '</p>';
    echo '<p class="card-text">Price: ' . $row['price'] . '</p>';
    echo '<a href="vehicle_details.php?id=' . $row['id'] . '" class="btn btn-primary">Vehicle Details</a>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>
