<?php
require_once '../config/session_config.php';
require_once '../config/db.php';

// Admin check
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    header("Location: login.php");
    exit();
}

$admin_username = $_SESSION['username'];
$username_to_delete = filter_input(INPUT_GET, 'username', FILTER_SANITIZE_STRING);

if (empty($username_to_delete)) {
    $_SESSION['error_message'] = "Invalid username";
    header("Location: admin_users.php");
    exit();
}

// Prevent self-deletion
if ($username_to_delete === $admin_username) {
    $_SESSION['error_message'] = "You cannot delete yourself!";
    header("Location: admin_users.php");
    exit();
}

try {
    $usersDB->beginTransaction();

    // 1. Get user_id for deletion
    $stmt = $usersDB->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$username_to_delete]);
    $user = $stmt->fetch();
    
    if (!$user) throw new Exception("User not found");

    // 2. Delete votes from competitionDB
    $stmt = $competitionDB->prepare("DELETE FROM votes WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);

    // 3. Delete competition entries
    $stmt = $competitionDB->prepare("DELETE FROM competition_entries WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);

    // 4. Delete recipes
    $stmt = $RecipeDB->prepare("DELETE FROM Recipes WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);

    // 5. Delete user
    $stmt = $usersDB->prepare("DELETE FROM users WHERE username = ?");
    $stmt->execute([$username_to_delete]);

    $usersDB->commit();
    $_SESSION['success_message'] = "User '$username_to_delete' deleted successfully";
} catch (Exception $e) {
    $usersDB->rollBack();
    $_SESSION['error_message'] = "Deletion failed: " . $e->getMessage();
}

header("Location: admin_users.php");
exit();
?>