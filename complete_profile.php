<?php
session_start();
include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"])) {
    $user_id = $_POST["user_id"];
    $id_type = $_POST["id_type"];
    $id_number = $_POST["id_number"];
    $license_number = $_POST["license_number"];
    $emergency_contact = $_POST["emergency_contact"];
    $emergency_contact_number = $_POST["emergency_contact_number"];

     // Check if the ID document is uploaded
     if (!empty($_FILES["id_document"]["name"])) {
        // Upload ID document for the chosen ID type
        $target_dir = "uploads/";
        $id_document = basename($_FILES["id_document"]["name"]);
        $target_file = $target_dir . $id_document;
        move_uploaded_file($_FILES["id_document"]["tmp_name"], $target_file);
    } else {
        // Use the existing ID document from the database
        $get_existing_id_document_query = "SELECT id_document FROM renters WHERE user_id = ?";
        $get_existing_id_document_stmt = $conn->prepare($get_existing_id_document_query);
        $get_existing_id_document_stmt->bind_param("i", $user_id);
        $get_existing_id_document_stmt->execute();
        $get_existing_id_document_result = $get_existing_id_document_stmt->get_result();

        if ($get_existing_id_document_result->num_rows > 0) {
            $existing_id_document_row = $get_existing_id_document_result->fetch_assoc();
            $id_document = $existing_id_document_row["id_document"];
        }
    }

    // Check if renter already exists
    $check_renter_query = "SELECT * FROM renters WHERE user_id = ?";
    $check_renter_stmt = $conn->prepare($check_renter_query);
    $check_renter_stmt->bind_param("i", $user_id);
    $check_renter_stmt->execute();
    $check_renter_result = $check_renter_stmt->get_result();

    if ($check_renter_result->num_rows > 0) {
        // Renter already exists, update the information
        $update_renter_query = "UPDATE renters SET id_type = ?, id_number = ?, license_number = ?, emergency_contact = ?, emergency_contact_number = ?, id_document = ? WHERE user_id = ?";
        $update_renter_stmt = $conn->prepare($update_renter_query);
        $update_renter_stmt->bind_param("ssssssi", $id_type, $id_number, $license_number, $emergency_contact, $emergency_contact_number, $id_document, $user_id);
        $update_renter_result = $update_renter_stmt->execute();

        if ($update_renter_result) {
            $_SESSION["success_message"] = "Your profile has been updated.";

            $update_compliance_query = "UPDATE bookings SET compliance = 1 WHERE renter_id = ?";
            $update_compliance_stmt = $conn->prepare($update_compliance_query);
            $update_compliance_stmt->bind_param("i", $user_id);
            $update_compliance_stmt->execute();
        } else {
            $_SESSION["error_message"] = "Failed to update your profile. Please try again later.";
        }
    } else {
        // Renter does not exist, insert new information
        $insert_renter_query = "INSERT INTO renters (user_id, id_type, id_number, license_number, emergency_contact, emergency_contact_number, id_document) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_renter_stmt = $conn->prepare($insert_renter_query);
        $insert_renter_stmt->bind_param("issssss", $user_id, $id_type, $id_number, $license_number, $emergency_contact, $emergency_contact_number, $id_document);
        $insert_renter_result = $insert_renter_stmt->execute();

        if ($insert_renter_result) {
            $_SESSION["success_message"] = "Your profile has been submitted for verification. Please wait for approval.";

            // Update the compliance column in the bookings table
            $update_compliance_query = "UPDATE bookings SET compliance = 1 WHERE renter_id = ?";
            $update_compliance_stmt = $conn->prepare($update_compliance_query);
            $update_compliance_stmt->bind_param("i", $user_id);
            $update_compliance_stmt->execute();
        } else {
            $_SESSION["error_message"] = "Failed to submit your profile. Please try again later.";
        }
    }

    // Redirect to renter_dashboard.php after processing the form
    header("Location: renter_dashboard.php");
    exit();
}

// Fetch renter information if it exists
if (isset($_GET["user_id"])) {
    $user_id = $_GET["user_id"];
    $fetch_renter_query = "SELECT * FROM renters WHERE user_id = ?";
    $fetch_renter_stmt = $conn->prepare($fetch_renter_query);
    $fetch_renter_stmt->bind_param("i", $user_id);
    $fetch_renter_stmt->execute();
    $fetch_renter_result = $fetch_renter_stmt->get_result();

    if ($fetch_renter_result->num_rows > 0) {
        $renter_info = $fetch_renter_result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-3">Complete Your Profile</h2>
        <form action="complete_profile.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_GET['user_id']); ?>">
            <?php
            if (isset($renter_info)) {
                // Renter already exists, display existing information
                ?>
                <div class="form-group">
                    <label for="id_type">ID Type</label>
                    <input type="text" class="form-control" id="id_type" name="id_type" value="<?php echo htmlspecialchars($renter_info["id_type"]); ?>" required>
                </div>
                <div class="form-group">
                    <label for="id_number">ID Number</label>
                    <input type="text" class="form-control" id="id_number" name="id_number" value="<?php echo htmlspecialchars($renter_info["id_number"]); ?>" required>
                </div>
                <div class="form-group">
                    <label for="id_document">Upload ID</label>
                    <input type="file" class="form-control-file" id="id_document" name="id_document">
                    <?php if (isset($renter_info["id_document"])) : ?>
                        <p>Uploaded ID: <a href="<?php echo htmlspecialchars($target_dir . $renter_info["id_document"]); ?>" target="_blank"><?php echo htmlspecialchars($renter_info["id_document"]); ?></a></p>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="license_number">License Number</label>
                    <input type="text" class="form-control" id="license_number" name="license_number" value="<?php echo htmlspecialchars($renter_info["license_number"]); ?>" required>
                </div>
                <div class="form-group">
                    <label for="emergency_contact">Emergency Contact Person</label>
                    <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" value="<?php echo htmlspecialchars($renter_info["emergency_contact"]); ?>" required>
                </div>
                <div class="form-group">
                    <label for="emergency_contact_number">Emergency Contact Number</label>
                    <input type="text" class="form-control" id="emergency_contact_number" name="emergency_contact_number" value="<?php echo htmlspecialchars($renter_info["emergency_contact_number"]); ?>" required>
                </div>
            <?php
            } else {
                // Renter is new, display empty fields
                ?>
                <div class="form-group">
                    <label for="id_type">ID Type</label>
                    <select class="form-control" id="id_type" name="id_type" required>
                        <option value="driver_license">Driver's License</option>
                        <option value="passport">Passport</option>
                        <option value="national_id">National ID</option>
                        <!-- Add more options if needed -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_number">ID Number</label>
                    <input type="text" class="form-control" id="id_number" name="id_number" required>
                </div>
                <div class="form-group">
                    <label for="id_document">Upload ID</label>
                    <input type="file" class="form-control-file" id="id_document" name="id_document" required>
                </div>
                <div class="form-group">
                    <label for="license_number">License Number</label>
                    <input type="text" class="form-control" id="license_number" name="license_number" required>
                </div>
                <div class="form-group">
                    <label for="emergency_contact">Emergency Contact Person</label>
                    <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" required>
                </div>
                <div class="form-group">
                    <label for="emergency_contact_number">Emergency Contact Number</label>
                    <input type="text" class="form-control" id="emergency_contact_number" name="emergency_contact_number" required>
                </div>
            <?php
            }
            ?>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</body>

</html>
