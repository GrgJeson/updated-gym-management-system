<?php
// Start session
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
    $_SESSION['error'] = "You must be logged in to purchase a membership.";
    header("Location: ../login/login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user has an approved/active membership
$user_id = $_SESSION['id'];
$check_membership_query = "SELECT expiry_date FROM subscription WHERE user_id = $user_id AND status = 'active' AND expiry_date > NOW()";
$check_result = $conn->query($check_membership_query);

if ($check_result && $check_result->num_rows > 0) {
    $row = $check_result->fetch_assoc();
    $expiry_date = date('Y-m-d', strtotime($row['expiry_date']));

    echo "<script>
        alert('You already have an active membership valid until $expiry_date. Please wait for it to expire before purchasing a new one.');
        window.location.href = '../user/dash.php';
    </script>";
    // header("Location: ../user/dash.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $membership_type = mysqli_real_escape_string($conn, $_POST['membership_type']);
    $duration = mysqli_real_escape_string($conn, $_POST['membership_duration']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment']);
    $success = "";
    $error = "";

    // Fetch membership ID and base price
    $membership_query = $conn->query("SELECT id, price FROM memberships WHERE type = '$membership_type'");
    if ($membership_query && $membership_row = $membership_query->fetch_assoc()) {
        $membership_id = $membership_row['id'];
        $base_price = $membership_row['price'];
    }

    // Duration mapping
    $duration_mapping = ['1month' => 1, '3months' => 3, '12months' => 12];
    $duration_prices = ['1month' => 3000, '3months' => 7000, '12months' => 20000];

    if (!isset($duration_mapping[$duration])) {
        $_SESSION['error'] = "Invalid duration selected.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $duration_months = $duration_mapping[$duration];
    $additional_price = $duration_prices[$duration];
    $total_price = ($base_price * $duration_months) + $additional_price;

    // Set subscription and expiry dates
    $subscription_date = date('Y-m-d');
    $expiry_date = date('Y-m-d', strtotime("+$duration_months months"));

    // Insert into the subscription table
    $sql = "INSERT INTO subscription (membership_id, user_id, status, duration, payment_status, subscription_date, expiry_date, total_price)
            VALUES ($membership_id, $user_id, 'pending', $duration_months, 'pending', '$subscription_date', '$expiry_date', '$total_price')";

    if ($conn->query($sql) === TRUE) {
        $success = "Your membership request has been submitted. Please wait for admin approval.";
    } else {
        $error = "Failed to process your membership request. Please try again.";
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Membership Purchase</title>
    <link rel="stylesheet" href="../header/header.css">
    <link rel="stylesheet" href="../membership/membership.css"> 
</head>
<body>
<header class="header" id="header">
    <div class="logo">
        <a href="../user/user.php">
            <img src="../image/logo.png" alt="EGO THRUST Logo">EGO THRUST
        </a>
    </div>
</header>

<form method="POST" action="">
    <div class="form-group">
    <?php
        if (isset($success)) {
            
    ?>
            <div class='success'><?php echo $success; ?></div>;
        
    <?php
        }
        else if (isset($error)) {
    ?>
            <div class='error'><?php echo $error; ?></div>;
    <?php
        }
    ?>
    </div>
    <div class="form-group">
        <label for="membership_type">Membership Type</label>
        <select id="membership_type" name="membership_type" required>
            <option value="">Select</option>
            <option value="Basic">Basic - Nrs0 (Access to gym facilities during working hours).</option>
            <option value="Premium">Premium - Nrs1250 (24/7 gym access, free group classes, and priority support).</option>
            <option value="VIP">VIP - Nrs2000 (All-inclusive access, personal trainer, and VIP amenities).</option>
        </select>
    </div>

    <div class="form-group">
        <label for="membership_duration">Membership Duration</label>
        <select id="membership_duration" name="membership_duration" required>
            <option value="">Select</option>
            <option value="1month">1 Month - Nrs3000</option>
            <option value="3months">3 Months - Nrs7000</option>
            <option value="12months">12 Months - Nrs20000</option>
        </select>
    </div>

    <div class="form-group">
        <label for="payment">Payment Method</label>
        <select id="payment" name="payment" required>
            <option value="cash">Offline Cash</option>
        </select>
    </div>

    <!-- Total Price Display -->
    <div class="form-group">
        <label>Total Amount</label>
        <p id="total_amount" style="color: white; font-weight: bold;">Nrs 0</p>
    </div>

    <button type="submit">Purchase</button>

    
</form>

</body>
<!-- Add this script at the end of the body -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const membershipTypeSelect = document.getElementById('membership_type');
    const membershipDurationSelect = document.getElementById('membership_duration');
    const totalAmountElement = document.getElementById('total_amount');

    const basePrices = {
        basic: 0,
        premium: 1250,
        vip: 2000
    };

    const durationPrices = {
        '1month': 3000,
        '3months': 7000,
        '12months': 20000
    };

    const durationMonths = {
        '1month': 1,
        '3months': 3,
        '12months': 12
    };

    function calculateTotal() {
        const membershipType = membershipTypeSelect.value;
        const duration = membershipDurationSelect.value;

        if (membershipType && duration) {
            const basePrice = basePrices[membershipType] || 0;
            const durationPrice = durationPrices[duration] || 0;
            const months = durationMonths[duration] || 0;

            const totalPrice = (basePrice * months) + durationPrice;
            totalAmountElement.textContent = Nrs ${totalPrice};
        } else {
            totalAmountElement.textContent = 'Nrs 0';
        }
    }

    membershipTypeSelect.addEventListener('change', calculateTotal);
    membershipDurationSelect.addEventListener('change', calculateTotal);
});
</script>

</html>