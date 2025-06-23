<?php
// admin/navbar.php
// This file assumes session_start() has been called and isLoggedIn() is available.
// It's included in other admin files.

// Optional: Get logged-in username
$admin_username = $_SESSION[ADMIN_USERNAME_SESSION_KEY] ?? 'Admin';
?>
<div class="hamburger" >&#9776;</div>  
<div class="navbar">
    <div class="navbar-brand">ADMIN DASHBOARD</div>
    <div class="navbar-user">
        Welcome, <?php echo htmlspecialchars($admin_username); ?>!
        <a href="<?php echo BASE_URL; ?>admin/logout.php" class="logout-btn">Logout</a>
    </div>
</div>
