<?php
// book.php
session_start();
include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicle_id = $_POST["vehicle_id"];
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];

    $sql = "INSERT INTO bookings (vehicle_id, renter_id, start_date, end_date, status) VALUES ('$vehicle_id', '{$_SESSION["user_id"]}', '$start_date', '$end_date', 'pending')";
    if ($conn->query($sql) === TRUE) {
        echo "Booking successful";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
    <input type="hidden" name="vehicle_id" value="<?php echo $_GET["vehicle_id"]; ?>">
    <input type="date" name="start_date" placeholder="Start Date" required><br>
    <input type="date" name="end_date" placeholder="End Date" required><br>
    <button type="submit">Book</button>
</form>
