<?php
// Start a session to manage user login state
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'trainers') {
    $_SESSION['error'] = "You must be logged in as an trainer to access this page.";
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

// Fetch the trainer's ID from session or authentication (replace with actual logic)
$trainers = $_SESSION['id']; // Replace with actual session variable for the logged-in trainer

// Check if trainer is logged in
if (empty($trainers)) {
    die("Trainer not logged in.");
}

// Fetch all workout plans and diet plans for the trainer
$workout_plans_query = "SELECT * FROM workout_plans WHERE trainers_id = ?";
$diet_plans_query = "SELECT * FROM diet_plans WHERE trainers_id = ?";

// Prepare statements
$stmt_workout = $conn->prepare($workout_plans_query);
$stmt_diet = $conn->prepare($diet_plans_query);

// Bind parameters
$stmt_workout->bind_param("i", $trainers);
$stmt_diet->bind_param("i", $trainers);

// // Execute queries
// $stmt_workout->execute();
// $stmt_diet->execute();
// $workout_result = $stmt_workout->get_result();
// $diet_result = $stmt_workout->get_result();
// // Get results
// $workout_plans = $workout_result->fetch_all(MYSQLI_ASSOC);
// $diet_plans = $diet_result->fetch_all(MYSQLI_ASSOC);

// Execute and fetch workout plans
if ($stmt_workout->execute()) {
    $workout_result = $stmt_workout->get_result();
    if ($workout_result) {
        $workout_plans = $workout_result->fetch_all(MYSQLI_ASSOC);
    } else {
        $workout_plans = [];
        echo "Failed to fetch workout plans: " . $conn->error;
    }
} else {
    echo "Workout plans query failed: " . $stmt_workout->error;
}

// Execute and fetch diet plans
if ($stmt_diet->execute()) {
    $diet_result = $stmt_diet->get_result();
    if ($diet_result) {
        $diet_plans = $diet_result->fetch_all(MYSQLI_ASSOC);
    } else {
        $diet_plans = [];
        echo "Failed to fetch diet plans: " . $conn->error;
    }
} else {
    echo "Diet plans query failed: " . $stmt_diet->error;
}

// Handle form submissions for adding, updating, or deleting
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add Workout Plan
    if (isset($_POST['add_workout'])) {
        $title = $_POST['workout_title'];
        $description = $_POST['workout_description'];
        $add_workout_query = "INSERT INTO workout_plans (trainers, title, description) VALUES (?, ?, ?)";
        $stmt_add_workout = $conn->prepare($add_workout_query);
        $stmt_add_workout->bind_param("iss", $trainers, $title, $description);
        $stmt_add_workout->execute();
    }

    // Add Diet Plan
    if (isset($_POST['add_diet'])) {
        $title = $_POST['diet_title'];
        $description = $_POST['diet_description'];
        $add_diet_query = "INSERT INTO diet_plans (trainers, title, description) VALUES (?, ?, ?)";
        $stmt_add_diet = $conn->prepare($add_diet_query);
        $stmt_add_diet->bind_param("iss", $trainers, $title, $description);
        $stmt_add_diet->execute();
    }

    // Delete Workout Plan
    if (isset($_POST['delete_workout'])) {
        $id = $_POST['workout_id'];
        $delete_workout_query = "DELETE FROM workout_plans WHERE id = ? AND trainers = ?";
        $stmt_delete_workout = $conn->prepare($delete_workout_query);
        $stmt_delete_workout->bind_param("ii", $id, $trainers_id);
        $stmt_delete_workout->execute();
    }

    // Delete Diet Plan
    if (isset($_POST['delete_diet'])) {
        $id = $_POST['diet_id'];
        $delete_diet_query = "DELETE FROM diet_plans WHERE id = ? AND trainers = ?";
        $stmt_delete_diet = $conn->prepare($delete_diet_query);
        $stmt_delete_diet->bind_param("ii", $id, $trainers);
        $stmt_delete_diet->execute();
    }

    // Update Workout Plan
    if (isset($_POST['update_workout'])) {
        $id = $_POST['workout_id'];
        $title = $_POST['workout_title'];
        $description = $_POST['workout_description'];
        $update_workout_query = "UPDATE workout_plans SET title = ?, description = ? WHERE id = ? AND trainers = ?";
        $stmt_update_workout = $conn->prepare($update_workout_query);
        $stmt_update_workout->bind_param("ssii", $title, $description, $id, $trainers);
        $stmt_update_workout->execute();
    }

    // Update Diet Plan
    if (isset($_POST['update_diet'])) {
        $id = $_POST['diet_id'];
        $title = $_POST['diet_title'];
        $description = $_POST['diet_description'];
        $update_diet_query = "UPDATE diet_plans SET title = ?, description = ? WHERE id = ? AND trainers = ?";
        $stmt_update_diet = $conn->prepare($update_diet_query);
        $stmt_update_diet->bind_param("ssii", $title, $description, $id, $trainers);
        $stmt_update_diet->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Dashboard</title>
    <link rel="stylesheet" href="Train.css">
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
            <!-- <li><a href=""> Trainee</a></li>   -->
        </ul>
    </div>

    <div class="login">
        <ul>
             <li><a href="../logout.php">Logout</a></li>
        </ul>
        
    </div>
</header>
    <h1>Trainer Dashboard</h1>

    <!-- Workout Plans Section -->
    <h2>Workout Plans</h2>
    <form method="POST">
        <input type="text" name="workout_title" placeholder="Workout Plan Title" required>
        <textarea name="workout_description" placeholder="Workout Plan Description" required></textarea>
        <button type="submit" name="add_workout">Add Workout Plan</button>
    </form>

    <ul>
        <?php foreach ($workout_plans as $plan): ?>
            <li>
                <h3><?php echo htmlspecialchars($plan['title']); ?></h3>
                <p><?php echo htmlspecialchars($plan['description']); ?></p>
                <form method="POST">
                    <input type="hidden" name="workout_id" value="<?php echo $plan['id']; ?>">
                    <input type="text" name="workout_title" value="<?php echo htmlspecialchars($plan['title']); ?>">
                    <textarea name="workout_description"><?php echo htmlspecialchars($plan['description']); ?></textarea>
                    <button type="submit" name="update_workout">Update</button>
                    <button type="submit" name="delete_workout">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Diet Plans Section -->
    <h2>Diet Plans</h2>
    <form method="POST">
        <input type="text" name="diet_title" placeholder="Diet Plan Title" required>
        <textarea name="diet_description" placeholder="Diet Plan Description" required></textarea>
        <button type="submit" name="add_diet">Add Diet Plan</button>
    </form>

    <ul>
        <?php foreach ($diet_plans as $plan): ?>
            <li>
                <h3><?php echo htmlspecialchars($plan['title']); ?></h3>
                <p><?php echo htmlspecialchars($plan['description']); ?></p>
                <form method="POST">
                    <input type="hidden" name="diet_id" value="<?php echo $plan['id']; ?>">
                    <input type="text" name="diet_title" value="<?php echo htmlspecialchars($plan['title']); ?>">
                    <textarea name="diet_description"><?php echo htmlspecialchars($plan['description']); ?></textarea>
                    <button type="submit" name="update_diet">Update</button>
                    <button type="submit" name="delete_diet">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

</body>
</html>
