<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Rental Service</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Navbar style */
        .navbar {
            background-color: #303030; 
            font-family: 'Montserrat', sans-serif; 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
        }

        .navbar-brand {
            font-size: 1.5rem; 
            color: #f7ab1c; 
        }

        .navbar-toggler-icon {
            color: #f7ab1c; 
        }

        .navbar-nav .nav-link {
            color: #ffffff; 
            font-weight: 500; 
            padding: 0.5rem 1rem; 
        }

        .navbar-nav .nav-link:hover {
            color: #f7ab1c; 
        }

        /* Button style */
        .navbar .btn-primary {
            background-color: #f7ab1c; 
            border: none; 
            border-radius: 20px;
            padding: 0.5rem 1rem; 
            font-weight: bold; 
            margin-left: 10px; 
        }

        .navbar .btn-primary:hover {
            background-color: #ffcd42; 
            color: #ffffff;
        }

        .active-link {
            color: #f7ab1c !important; 
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
                        <a class="nav-link" href="renter_dashboard.php"><i class="fas fa-tachometer-alt mr-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="vehicles.php"><i class="fas fa-car mr-1"></i>Vehicles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="rental_status.php"><i class="fas fa-calendar-check mr-1"></i>Rental Status</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-warning text-dark" href="home.php"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Add 'active' class to active link
        $(document).ready(function() {
            var currentLocation = window.location.href;
            $('.navbar-nav .nav-link').each(function() {
                var $this = $(this);
                // Check if the current path is the same as the link path
                if ($this.attr('href') === currentLocation) {
                    $this.addClass('active-link');
                }
            });
        });
    </script>
</body>
</html>
