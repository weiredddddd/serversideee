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

// Modify the user fetch query to include nickname
$stmt = $usersDB->prepare("SELECT email, nickname, avatar, registration_date FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_email = $user['email'] ?? 'Not Available';
$nickname = $user['nickname'] ?? $_SESSION['username']; // Fall back to username if no nickname
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Profile Image */
        .profile-img-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #ddd;
        }
        /* Profile Header */
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        /* Profile Card Styling */
        .profile-card {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            margin: 0 auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }
        .profile-info {
            margin-bottom: 30px;
        }
        .profile-info p {
            font-size: 1.1rem;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .profile-info p:last-child {
            border-bottom: none;
        }
        .profile-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }
        /* Success message */
        .alert-success {
            margin: 20px auto;
            max-width: 600px;
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

    <!-- Main Content Section -->
    <div class="container mt-5">
        <div class="profile-card">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-img-container">
                    <img src="../assets/avatars/<?= $current_avatar ?>" class="profile-img" alt="Profile Picture">
                </div>
                <h2><?= htmlspecialchars($nickname) ?></h2>
                <p class="text-muted">Your Profile Information</p>
            </div>
            
            <!-- Profile Information -->
            <div class="profile-info">
                <p><strong>Username:</strong> <?= htmlspecialchars($_SESSION['username']) ?></p>
                <p><strong>Nickname:</strong> <?= htmlspecialchars($nickname) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user_email) ?></p>
                <p><strong>Member Since:</strong> <?= htmlspecialchars($member_since) ?></p>
            </div>
            
            <!-- Profile Actions -->
            <div class="profile-actions">
                <a href="edit_profile.php" class="btn btn-primary">
                    <i class="fas fa-user-edit"></i> Edit Profile
                </a>
                <a href="change_password.php" class="btn btn-outline-secondary">
                    <i class="fas fa-key"></i> Change Password
                </a>
            </div>
            
            <!-- Delete Account Button -->
            <button class="btn btn-danger w-100 mt-4" onclick="confirmDelete()">Delete Account</button>
        </div>
    </div>
    <script>
    function confirmDelete() {
        if (confirm("Are you sure you want to delete your account? This action cannot be undone!")) {
            window.location.href = '../users/delete_account.php';
        }
    }
    </script>
    <?php include_once '../includes/footer.php'; ?>
</body>
</html>