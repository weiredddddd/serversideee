<?php
require_once '../config/session_config.php';
require_once '../config/db.php';

// Admin check
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    header("Location: ../users/login.php");
    exit();
}

if (isset($_GET['comment_id']) && is_numeric($_GET['comment_id']) && isset($_GET['post_id'])) {
    $comment_id = (int)$_GET['comment_id'];
    $post_id = (int)$_GET['post_id'];
    
    try {
        // Delete the comment
        $stmt = $communityDB->prepare("DELETE FROM post_comments WHERE comment_id = ?");
        $stmt->execute([$comment_id]);
        
        header("Location: community.php?tab=discussions&deleted=success&post_id=$post_id");
    } catch (PDOException $e) {
        header("Location: community.php?error=" . urlencode("Error deleting comment: " . $e->getMessage()));
    }
} else {
    header("Location: community.php?error=" . urlencode("Invalid parameters."));
}
exit();
?>