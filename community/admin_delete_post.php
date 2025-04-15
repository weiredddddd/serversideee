<?php
require_once '../config/session_config.php';
require_once '../config/db.php';

// Admin check
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['post_id']) && is_numeric($_GET['post_id'])) {
    $post_id = (int)$_GET['post_id'];
    
    try {
        // Delete comments first (due to foreign key constraints)
        $stmt = $communityDB->prepare("DELETE FROM post_comments WHERE post_id = ?");
        $stmt->execute([$post_id]);
        
        // Delete post
        $stmt = $communityDB->prepare("DELETE FROM discussion_posts WHERE post_id = ?");
        $stmt->execute([$post_id]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: community.php?deleted=success");
        } else {
            header("Location: community.php?error=" . urlencode("Post not found or already deleted."));
        }
    } catch (PDOException $e) {
        header("Location: community.php?error=" . urlencode("Error deleting post: " . $e->getMessage()));
    }
} else {
    header("Location: community.php?error=" . urlencode("Invalid post ID."));
}
exit();
?>