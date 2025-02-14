<?php
// Start session
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
    $_SESSION['error'] = "You must be logged in to renew a membership.";
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

$user_id = $_SESSION['id'];

// Check if the user has an active or approved membership
$current_membership_query = $conn->query("SELECT s.id, s.expiry_date, m.type FROM subscription s JOIN memberships m ON s.membership_id = m.id WHERE s.user_id = $user_id AND s.status = 'approved' ORDER BY s.expiry_date DESC LIMIT 1");

if ($current_membership_query && $current_membership_query->num_rows > 0) {
    $current_membership = $current_membership_query->fetch_assoc();
    $current_membership_type = $current_membership['type'];
    $current_expiry_date = $current_membership['expiry_date'];
} else {
    // Redirect to the purchase page if no active membership exists
    header("Location: membership.php");
    exit();
}

// Handle renewal form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $duration = mysqli_real_escape_string($conn, $_POST['membership_duration']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment']);

    $duration_mapping = [
        '1month' => 1,
        '3months' => 3,
        '12months' => 12
    ];
    $duration_prices = [
        '1month' => 3000,
        '3months' => 7000,
        '12months' => 20000
    ];

    if (!isset($duration_mapping[$duration])) {
        $_SESSION['error'] = "Invalid duration selected.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $duration_months = $duration_mapping[$duration];
    $additional_price = $duration_prices[$duration];

    // Calculate the new expiry date
    $new_expiry_date = date('Y-m-d', strtotime("$current_expiry_date +$duration_months months"));
    $total_price = $additional_price;

    // Insert renewal into the subscription table
    $sql = "INSERT INTO subscription (membership_id, user_id, status, duration, payment_status, subscription_date, expiry_date, total_price)
            VALUES ((SELECT id FROM memberships WHERE type = '$current_membership_type'), $user_id, 'pending', $duration_months, 'pending', '$current_expiry_date', '$new_expiry_date', '$total_price')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Membership renewal request submitted successfully. Please wait for admin approval.";
    } else {
        $_SESSION['error'] = "Failed to submit renewal request. Please try again.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renew Membership</title>
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
        if (isset($_SESSION['success'])) {
            echo "<div class='success'>" . $_SESSION['success'] . "</div>";
            unset($_SESSION['success']);
        } elseif (isset($_SESSION['error'])) {
            echo "<div class='error'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']);
        }
        ?>
    </div>
    <div class="form-group">
        <p><strong>Current Membership:</strong> <?php echo $current_membership_type; ?></p>
        <p><strong>Expiry Date:</strong> <?php echo $current_expiry_date; ?></p>
    </div>

    <div class="form-group">
        <label for="membership_duration">Renewal Duration</label>
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

    <button type="submit">Renew Membership</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const membershipDurationSelect = document.getElementById('membership_duration');
    const totalAmountElement = document.getElementById('total_amount');

    const durationPrices = {
        '1month': 3000,
        '3months': 7000,
        '12months': 20000
    };

    function calculateTotal() {
        const duration = membershipDurationSelect.value;
        if (duration) {
            const totalPrice = durationPrices[duration] || 0;
            totalAmountElement.textContent = `Nrs ${totalPrice}`;
        } else {
            totalAmountElement.textContent = 'Nrs 0';
        }
    }

    membershipDurationSelect.addEventListener('change', calculateTotal);
});
</script>

</body>
</html>
