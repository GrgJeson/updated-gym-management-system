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

// Fetch active users with memberships
$users_query = "SELECT u.id, u.Full_Name FROM user u JOIN subscription s ON u.id = s.user_id WHERE s.status = 'active'";
$users_result = $conn->query($users_query);

// Fetch all trainers
$trainers_query = "SELECT id, name FROM trainers";
$trainers_result = $conn->query($trainers_query);

// Handle trainer assignment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_trainer'])) {
    $user_id = $_POST['user_id'];
    $trainers_id = $_POST['trainers_id'];
    $duration = $_POST['duration']; // Get the duration in months from the form

    // Define prices for each duration in Nepali Rupees
    $prices = [
        1 => 2000,  // 1 month = 2000 NPR
        3 => 5000,  // 3 months = 5000 NPR
        6 => 9000,  // 6 months = 9000 NPR
        12 => 16000 // 12 months = 16000 NPR
    ];

    if (!empty($user_id) && !empty($trainers_id) && !empty($duration)) {
        // Calculate expiry date based on the duration in months
        $expiry_date = date('Y-m-d', strtotime("+$duration months"));

        // Get the price for the selected duration
        $price = isset($prices[$duration]) ? $prices[$duration] : 0;

        // Update the user with the assigned trainer, duration, expiry date, and price
        $assign_query = "UPDATE user SET trainers_id = ?, duration = ?, expiry_date = ?, price = ? WHERE id = ?";
        $stmt = $conn->prepare($assign_query);

        // Check if the statement prepared successfully
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("iiisi", $trainers_id, $duration, $expiry_date, $price, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Trainer successfully assigned to the user for $duration months.";
        } else {
            $_SESSION['error'] = "Failed to assign trainer: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Please select both a user and a trainer, and specify a valid duration.";
    }

    header("Location: assignTrainer.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Trainer</title>
    <link rel="stylesheet" href="../admin/assign.css">
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
    <h1>Assign Trainer to User</h1>
</header>
<main>
    <!-- Display success or error messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="assignTrainer.php">
        <div class="form-group">
            <label for="trainers_id">Select Trainer:</label>
            <select name="trainers_id" id="trainers_id" required>
                <option value="">-- Select a Trainer --</option>
                <?php while ($trainers = $trainers_result->fetch_assoc()): ?>
                    <option value="<?php echo $trainers['id']; ?>"><?php echo $trainers['name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="user_id">Select User:</label>
            <select name="user_id" id="user_id" required>
                <option value="">-- Select a User --</option>
                <?php while ($user = $users_result->fetch_assoc()): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo $user['Full_Name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="duration">Select Duration:</label>
            <select name="duration" id="duration" required>
                <option value="">-- Select Duration --</option>
                <option value="1">1 Month - 4500 NPR</option>
                <option value="3">3 Months - 12000 NPR</option>
                <option value="6">6 Months - 25000 NPR</option>
                <option value="12">12 Months - 45000 NPR</option>
            </select>
        </div>

        <div class="form-group">
            <button type="submit" name="assign_trainer">Assign Trainer</button>
        </div>
    </form>
</main>
</body>
</html>
