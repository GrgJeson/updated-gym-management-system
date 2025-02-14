<?php
session_start();

// Check admin access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    $_SESSION['error'] = "You must be logged in as an admin to access this page.";
    header("Location: ../login/login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "gym";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle remove action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_user'])) {
    $user_id = $_POST['user_id'];

    if (!empty($user_id)) {
        // Remove trainer assignment for the user
        $remove_query = "UPDATE user SET trainers_id = NULL, duration = NULL, expiry_date = NULL, price = NULL WHERE id = ?";
        $stmt = $conn->prepare($remove_query);

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Trainer assignment removed successfully.";
        } else {
            $_SESSION['error'] = "Failed to remove trainer assignment: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Invalid user selected for removal.";
    }

    header("Location: assigned.php");
    exit();
}

// Fetch assigned trainers and user details
$assigned_query = "
    SELECT 
        u.id AS user_id,
        u.Full_Name AS user_name,
        t.name AS trainers_name,
        u.duration,
        u.expiry_date,
        u.price
    FROM 
        user u 
    JOIN 
        trainers t 
    ON 
        u.trainers_id = t.id
    WHERE 
        u.trainers_id IS NOT NULL";

$assigned_result = $conn->query($assigned_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigned Trainers</title>
    <link rel="stylesheet" href="../admin/assigned.css">
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
<header>
    <h1>Assigned Trainers</h1>
</header>
<main>
    <!-- Display success or error messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>User Name</th>
                <th>Trainer Name</th>
                <th>Duration (Months)</th>
                <th>Expiry Date</th>
                <th>Price (NPR)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($assigned_result->num_rows > 0): ?>
                <?php while ($row = $assigned_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['user_name']; ?></td>
                        <td><?php echo $row['trainers_name']; ?></td>
                        <td><?php echo $row['duration']; ?></td>
                        <td><?php echo date("d-m-Y", strtotime($row['expiry_date'])); ?></td>
                        <td><?php echo $row['price']; ?></td>
                        <td>
                            <form method="POST" action="assigned.php">
                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                <button type="submit" name="remove_user" class="btn-remove">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No trainers assigned yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>
</body>
</html>
