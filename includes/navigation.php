<?php
// Include session config instead of calling session_start directly
require_once __DIR__ . '/../config/session_config.php';

// Define base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
define('BASE_URL', $protocol . "://" . $_SERVER['HTTP_HOST'] . '/ServerSide/serversideee');
?>

<!-- Top Navbar (First Layer) -->
<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
    <div class="container">
        <!-- Website Name -->
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/index.php">NoiceFoodie</a>

        <!-- Search Bar -->
        <form class="d-flex mx-auto" action="<?= BASE_URL ?>/recipes/recipes.php" method="GET">
            <input class="form-control me-2" type="search" name="search" placeholder="Search recipes..." aria-label="Search">
            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
        </form>

        <!-- User Account Section -->
        <ul class="navbar-nav">
            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['username'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/users/profile.php">Profile</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/users/logout.php">Logout</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/users/login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/users/register.php">Register</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- Main Navbar (Second Layer) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/recipes/recipes.php">Recipes</a>
                </li>
                <li class="nav-item">
    <a class="nav-link" href="<?= BASE_URL ?>/meal/schedule.php">Meal Planning</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/community/community.php">Community</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/competitions.php">Competitions</a>
                </li>
            </ul>
        </div>
    </div>
</nav>