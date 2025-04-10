<?php
require_once '../config/session_config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Prevent browser from caching logged-in state
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Fetch user's recipes
require_once '../config/db.php';
$user_id = $_SESSION['user_id'];

// Modify the user fetch query to include registration_date
$stmt = $usersDB->prepare("SELECT email, avatar, registration_date FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_email = $user['email'] ?? 'Not Available';
$avatar_id = $user['avatar'] ?? 0;
$registration_date = $user['registration_date'] ?? null;

// Format the registration date nicely
$member_since = $registration_date ? date('F Y', strtotime($registration_date)) : 'Unknown';

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
$stmt = $RecipeDB->prepare("SELECT * FROM Recipes WHERE user_id = ? ORDER BY created_at DESC");
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
    <?php include_once '../includes/navigation.php'; ?>
    <!-- Display success message if set -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                    <p><strong>Member Since:</strong> <?= htmlspecialchars($member_since) ?></p>
                    <!-- Delete Account Button -->
                    <button class="btn btn-danger w-100 mt-2" onclick="confirmDelete()">Delete Account</button>
                    <div class="d-grid gap-2 mt-3">
                        <a href="edit_profile.php" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Profile
                        </a>
                        <a href="change_password.php" class="btn btn-outline-secondary mt-2">
                            <i class="bi bi-key"></i> Change Password
                        </a>
                    </div>
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

        <!-- Middle Section (Activity Log) -->
    <div class="col-lg-6">
        <div class="card p-3 shadow-sm">
            <h5 class="mb-3">Activity Log</h5>
            <a href="../recipes/manage.php" class="btn btn-primary w-100 mb-3">Manage My Recipes</a>

            <?php if (empty($recipes)): ?>
                <p class="text-muted">You haven't added any recipes yet.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($recipes as $recipe): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <div><strong><?= htmlspecialchars($recipe['title']) ?></strong></div>
                                <small class="text-muted">
                                    Added on <?= date('F j, Y', strtotime($recipe['created_at'])) ?>
                                    <?php if (!empty($recipe['updated_at']) && $recipe['updated_at'] !== $recipe['created_at']): ?>
                                        • Edited on <?= date('F j, Y', strtotime($recipe['updated_at'])) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <a href="../recipes/view.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
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
    <script>
    function confirmDelete() {
        if (confirm("Are you sure you want to delete your account? This action cannot be undone!")) {
            window.location.href = '../users/delete_account.php';
        }
    }
    </script>
</body>
</html>