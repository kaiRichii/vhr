<?php
session_start();
include('database.php');

// if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
//     header("Location: index.php");
//     exit();
// }

$search = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM vehicles";
if (!empty($search)) {
    $sql .= " WHERE model LIKE '%$search%'";
}
$sql .= " LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);

$total_count_sql = "SELECT COUNT(*) AS count FROM vehicles";
if (!empty($search)) {
    $total_count_sql .= " WHERE model LIKE '%$search%'";
}
$total_count_result = mysqli_query($conn, $total_count_sql);
$total_count_row = mysqli_fetch_assoc($total_count_result);
$total_count = $total_count_row['count'];
$total_pages = ceil($total_count / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicles</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
        }

        .navbar {
            background-color: #343a40;
        }

        .navbar-brand,
        .navbar-nav .nav-link {
            color: #ffffff;
        }

        .btn-primary {
            background-color: #f0ad4e;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            font-weight: bold;
            color: white;
        }

        .btn-primary:hover {
            background-color: #eea236;
        }

        .card {
            text-align: center;
            padding: 20px;
            box-shadow: 0 4px 10px 0 rgba(31, 38, 135, 0.15);
            border-radius: 0.5rem;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .card img {
            display: block;
            margin: 0 auto;
            max-height: 150px;
            width: 150px;
            margin-bottom: 10px;
        }

        .card-body {
            padding: 20px;
        }

        .card-title {
            font-size: 1.25rem;
            margin-bottom: 10px;
        }

        .card-text {
            margin-bottom: 10px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Vehicle Rental Service</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_vehicles.php"><i class="fas fa-car mr-1"></i></i>Listings</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <!-- <h1>Vehicles</h1> -->
        <!-- <form class="mb-3" method="get" id="searchForm" action="vehicles.php">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search by model" name="search"
                    id="searchInput" value="<?php echo $search; ?>">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </div>
        </form> -->
        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?php echo $row['picture']; ?>" class="card-img-top" alt="Vehicle Image">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['model']; ?></h5>
                            <p class="card-text">Type: <?php echo $row['type']; ?></p>
                            <p class="card-text">Price: <?php echo $row['price']; ?>/day</p>
                            <!-- <a href="vehicle_details.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Vehicle Details</a> -->
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <?php if ($total_pages > 1) : ?>
            <div aria-label="Page navigation">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="vehicles.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#searchForm').submit(function (event) {
            event.preventDefault(); // Prevent form submission
            loadPage(1); // Load the first page of search results
        });

        $('#searchInput').on('input', function () {
            loadPage(1);
        });

        function loadPage(page) {
            console.log('Loading page:', page);
            var search = $('#searchInput').val();
            $.ajax({
                type: 'GET',
                url: 'searchVehicles.php',
                data: { search: search, page: page },
                success: function (response) {
                    $('.row').html(response);
                    updatePagination(page);
                },
                error: function (xhr, status, error) {
                    console.log('Error loading page:', error);
                    console.log(xhr.responseText);
                }
            });
        }

        function updatePagination(page) {
            $('.pagination').find('.active').removeClass('active');
            $('.pagination').find('li').eq(page - 1).addClass('active');
        }
    </script>
</body>

</html>
