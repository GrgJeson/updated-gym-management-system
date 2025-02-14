<?php 

// Start session to handle login and user-related variables
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    $_SESSION['error'] = "You must be logged in as an admin to access this page.";
    header("Location: ../login/login.php");
    exit();
}

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Add Trainer
if (isset($_POST['add_trainer'])) {
    $name = mysqli_real_escape_string($conn, $_POST['trainer_name']);
    $email = mysqli_real_escape_string($conn, $_POST['trainer_email']);
    $phone = mysqli_real_escape_string($conn, $_POST['trainer_phone']);
    $password = password_hash($_POST['trainer_password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO trainers (name, email, phone, password) VALUES ('$name', '$email', '$phone', '$password')";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Trainer added successfully.";
    } else {
        $_SESSION['error'] = "Error adding trainer: " . $conn->error;
    }
}

// Delete Trainer
if (isset($_POST['delete_trainer'])) {
    $trainer_id = $_POST['trainer_id'];
    $sql = "DELETE FROM trainers WHERE id='$trainer_id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Trainer deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting trainer: " . $conn->error;
    }
}


// Get all trainers
$trainers_sql = "SELECT * FROM trainers";
$trainers_result = $conn->query($trainers_sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../admin/trainer.css">
</head>
<body>
<header class="header" id="header">
    <div class="logo">
        <a href="">
            <img src="../image/logo.png" alt="EGO THRUST Logo">EGO THRUST
        </a>
    </div>

    <div class="nav" id="nav">
    <ul class="center">
            <li><a href="../admin/updatemembership.php">Update Price</a></li>
            <li><a href="../admin/addTrainer.php">Add Trainer</a></li>
            <li><a href="../admin/admin.php">Pending Membership</a></li>
            <li><a href="../admin/approved.php">Approved members</a></li>
            <li><a href="../admin/dashboard.php">Dashboard</a></li>
            <li><a href="../admin/assignTrainer.php">Assign</a></li>
            <li><a href="../admin/assigned.php">Assigned</a></li>

        </ul>
    </div>

    <div class="login">
        <ul>  
                <li><a href="../logout.php">Logout</a></li>
        </ul>
        
    </div>
</header>
<h2>Manage Trainers</h2>

<!-- Add Trainer Form -->
<form method="POST">
    <input type="text" name="trainer_name" placeholder="Trainer Name" required>
    <input type="email" name="trainer_email" placeholder="Trainer Email" required>
    <input type="text" name="trainer_phone" placeholder="Trainer Phone" required>
    <input type="password" name="trainer_password" placeholder="Trainer Password" required>
    <button type="submit" name="add_trainer">Add Trainer</button>
</form>

<!-- List of Trainers -->
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($trainer = $trainers_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $trainer['name']; ?></td>
                <td><?php echo $trainer['email']; ?></td>
                <td><?php echo $trainer['phone']; ?></td>
                <td><?php echo $trainer['status']; ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="trainer_id" value="<?php echo $trainer['id']; ?>">
                        <button type="submit" name="delete_trainer">Delete Trainer</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>

<?php
$conn->close();
?>
