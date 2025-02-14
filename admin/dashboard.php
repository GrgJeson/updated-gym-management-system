<?php
session_start();

// Database connection details
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "gym";

// Create connection
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user information
$admin_id = $_SESSION['id'];
function getUserData($admin_id){
    $query = "SELECT username,password, email FROM admin WHERE id = ?";
    $stmt = $GLOBALS['conn']->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $GLOBALS['conn']->error);
    }

    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit();
    }
    // return $admin;
}

$user = getUserData($admin_id);

// Fetch total counts for dashboard summary
$summary_query = "
    SELECT 
        (SELECT COUNT(*) FROM user) AS total_users,
        (SELECT COUNT(*) FROM subscription WHERE status = 'active') AS active_memberships,
        (SELECT COUNT(*) FROM subscription WHERE status = 'pending') AS pending_requests,
         (SELECT COUNT(*) FROM trainers WHERE status = 'active') AS trainers

    ";
$summary_result = $conn->query($summary_query);
$summary_data = $summary_result->fetch_assoc();



$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../header/header.css">
    <link rel="stylesheet" href="../admin./dashboard.css">
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
<div class="container">
    <h1>Admin Dashboard</h1>

    <!-- Summary Section -->
    <div class="summary">
    <h2>Dashboard Summary</h2>
    <ul>
        <li>Active Memberships: <span id="active-memberships"><?php echo $summary_data['active_memberships']; ?></span></li>
        <li>Pending Memberships: <span id="pending-requests"><?php echo $summary_data['pending_requests']; ?></span></li>
        <li>Trainers: <span id="trainers"><?php echo $summary_data['trainers']; ?></span></li>
        <li>Total Users: <span id="total-users"><?php echo $summary_data['total_users']; ?></span></li>
    </ul>
</div>

</div>
</body>
</html>
