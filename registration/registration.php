<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Registration Form</title>
    <link rel="stylesheet" href="../registration/registration.css">

</head>
<body>
<nav class="nav">
    <header class="header" id="header">
    <div class="logo">
        <a href="../home/home.php">
            <img src="../image/logo.png" alt="EGO THRUST Logo">EGO THRUST
        </a>
    </div>
<!-- 
    <div class="nav" id="nav">
        <ul class="center">
            <li><a href="../home/home.php">Home</a></li>
            <li><a href="../home/home.php">Program</a></li>
            <li><a href="../home/home.php">Choose Us</a></li>
          
        </ul>
    </div> -->
</header>
<div class="form-container">
    <h2>Gym Registration</h2>
    <form action="#" method="POST">
        <label for="Full_Name">Full Name</label>
        <input type="text" id="Full_Name" name="Full_Name" placeholder="Enter your full name" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>
        
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>

        <label for="phone">Phone Number</label>
        <input type="text" id="phone" name="phone" placeholder="Enter your phone number" required>

        <label for="gender">Gender</label>
        <select id="gender" name="gender" required>
            <option value="">Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select>

        <button type="submit">Register</button>
        <h3>  if already have an account? <a href="../login/login.php"><span> Login</span></a></h3>
    </form>
</div>

</body>
</html>

<?php
session_start();
// register.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Full_Name = htmlspecialchars($_POST['Full_Name']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $phone = htmlspecialchars($_POST['phone']);
    $gender = htmlspecialchars($_POST['gender']);

    // Database connection
    $servername = "localhost";
    $username_db = "root";
    $password_db = "";
    $dbname = "gym";

    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $hash_password = password_hash($password, PASSWORD_BCRYPT);
    // Insert member data
    $insert_sql = "INSERT INTO user (Full_Name, email,password, phone, gender) VALUES (?, ?,?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sssss",$Full_Name, $email,$hash_password, $phone, $gender);

    if ($insert_stmt->execute()) {
        echo "<script>
        alert('Registration successful!');
        header('../login/login.php');
        </script>";
    } else {
        echo "Error: " . $insert_stmt->error;
    }

    $insert_stmt->close();
    $conn->close();
}
?>
