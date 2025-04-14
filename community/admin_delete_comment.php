<?php
require_once '../config/session_config.php';
require_once '../config/db.php';

// Admin check
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['comment_id']) && is_numeric($_GET['comment_id']) && isset($_GET['recipe_id'])) {
    $comment_id = (int)$_GET['comment_id'];
    $recipe_id = (int)$_GET['recipe_id'];
    
    try {
        // Delete the comment
        $stmt = $communityDB->prepare("DELETE FROM recipe_comments WHERE comment_id = ?");
        $stmt->execute([$comment_id]);
        
        // Optionally delete associated rating if needed
        // $stmt = $communityDB->prepare("DELETE FROM recipe_ratings WHERE user_id = (SELECT user_id FROM recipe_comments WHERE comment_id = ?) AND recipe_id = ?");
        // $stmt->execute([$comment_id, $recipe_id]);

        header("Location: recipe_feedback.php?id=$recipe_id&deleted=success");
    } catch (PDOException $e) {
        header("Location: recipe_feedback.php?id=$recipe_id&error=" . urlencode("Error deleting comment: " . $e->getMessage()));
    }
} else {
    header("Location: community.php?error=" . urlencode("Invalid parameters."));
}
exit();
?>