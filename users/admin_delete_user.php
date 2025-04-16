<?php
require_once '../config/session_config.php';
require_once '../config/db.php';

// Admin check
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== '1') {
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
    $userId = $user['user_id'];

    // 2. Delete competitions created by user and their dependencies
    $stmt = $competitionDB->prepare("SELECT competition_id FROM competitions WHERE created_by = ?");
    $stmt->execute([$userId]);
    $competitions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($competitions as $compId) {
        // Delete votes in this competition's entries
        $stmt = $competitionDB->prepare("
            DELETE v FROM votes v
            INNER JOIN competition_entries e ON v.entry_id = e.entry_id
            WHERE e.competition_id = ?
        ");
        $stmt->execute([$compId]);

        // Delete competition entries
        $stmt = $competitionDB->prepare("DELETE FROM competition_entries WHERE competition_id = ?");
        $stmt->execute([$compId]);

        // Delete competition itself
        $stmt = $competitionDB->prepare("DELETE FROM competitions WHERE competition_id = ?");
        $stmt->execute([$compId]);
    }

    // 3. Delete votes on user's entries (in other competitions)
    $stmt = $competitionDB->prepare("SELECT entry_id FROM competition_entries WHERE user_id = ?");
    $stmt->execute([$userId]);
    $entries = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($entries)) {
        $placeholders = implode(',', array_fill(0, count($entries), '?'));
        $stmt = $competitionDB->prepare("DELETE FROM votes WHERE entry_id IN ($placeholders)");
        $stmt->execute($entries);
    }

    // 4. Delete user's competition entries
    $stmt = $competitionDB->prepare("DELETE FROM competition_entries WHERE user_id = ?");
    $stmt->execute([$userId]);

    // 5. Delete votes made BY the user
    $stmt = $competitionDB->prepare("DELETE FROM votes WHERE user_id = ?");
    $stmt->execute([$userId]);

    // 6. Delete recipes
    $stmt = $RecipeDB->prepare("DELETE FROM Recipes WHERE user_id = ?");
    $stmt->execute([$userId]);

    // 7. Finally, delete the user
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