<?php
session_start();
include('database.php');

// Check if user_id is set in the URL
if (isset($_GET['user_id'])) {
    $id = $_GET['user_id'];

    // Handle Add User
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_user"])) {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $email = $_POST["email"];
        $phone = $_POST["phone"];
        $role = $_POST["role"];
    
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
        $sql = "UPDATE `users` SET `username`='$username',`password`='$password',`role`='$role',`email`='$email',`phone`='$phone' 
        WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            $success = "Updated successfully";
            header("Location: admin.php");
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    // Fetch user details from the database
    $sql = "SELECT * FROM users WHERE id = $id LIMIT 1";
    $result = mysqli_query($conn, $sql);

    // Check if the query was successful
    if ($result) {
        // Fetch user details
        $row = mysqli_fetch_assoc($result);
        // Close the result set
        mysqli_free_result($result);
    } else {
        // Handle the case when the query fails
        $error = "Error retrieving user details: " . mysqli_error($conn);
    }
} else {
    // Handle the case when user_id is not set in the URL
    $error = "User ID not provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
     rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
     crossorigin="anonymous">

     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" 
     integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" 
     crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Document</title>
</head>
<body>

    <?php
    // Display messages
    if (isset($success)) {
        echo "<p style='color: green;'>$success</p>";
    }
    if (isset($error)) {
        echo "<p style='color: red;'>$error</p>";
    }
    ?>
    <nav class="navbar navbar-light justify-content-center fs-3 mb-5">
        User Management
    </nav>

    <div class="container">
        <div class="text-center mb-4">
            <h3>Edit User Information</h3>
            <p class="text-muted">Click update after changing any Information</p>
        </div>

        <div class="container d-flex justify-content-center">
            <form action="edit_user.php?user_id=<?php echo $id; ?>" method="post" style="width: 5vw; min-width: 300px;">
            <div class="row mb-3">
                <div class="col">
                    <label class="form-label" for="">username:</label>
                    <input type="text" class="form-control" name="username" value="<?php echo $row['username'] ?>">
                </div>

                <div class="col">
                    <label class="form-label" for="">password:</label>
                    <input type="password" class="form-control" name="password" value="<?php echo $row['password'] ?>">
                </div>
            </div>
            <div class="mb-3">
                    <label class="form-label" for="">Email:</label>
                    <input type="text" class="form-control" name="email" placeholder="name@gmail.com" 
                    value="<?php echo $row['email'] ?>">
            </div>

            <div class="mb-3">
                    <label class="form-label" for="">phone:</label>
                    <input type="text" class="form-control" name="phone" value="<?php echo $row['phone'] ?>">
            </div>

            <div class=" form-group mb-3">
                <select name="role">
                <option value="renter">Renter</option>
                <option value="administrator">Administrator</option>
                <option value="vehicle_owner">Vehicle Owner</option>
                </select><br>
            </div>

            <div>
                <button type="submit" class="btn btn-success" name="add_user" value="Update">Update</button>
                <a href="manage_users.php" class="btn btn-danger">Cancel</a>
            </div>
        </form>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
 integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
 crossorigin="anonymous"></script>
</body>
</html>
