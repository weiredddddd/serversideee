<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include navigation bar
include '../navigation.php';

// Fetch user's recipes
require '../config/db.php';
$user_id = $_SESSION['user_id'];

// Fetch user's email and avatar
$stmt = $pdo->prepare("SELECT email, avatar FROM Users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_email = $user['email'] ?? 'Not Available';
$avatar_id = $user['avatar'] ?? 0; // Default to 0 if not set

// Preset avatars (should match the ones in edit_profile.php)
$preset_avatars = [
    'avatar1.png',
    'avatar2.png',
    'avatar3.png',
    'avatar4.png',
    'avatar5.png',
    'avatar6.png'
];

// Get the current avatar image
$current_avatar = $preset_avatars[$avatar_id] ?? $preset_avatars[0];

// Fetch user's recipes
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        /* Floating "Add Recipe" button */
        .floating-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            padding: 12px 20px;
            border-radius: 50px;
        }
        /* Hover effect for cards */
        .recipe-card {
            transition: transform 0.2s;
        }
        .recipe-card:hover {
            transform: scale(1.03);
        }
        /* Profile Image */
        .profile-img-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #ddd;
        }
        /* Profile Header */
        .profile-header {
            text-align: center;
        }
        /* Sidebar Styling */
        .sidebar-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        /* Success message */
        .alert-success {
            margin: 20px auto;
            max-width: 800px;
        }
    </style>
</head>
<body class="bg-light">

<!-- Display success message if set -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success text-center">
        <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<!-- Profile Header -->
<div class="container mt-4">
    <div class="card p-4 shadow-sm">
        <div class="profile-img-container">
            <img src="../assets/avatars/<?= $current_avatar ?>" class="profile-img" alt="Profile Picture">
        </div>
        <div class="profile-header">
            <h2><?= htmlspecialchars($_SESSION['username']) ?>'s Profile</h2>
            <p class="text-muted">Welcome to your recipe dashboard.</p>
        </div>
    </div>
</div>

<!-- Main Content Section -->
<div class="container mt-4">
    <div class="row">
        <!-- Left Sidebar (User Info) -->
        <div class="col-lg-3">
            <div class="sidebar-card">
                <h5>Profile Info</h5>
                <p><strong>Username:</strong> <?= htmlspecialchars($_SESSION['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user_email) ?></p>
                <p><strong>Member Since:</strong> January 2025</p>
                <a href="../users/edit_profile.php" class="btn btn-outline-primary w-100 mt-2">Edit Profile</a>
            </div>

            <div class="sidebar-card">
                <h5>Upcoming Features</h5>
                <ul class="list-unstyled">
                    <li><i class="bi bi-people"></i> Follow other users</li>
                    <li><i class="bi bi-bookmark"></i> Bookmark favorite recipes</li>
                    <li><i class="bi bi-trophy"></i> Participate in competitions</li>
                </ul>
            </div>
        </div>

        <!-- Middle Section (User Recipes) -->
        <div class="col-lg-6">
            <div class="card p-3 shadow-sm">
                <h5 class="mb-3">My Recipes</h5>
                <a href="../recipes/manage.php" class="btn btn-primary w-100">Manage My Recipes</a>

                <!-- Display User's Recipes -->
                <?php if (empty($recipes)): ?>
                    <p class="text-muted mt-3">No recipes found.</p>
                <?php else: ?>
                    <div class="row mt-3">
                        <?php foreach ($recipes as $recipe): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card recipe-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
                                        <p class="card-text"><?= htmlspecialchars($recipe['description']) ?></p>
                                        <p class="text-muted"><small>Category: <?= htmlspecialchars($recipe['category']) ?></small></p>
                                        <a href="../recipes/view.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-sm btn-primary">View Recipe</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Sidebar (Rated & Commented Posts) -->
        <div class="col-lg-3">
            <div class="sidebar-card">
                <h5>Rated Recipes</h5>
                <p class="text-muted">Recipes you have rated.</p>
                <div class="border p-2 bg-white">[Rated Recipes Placeholder]</div>
            </div>

            <div class="sidebar-card">
                <h5>Commented Recipes</h5>
                <p class="text-muted">Recipes you have commented on.</p>
                <div class="border p-2 bg-white">[Commented Recipes Placeholder]</div>
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