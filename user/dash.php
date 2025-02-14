<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: ../login/login.php");
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
$user_id = $_SESSION['id'];
function getUserData($user_id) {
    $query = "SELECT Full_Name, email, phone, trainers_id FROM user WHERE id = ?";
    $stmt = $GLOBALS['conn']->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $GLOBALS['conn']->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit();
    }
    return $user;
}

// Fetch trainer details
function getTrainerDetails($trainers_id) {
    $query = "SELECT name, email, phone FROM trainers WHERE id = ?";
    $stmt = $GLOBALS['conn']->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $GLOBALS['conn']->error);
    }

    $stmt->bind_param("i", $trainers_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null; // No trainer assigned
}

$user = getUserData($user_id);
$trainers = $user['trainers_id'] ? getTrainerDetails($user['trainers_id']) : null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = htmlspecialchars($_POST['Full_Name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);

    $update_query = "UPDATE user SET Full_Name = ?, email = ?, phone = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);

    if ($update_stmt->execute()) {
        $_SESSION['user_Full_Name'] = $full_name; // Update session
        $success_message = "Profile updated successfully!";
        $user = getUserData($user_id);
        $trainers = $user['trainers_id'] ? getTrainerDetails($user['trainers_id']) : null;
    } else {
        $error_message = "Error updating profile.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="dash.css">
    <link rel="stylesheet" href="../header/header.css">
</head>
<body>
<header>
    <?php include "../header/Header.php"; ?>
</header>
<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($user['Full_Name']); ?></h1>
    <?php if (!empty($success_message)) : ?>
        <p class="success"><?php echo $success_message; ?></p>
    <?php endif; ?>
    <?php if (!empty($error_message)) : ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form action="dash.php" method="POST" class="profile-form">
        <label for="Full_Name">Full Name:</label>
        <input type="text" id="Full_Name" name="Full_Name" value="<?php echo htmlspecialchars($user['Full_Name']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>

        <button type="submit">Update Profile</button>
    </form>

    <div class="trainer-details">
        <h2>Your Assigned Trainer</h2>
        <?php if ($trainers): ?>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($trainers['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($trainers['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($trainers['phone']); ?></p>
        <?php else: ?>
            <p>You do not have an assigned trainer. Please contact the admin.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
