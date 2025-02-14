<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Management System</title>
    <link rel="stylesheet" href="home.css"> 
    <link rel="stylesheet" href="../header/header.css">
</head>
<body>
    <header>
        <?php include "../header/Header.php" ?>
    </header>

    <main>
        <section class="hero">
            <h1>Welcome to Our Gym Management System</h1>
            <p>Your fitness journey begins here. Explore our programs and become a member today!</p>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Gym Management System. All rights reserved.</p>
    </footer>
</body>
</html>
