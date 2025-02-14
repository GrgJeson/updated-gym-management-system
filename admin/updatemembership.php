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
//update membership price
if (isset($_POST['update_membership'])) {
    $membership_id = $_POST['membership_id'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    $sql = "UPDATE memberships SET price='$price', description='$description' WHERE id='$membership_id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Membership updated successfully.";
    } else {
        $_SESSION['error'] = "Error updating membership: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../admin/update.css">
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
<h2>Update Membership Prices</h2>
<table>
    <thead>
        <tr>
            <th>Type</th>
            <th>Price</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $memberships_result = $conn->query("SELECT * FROM memberships");
        while ($membership = $memberships_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $membership['type']; ?></td>
                <td><?php echo $membership['price']; ?></td>
                <td><?php echo $membership['description']; ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="membership_id" value="<?php echo $membership['id']; ?>">
                        <input type="text" name="price" placeholder="New Price" required>
                        <input type="text" name="description" placeholder="New Description" required>
                        <button type="submit" name="update_membership">Update</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</body>
</html>