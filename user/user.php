<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

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
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT email, created_at FROM user WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    // If user not found, force logout
    header("Location: logout.php");
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="user.css"> 
    <link rel="stylesheet" href="../header/header.css">
</head>
<body>
<header>
        <?php include "../header/Header.php" ?>
    </header>
    <div class="dashboard">
        <h1>Welcome to Your Dashboard</h1>

        <div class="user-info">
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
            <p><strong>Member Since:</strong> <?= htmlspecialchars($user['created_at']); ?></p>
        </div>

        <a href="../logout.php">Logout</a>
    </div>
</body>
</html>
