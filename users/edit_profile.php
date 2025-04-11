<?php
require_once '../config/session_config.php';
require_once '../config/db.php'; 

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Fetch current user data
$stmt = $usersDB->prepare("SELECT username, email FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Preset avatars
$preset_avatars = [
    'avatar1.png',
    'avatar2.png',
    'avatar3.png',
    'avatar4.png',
    'avatar5.png'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $avatar_choice = (int)$_POST['avatar'];
    
    // Validate username
    if (empty($new_username)) {
        $errors[] = "Username cannot be empty";
    } elseif (strlen($new_username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    } elseif (strlen($new_username) > 30) {
        $errors[] = "Username cannot exceed 30 characters";
    } else {
        // Check if username is already taken (excluding current user)
        $stmt = $usersDB->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
        $stmt->execute([$new_username, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = "Username is already taken";
        }
    }
    
    // Validate avatar selection
    if (!isset($preset_avatars[$avatar_choice])) {
        $errors[] = "Invalid avatar selection";
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        try {
            $usersDB->beginTransaction();
            
            // Update username
            $stmt = $usersDB->prepare("UPDATE users SET username = ? WHERE user_id = ?");
            $stmt->execute([$new_username, $user_id]);
            
            // Update avatar in session (we'll use the index to remember)
            $_SESSION['avatar'] = $avatar_choice;
            $_SESSION['username'] = $new_username;
            
            $usersDB->commit();
            $success = true;
            $_SESSION['success_message'] = "Profile updated successfully!";
            header("Location: profile.php");
            exit();
        } catch (PDOException $e) {
            $usersDB->rollBack();
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .avatar-option {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.2s;
        }
        .avatar-option:hover {
            transform: scale(1.1);
        }
        .avatar-option.selected {
            border-color: #0d6efd;
        }
        .current-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ddd;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/navigation.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h4 class="mb-0">Edit Profile</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <p class="mb-1"><?= htmlspecialchars($error) ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="text-center mb-4">
                                <?php 
                                $current_avatar = $preset_avatars[$_SESSION['avatar'] ?? 0] ?? $preset_avatars[0];
                                ?>
                                <img src="../assets/avatars/<?= $current_avatar ?>" class="current-avatar mb-2" id="currentAvatarPreview">
                                <h5>Change Avatar</h5>
                            </div>
                            
                            <div class="row mb-4 justify-content-center">
                                <?php foreach ($preset_avatars as $index => $avatar): ?>
                                    <div class="col-4 col-sm-3 col-md-2 text-center mb-3">
                                        <img src="../assets/avatars/<?= $avatar ?>" 
                                             class="avatar-option <?= ($index == ($_SESSION['avatar'] ?? 0)) ? 'selected' : '' ?>"
                                             onclick="selectAvatar(<?= $index ?>, '<?= $avatar ?>')">
                                        <input type="radio" name="avatar" value="<?= $index ?>" 
                                               <?= ($index == ($_SESSION['avatar'] ?? 0)) ? 'checked' : '' ?>
                                               style="display: none;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= htmlspecialchars($_POST['username'] ?? $user['username']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                <small class="text-muted">Contact support to change your email</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <a href="profile.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectAvatar(index, avatarPath) {
            // Update visual selection
            document.querySelectorAll('.avatar-option').forEach(el => {
                el.classList.remove('selected');
            });
            event.target.classList.add('selected');
            
            // Update the radio button
            document.querySelector(`input[name="avatar"][value="${index}"]`).checked = true;
            
            // Update preview
            document.getElementById('currentAvatarPreview').src = `../assets/avatars/${avatarPath}`;
        }
    </script>

    <?php include_once '../includes/footer.php'; ?>
</body>
</html>