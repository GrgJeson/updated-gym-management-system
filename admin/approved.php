<?php
// Start session to handle login and user-related variables
session_start();

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

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

//Remove membership
if (isset($_POST['remove_user'])) {
    $subscription_id = intval($_POST['subscription_id']);
    $sql = "DELETE FROM subscription WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $subscription_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Membership removed.";
    } else {
        $_SESSION['error'] = "Error removing membership: " . $conn->error;
    }
    $stmt->close();
}

$sql = "SELECT 
            s.id AS subscription_id, 
            u.Full_Name, 
            u.email, 
            u.phone, 
            m.type AS membership_type, 
            s.payment_status, 
            s.status, 
            s.subscription_date, 
            s.expiry_date
        FROM subscription s
        JOIN user u ON s.user_id = u.id
        JOIN memberships m ON s.membership_id = m.id
        WHERE s.status = 'active'";
        $result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Query Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../admin/admin.css">
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

<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<h2>Approved members</h2>

<?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Membership Type</th>
                <th>Payment Status</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['Full_Name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['phone']; ?></td>
                    <td><?php echo $row['membership_type']; ?></td>
                    <td><?php echo $row['payment_status']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                    <form method="POST">
                            <input type="hidden" name="subscription_id" value="<?php echo $row['subscription_id']; ?>">
                            <button type="submit" name="remove_user">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No pending requests found.</p>
<?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>
