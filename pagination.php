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

$limit = 10; // Define the limit here

$total_count_sql = "SELECT COUNT(*) AS count FROM vehicles WHERE owner_id = $owner_id $search_condition";
$total_count_result = mysqli_query($conn, $total_count_sql);
$total_count_row = mysqli_fetch_assoc($total_count_result);
$total_count = $total_count_row['count'];
$total_pages = ceil($total_count / $limit);

$current_page = isset($_GET['page']) ? $_GET['page'] : 1;

$output = '<nav aria-label="Page navigation"><ul class="pagination">';
for ($i = 1; $i <= $total_pages; $i++) {
    $output .= '<li class="page-item ' . ($current_page == $i ? 'active' : '') . '"><a class="page-link" href="#" onclick="loadPage(' . $i . '); return false;">' . $i . '</a></li>';
}
$output .= '</ul></nav>';

echo $output;
?>
