<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "vehicle_owner") {
    header("Location: index.php");
    exit();
}

include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
    $vehicle_id = $_GET["id"];

    // Fetch vehicle details from the database
    $sql = "SELECT * FROM vehicles WHERE id = $vehicle_id";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $type = $row['type'];
        $model = $row['model'];
        $price = $row['price'];
        $availability = $row['availability'];
        $picture = $row['picture'];

        // Fetch additional pictures from vehicle_images table
        $sql_images = "SELECT image_url FROM vehicle_images WHERE vehicle_id = $vehicle_id";
        $result_images = mysqli_query($conn, $sql_images);
        $images = [];
        if ($result_images && mysqli_num_rows($result_images) > 0) {
            while ($row_image = mysqli_fetch_assoc($result_images)) {
                $images[] = $row_image['image_url'];
            }
        }
    } else {
        // Redirect to manage_vehicles.php if the vehicle ID is not found
        header("Location: manage_vehicles.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["edit_vehicle"])) {
    $vehicle_id = $_POST["vehicle_id"];
    $type = $_POST["type"];
    $model = $_POST["model"];
    $price = $_POST["price"];
    $availability = isset($_POST["availability"]) ? 1 : 0;

    // Update the vehicle details in the database
    $sql = "UPDATE vehicles SET type='$type', model='$model', price=$price, availability=$availability WHERE id=$vehicle_id";
    if ($conn->query($sql) === TRUE) {
        // Handle multiple pictures upload for vehicle_images table
        if (!empty($_FILES['pictures']['name'][0])) {
            $image_urls = [];
            foreach ($_FILES['pictures']['name'] as $key => $name) {
                $tmp_name = $_FILES['pictures']['tmp_name'][$key];
                $imageFileType = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $target_file = "uploads/" . uniqid() . "." . $imageFileType;
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $image_urls[] = $target_file;
                }
            }

            // Insert image URLs into vehicle_images table
            foreach ($image_urls as $image_url) {
                $sql_add_image = "INSERT INTO vehicle_images (vehicle_id, image_url) VALUES ($vehicle_id, '$image_url')";
                $conn->query($sql_add_image);
            }
        }

        $success = "Vehicle details updated successfully";
        echo '<script>
            setTimeout(function() {
                window.location.href = "manage_vehicles.php";
            }, 1000); // 1 second delay
        </script>';    
    } else {
        $error = "Error updating vehicle details: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vehicle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
     rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
     crossorigin="anonymous">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" 
     integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" 
     crossorigin="anonymous" referrerpolicy="no-referrer" />
     <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" >
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        h1 {
            margin-bottom: 30px;
            font-weight: bold;
            color: #333;
        }

        form {
            max-width: 600px;
            margin: auto;
        }

        .form-control {
            margin-bottom: 20px;
            border-radius: 10px;
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
    </style>
</head>

<body>
    <div class="container mt-5">
        <!-- <h1>Edit Vehicle</h1> -->
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($success)) : ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>" enctype="multipart/form-data">
        <input type="hidden" name="vehicle_id" value="<?php echo $vehicle_id; ?>">
            <div class="mb-3">
                <label for="type" class="form-label">Vehicle Type:</label>
                <input type="text" class="form-control" id="type" name="type" value="<?php echo $type; ?>" required>
            </div>
            <div class="mb-3">
                <label for="model" class="form-label">Model:</label>
                <input type="text" class="form-control" id="model" name="model" value="<?php echo $model; ?>" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Rental Price:</label>
                <input type="text" class="form-control" id="price" name="price" value="<?php echo $price; ?>" required>
            </div>
            <div class="mb-3">
                <label for="availability" class="form-label">Availability:</label>
                <select class="form-select" id="availability" name="availability" required>
                    <option value="1" <?php if ($availability == 1) echo 'selected'; ?>>Available</option>
                    <option value="0" <?php if ($availability == 0) echo 'selected'; ?>>Not Available</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="picture" class="form-label">Picture:</label>
                <input type="file" class="form-control" id="picture" name="picture" accept="image/*">
            </div>
            <div class="mb-3">
                <label for="pictures" class="form-label">Additional Pictures:</label>
                <input type="file" class="form-control" id="pictures" name="pictures[]" accept="image/*" multiple>
            </div>

            <div class="mb-3">
                <button type="button" class="btn btn-danger" onclick="window.history.back();">Cancel</button>
                <button type="submit" class="btn btn-success" name="edit_vehicle">Save Changes</button>
            </div>

        </form>
    </div>
</body>

</html>