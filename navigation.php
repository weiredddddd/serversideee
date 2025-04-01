<?php
session_start();

// Define base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
define('BASE_URL', $protocol . "://" . $_SERVER['HTTP_HOST'] . '/asm');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Ensure dropdown menus appear above other content */
        .dropdown-menu {
            z-index: 1100;
        }
    </style>
</head>
<body>

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
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                    <a class="nav-link active" href="<?= BASE_URL ?>/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/recipes/recipes.php">Recipes</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="mealPlanningDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Meal Planning
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="mealPlanningDropdown">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/meal/planning.php">Plan a Meal</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/meal/schedule.php">View Schedule</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/community.php">Community</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/competitions.php">Competitions</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Load Bootstrap JS bundle (includes Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize all dropdowns
        var dropdowns = document.querySelectorAll('.dropdown-toggle');
        dropdowns.forEach(function(dropdown) {
            dropdown.addEventListener('click', function(e) {
                e.preventDefault();
                var dropdownMenu = this.nextElementSibling;
                if (dropdownMenu.classList.contains('show')) {
                    dropdownMenu.classList.remove('show');
                } else {
                    dropdownMenu.classList.add('show');
                }
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.matches('.dropdown-toggle') && !e.target.closest('.dropdown-menu')) {
                document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
                    menu.classList.remove('show');
                });
            }
        });
    });
</script>

</body>
</html>