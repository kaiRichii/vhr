<?php
// owner.php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "vehicle_owner") {
    header("Location: index.php");
    exit();
}

include('database.php');

$user_id = $_SESSION["user_id"];

// Handle Add Vehicle
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_vehicle"])) {
    $type = $_POST["type"];
    $model = $_POST["model"];
    $price = $_POST["price"];
    $availability = isset($_POST["availability"]) ? 1 : 0;

    // Check if a file was uploaded
    if ($_FILES["picture"]["error"] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["picture"]["name"]);

        // Check if file already exists
        if (file_exists($target_file)) {
            $error = "Sorry, file already exists.";
        } else {
            // Move the file to the uploads directory
            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
                // File uploaded successfully, insert vehicle details into database
                $sql_add_vehicle = "INSERT INTO vehicles (owner_id, type, model, price, availability, picture) VALUES ($user_id, '$type', '$model', $price, $availability, '$target_file')";
                if ($conn->query($sql_add_vehicle) === TRUE) {
                    $success = "Vehicle added successfully";
                } else {
                    $error = "Error adding vehicle: " . $conn->error;
                }
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        $error = "Please select a picture for the vehicle.";
    }
}

// Fetch and display owner's vehicles
$sql_vehicles = "SELECT * FROM vehicles WHERE owner_id = $user_id";
$result_vehicles = $conn->query($sql_vehicles);

// Fetch and display owner's bookings
$sql_bookings = "SELECT * FROM bookings WHERE vehicle_id IN (SELECT id FROM vehicles WHERE owner_id = $user_id)";
$result_bookings = $conn->query($sql_bookings);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vehicle Owner Dashboard</title>
</head>
<style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
}

.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

h1, h2 {
    text-align: center;
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

table, th, td {
    border: 1px solid #ddd;
}

th, td {
    padding: 8px;
    text-align: center;
}

th {
    background-color: #f2f2f2;
}

img {
    max-width: 100px;
    max-height: 100px;
}

.btn {
    display: inline-block;
    padding: 8px 16px;
    margin-bottom: 20px;
    font-size: 16px;
    text-align: center;
    text-decoration: none;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn:hover {
    background-color: #0056b3;
}

.alert {
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 5px;
}

input[type="text"],
input[type="number"],
input[type="file"],
input[type="submit"],
input[type="checkbox"] {
    width: 100%;
    padding: 8px;
    margin-top: 3px;
    margin-bottom: 10px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
}

input[type="submit"] {
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

input[type="submit"]:hover {
    background-color: #0056b3;
}

.checkbox-label {
    display: block;
    margin-top: 10px;
}

.checkbox-label input[type="checkbox"] {
    display: inline-block;
    width: auto;
    margin-top: 0;
    margin-right: 5px;
}
</style>
<body>
    <h1>Welcome, Vehicle Owner</h1>
    
    <h2>My Vehicles</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Model</th>
            <th>Price</th>
            <th>Availability</th>
            <th>Picture</th>
        </tr>
        <?php while ($row_vehicles = $result_vehicles->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row_vehicles['id']; ?></td>
            <td><?php echo $row_vehicles['type']; ?></td>
            <td><?php echo $row_vehicles['model']; ?></td>
            <td><?php echo $row_vehicles['price']; ?></td>
            <td><?php echo $row_vehicles['availability'] ? 'Available' : 'Not Available'; ?></td>
            <td><img src="<?php echo $row_vehicles['picture']; ?>" style="max-width: 100px; max-height: 100px;" alt="Vehicle Picture"></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h2>Add New Vehicle</h2>
    <?php
    // Display messages
    if (isset($success)) {
        echo "<p style='color: green;'>$success</p>";
    }
    if (isset($error)) {
        echo "<p style='color: red;'>$error</p>";
    }
    ?>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data">
        <label>Type:</label>
        <input type="text" name="type" required><br>
        <label>Model:</label>
        <input type="text" name="model" required><br>
        <label>Price:</label>
        <input type="number" name="price" required><br>
        <label>Availability:</label>
        <input type="checkbox" name="availability"><br>
        <label>Picture:</label>
        <input type="file" name="picture" required><br>
        <input type="submit" name="add_vehicle" value="Add Vehicle">
    </form>

    <h2>My Bookings</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Vehicle ID</th>
            <th>Renter ID</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
        </tr>
        <?php while ($row_bookings = $result_bookings->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row_bookings['id']; ?></td>
            <td><?php echo $row_bookings['vehicle_id']; ?></td>
            <td><?php echo $row_bookings['renter_id']; ?></td>
            <td><?php echo $row_bookings['start_date']; ?></td>
            <td><?php echo $row_bookings['end_date']; ?></td>
            <td><?php echo $row_bookings['status']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
