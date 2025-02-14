<?php
// Start a session to manage user login state
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

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

    // Validate input fields
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and Password are required.";
    } else {
        $sql = "SELECT id, password FROM user WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result === false) {
            die("Query failed: " . $conn->error);
        }


        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['success'] = "Login successful!";
                header("Location: user/user.php"); // Redirect to the dashboard
                exit();
            } else {
                $_SESSION['error'] = "Invalid password.";
            }
        } else {
            $_SESSION['error'] = "No user found with this email.";
        }
    }

    // Redirect to avoid resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Management Login</title>                 
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-container .form-group {
            margin-bottom: 15px;
        }
        .form-container label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-container button {
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="#">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
