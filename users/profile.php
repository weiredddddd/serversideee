<?php
session_start();
//
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include navigation bar
include '../navigation.php'; // Adjust path if needed

// Fetch user's recipes
require '../config/db.php'; // Include database connection
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM Recipes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Floating "Add Recipe" button */
        .floating-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .recipe-card {
            transition: transform 0.2s;
        }
        .recipe-card:hover {
            transform: scale(1.02);
        }
    </style>
</head>
<body class="bg-light">

<!-- Profile Header -->
<div class="container mt-4">
    <div class="card p-4 shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <img src="https://via.placeholder.com/100" class="rounded-circle img-fluid" alt="Profile Picture">
            </div>
            <div class="col-md-10">
                <h2><?= htmlspecialchars($_SESSION['username']) ?>'s Profile</h2>
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
                <p><strong>Username:</strong> <?= htmlspecialchars($_SESSION['username']) ?></p>
                <p><strong>Email:</strong> user@example.com</p>
                <p><strong>Member Since:</strong> January 2025</p>
            </div>
        </div>

        <!-- Middle Section (Posts) -->
        <div class="col-md-6">
            <div class="card p-3 shadow-sm">
                <h5>My Recipes</h5>
                <p class="text-muted">View and manage the recipes you've posted.</p>
                <a href="../recipes/manage.php" class="btn btn-primary w-100">Manage My Recipes</a>

                <!-- Display User's Recipes -->
                <?php if (empty($recipes)): ?>
                    <p class="text-muted mt-3">No recipes found.</p>
                <?php else: ?>
                    <div class="mt-3">
                        <?php foreach ($recipes as $recipe): ?>
                            <div class="card recipe-card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($recipe['description']) ?></p>
                                    <p class="card-text"><small class="text-muted">Category: <?= htmlspecialchars($recipe['category']) ?></small></p>
                                    <a href="../recipes/view.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-primary">View Recipe</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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

<!-- Floating "Add Recipe" Button -->
<a href="../recipes/add_recipe.php" class="btn btn-primary floating-btn">
    <i class="bi bi-plus-lg"></i> Add Recipe
</a>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>