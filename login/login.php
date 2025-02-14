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
    $usertype = htmlspecialchars(trim($_POST['user_type']));


    // Validate input fields
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email, Password, and User Type are required."; 
    } else {
        $userFound = false; // To track if the user is found
   
        if ($usertype === 'admin') { 
            $query = "SELECT * FROM admin WHERE email = '$email'"; 
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) { 
                $admin = mysqli_fetch_assoc($result); 

                if(password_verify($password, $admin['password'])){
                    $_SESSION['id'] = $admin['id'];
                    $_SESSION['user_type'] = $usertype;
                    $_SESSION['email'] = $admin['email'];
                    header("Location: /admin/admin.php");                    
                    exit();
                } else {
                    echo "<script> 
                        alert('Invalid username or password!');
                        </script>"; 
                    header("Location: /login/login.php");
                    exit; 
                }
            } else { 
                echo "<script> 
                alert('Username not found. Please check your credentials.');
                </script>"; 
                exit; 
            }
        } else if ($usertype === 'trainers') { 
            $query = "SELECT * FROM trainers WHERE email = '$email'"; 
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) > 0) { 
                $trainers = mysqli_fetch_assoc($result); 
            
                if(password_verify($password, $trainers['password'])){
                    $_SESSION['id'] = $trainers['id'];
                    $_SESSION['user_type'] = $usertype;
                    $_SESSION['email'] = $trainers['email'];
                    header("Location: /Trainer/Trainer.php");                    
                    exit();
                } else { 
                    echo "<script> 
                    alert('Incorrect password. Please try again.'); 
                    </script>"; 
                    exit; 
                } 
            }
             else { 
                echo "<script> 
                alert('Username not found. Please check your credentials.'); 
                </script>"; 
                exit; 
            }
        } else if ($usertype === 'user') { 
            $query = "SELECT * FROM user WHERE email = '$email'"; 
            $result = mysqli_query($conn, $query); 

            if (mysqli_num_rows($result) > 0) { 
                $user = mysqli_fetch_assoc($result); 
                    
                if (password_verify($password, $user['password'])) { 
                    $_SESSION['email'] = $email; 
                    $_SESSION['id'] = $user['id'];
                    header("Location: ../user/user.php"); 
                    exit; 
                } else { 
                    echo "Incorrect password. Please try again";
                    exit; 
                } 
            } else { 
                echo "<script> 
                alert('Username not found. Please register first.'); 
                </script>"; 
                exit; 
            } 
        } else { 
            echo "<script> 
            alert('Invalid user type selected.'); 
            </script>"; 
            exit; 
        } 
    } 
}
    ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Management Login</title> 
    <link rel="stylesheet" href="../login/login.css">
    <link rel="stylesheet" href="../header/header.css">

</head>
<body>
<header class="header" id="header">
    <div class="logo">
        <a href="../home/home.php">
            <img src="../image/logo.png" alt="EGO THRUST Logo">EGO THRUST
        </a>
    </div>
</header>

   
    <div class="form">
        <h2>LOGIN</h2>
        

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

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required><br>
                <br>
                <label for="user_type">User Type</label>
                <select id="user_type" name="user_type" required>
                    <option value="">Select</option>
                    <option value="user">user</option>
                    <option value="admin">admin</option>
                    <option value="trainers">trainer</option>
                </select>
            </div>
            <button type="submit" id="login">Login</button>
           <h2>Don't have an account?   <a href="../registration/registration.php"><span>Register</span></a></h2>
        </form>
    </div>
</body>
</html>