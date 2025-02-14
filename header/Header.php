<?php $loggedIn = $_SESSION['user_id']; ?>

 <nav>
 <ul>
     <li><a href="home.php">Home</a></li>
     <li><a href="choose_us.php">Choose Us</a></li>
     <li><a href="programs.php">Programs</a></li>
     <li><a href="../membership.php">Buy Membership</a></li>
     <?php if ($loggedIn): ?>
         <li><a href="../logout.php">Logout</a></li>
     <?php else: ?>
         <li><a href="../login.php">Login</a></li>
         <li><a href="../registration.php">Registration</a></li>
     <?php endif; ?>
 </ul>
</nav>
