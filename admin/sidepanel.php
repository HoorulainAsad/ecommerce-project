<?php
// admin/sidepanel.php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidepanel">
    <div class="sidepanel-header">
        <a href="<?php echo BASE_URL; ?>admin/index.php">Dashboard</a>
    </div>
    <ul class="sidepanel-nav">
        <li><a href="<?php echo BASE_URL; ?>admin/index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/addproduct.php" class="<?= ($current_page == 'addproduct.php') ? 'active' : '' ?>"><i class="fas fa-plus-circle"></i> Add Product</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/viewproducts.php" class="<?= ($current_page == 'viewproducts.php') ? 'active' : '' ?>"><i class="fas fa-tshirt"></i> View Products</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/managecategory.php" class="<?= ($current_page == 'managecategory.php') ? 'active' : '' ?>"><i class="fas fa-list-alt"></i> Manage Categories</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/vieworders.php" class="<?= ($current_page == 'vieworders.php') ? 'active' : '' ?>"><i class="fas fa-shopping-cart"></i> View Orders</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/userdetails.php" class="<?= ($current_page == 'userdetails.php') ? 'active' : '' ?>"><i class="fas fa-users"></i> User Details</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/customerfeedback.php" class="<?= ($current_page == 'customerfeedback.php') ? 'active' : '' ?>"><i class="fas fa-comments"></i> Customer Feedback</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/logout.php" class="<?= ($current_page == 'logout.php') ? 'active' : '' ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>
