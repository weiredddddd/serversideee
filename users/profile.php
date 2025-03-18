<?php
session_start();
include 'navigation.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../index.php">NoiceFoodie</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../recipes/add_recipe.php">Add Recipe</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_recipes.php">Manage My Recipes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-danger text-white" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Profile Header -->
<div class="container mt-4">
    <div class="card p-4 shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <img src="https://via.placeholder.com/100" class="rounded-circle img-fluid" alt="Profile Picture">
            </div>
            <div class="col-md-10">
                <h2><?= $_SESSION['username'] ?>'s Profile</h2>
                <p class="text-muted">Welcome to your recipe dashboard.</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Section -->
<div class="container mt-4">
    <div class="row">
        <!-- Left Sidebar (User Info) -->
        <div class="col-md-3">
            <div class="card p-3 shadow-sm">
                <h5>Profile Info</h5>
                <p><strong>Username:</strong> <?= $_SESSION['username'] ?></p>
                <p><strong>Email:</strong> user@example.com</p>
                <p><strong>Member Since:</strong> January 2025</p>
            </div>
        </div>

        <!-- Middle Section (Posts) -->
        <div class="col-md-6">
            <div class="card p-3 shadow-sm">
                <h5>My Recipes</h5>
                <p class="text-muted">View and manage the recipes you've posted.</p>
                <a href="manage_recipes.php" class="btn btn-primary w-100">Manage My Recipes</a>
            </div>

            <div class="card p-3 shadow-sm mt-3">
                <h5>Rated Posts</h5>
                <p class="text-muted">Recipes you have rated.</p>
                <div class="border p-2 bg-white">[Rated Posts Section - Developer Module Placeholder]</div>
            </div>

            <div class="card p-3 shadow-sm mt-3">
                <h5>Commented Posts</h5>
                <p class="text-muted">Recipes you have commented on.</p>
                <div class="border p-2 bg-white">[Commented Posts Section - Developer Module Placeholder]</div>
            </div>
        </div>

        <!-- Right Sidebar (Future Features) -->
        <div class="col-md-3">
            <div class="card p-3 shadow-sm">
                <h5>Upcoming Features</h5>
                <p class="text-muted">New features will be added soon.</p>
                <ul>
                    <li>Follow other users</li>
                    <li>Bookmark favorite recipes</li>
                    <li>Participate in competitions</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
