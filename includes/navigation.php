<?php
// Include session config instead of calling session_start directly
require_once __DIR__ . '/../config/session_config.php';

// Define base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

// Get current script path.
$scriptPath = $_SERVER['SCRIPT_NAME'];

// Remove the filename (e.g., recipes.php), leave the base folder
$projectFolder = explode('/', trim($scriptPath, '/'))[0];

// Define BASE_URL
define('BASE_URL', $protocol . "://" . $_SERVER['HTTP_HOST'] . '/' . $projectFolder);

// Get nickname if available
if (isset($_SESSION['user_id']) && !isset($_SESSION['nickname'])) {
    // Fetch nickname on first page load after login
    require_once __DIR__ . '/../config/db.php';
    $stmt = $usersDB->prepare("SELECT nickname FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['nickname'] = $user['nickname'] ?? $_SESSION['username'];
}

// Use nickname for display, falling back to username if not available
$display_name = $_SESSION['nickname'] ?? $_SESSION['username'] ?? '';
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
                        <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($display_name) ?>
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
                    <a class="nav-link" href="<?= BASE_URL ?>/competition/competitions.php">Competitions</a>
                </li>
            </ul>
        </div>
    </div>
</nav>