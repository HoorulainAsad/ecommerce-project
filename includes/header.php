<?php
// includes/header.php

// Ensure functions are loaded (which starts session and includes config/database)
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../classes/CartManager.php';
$cartManager = new CartManager();
$cartItemCount = $cartManager->getTotalCartItemCount();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MSGM Bridal & Formalwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid container-xl">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">
            <!-- Logo placeholder -->
            <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="MSGM Bridal Logo"  onerror="this.onerror=null;this.src='https://placehold.co/150x40/E9E3CE/7F0E10?text=MSGM+Bridal';">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="<?php echo BASE_URL; ?>index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>category.php?name=bridal">Bridal</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>category.php?name=formal">Formal</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>category.php?name=partywear">Partywear</a>
                </li>
            </ul>
            <form class="d-flex me-3" role="search" action="<?php echo BASE_URL; ?>search.php" method="GET">
                <input class="form-control me-2" type="search" name="q" placeholder="Search by dress name" aria-label="Search">
                <button class="btn btn-outline-success" type="submit"><i class="fas fa-search"></i></button>
            </form>
            <div class="d-flex align-items-center">
                <?php if (isUserLoggedIn()): ?>
                    <span class="user-greeting me-3">Hello, <?php echo htmlspecialchars(getLoggedInUsername()); ?>!</span>
                <a href="<?php echo BASE_URL; ?>logout.php" class="nav-link logout-btn">Logout</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>login.php" class="nav-link">Login/Signup</a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>cart.php" class="cart-icon position-relative">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-item-count">
                        <?php echo $cartItemCount; ?>
                    </span>
                </a>
            </div>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
