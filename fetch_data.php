<?php
session_start();
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

include('database.php');

$owner_id = $_SESSION["user_id"];

$page = isset($_POST['page']) ? $_POST['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_POST['search']) ? $_POST['search'] : '';
$search_condition = $search ? " AND model LIKE '%$search%'" : '';

$sql = "SELECT * FROM vehicles WHERE owner_id = $owner_id $search_condition LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

$data['vehicle_table'] = '';
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data['vehicle_table'] .= '
            <tr>
                <td>' . $row['type'] . '</td>
                <td>' . $row['model'] . '</td>
                <td>' . $row['price'] . '</td>
                <td>' . ($row['availability'] ? 'Available' : 'Unavailable') . '</td>
                <td>
                    <a href="edit_vehicle.php?id=' . $row['id'] . '">Edit</a> |
                    <a href="toggle_availability.php?id=' . $row['id'] . '&available=' . ($row['availability'] ? '0' : '1') . '">'
                        . ($row['availability'] ? 'Mark as Unavailable' : 'Mark as Available') .
                    '</a>
                </td>
            </tr>';
    }
} else {
    $data['vehicle_table'] .= '
        <tr>
            <td colspan="5">No vehicles found.</td>
        </tr>';
}

// Calculate total pages for pagination
$total_count_sql = "SELECT COUNT(*) AS count FROM vehicles WHERE owner_id = $owner_id $search_condition";
$total_count_result = mysqli_query($conn, $total_count_sql);
$total_count_row = mysqli_fetch_assoc($total_count_result);
$total_count = $total_count_row['count'];
$total_pages = ceil($total_count / $limit);

// Pagination links
$data['pagination'] = '';
for ($i = 1; $i <= $total_pages; $i++) {
    $data['pagination'] .= '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
        <a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>
    </li>';
}

echo json_encode($data);
?>
