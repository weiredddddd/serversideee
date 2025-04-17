<?php
require_once '../config/session_config.php';
require_once '../config/db.php';

// Set content type to JSON if it's an AJAX request
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
if ($is_ajax) {
    header('Content-Type: application/json');
}

// Admin check
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'error' => 'Admin access required']);
        exit();
    } else {
        header("Location: ../users/login.php");
        exit();
    }
}

if (isset($_GET['comment_id']) && is_numeric($_GET['comment_id']) && isset($_GET['recipe_id'])) {
    $comment_id = (int)$_GET['comment_id'];
    $recipe_id = (int)$_GET['recipe_id'];
    
    try {
        // Start a transaction for safer operations
        $communityDB->beginTransaction();
        
        // First get the user_id associated with this comment
        $user_stmt = $communityDB->prepare("SELECT user_id FROM recipe_comments WHERE comment_id = ?");
        $user_stmt->execute([$comment_id]);
        $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data) {
            $user_id = $user_data['user_id'];
            
            // Delete the associated rating
            $rating_stmt = $communityDB->prepare("DELETE FROM recipe_ratings WHERE user_id = ? AND recipe_id = ?");
            $rating_stmt->execute([$user_id, $recipe_id]);
        }
        
        // Delete the comment
        $stmt = $communityDB->prepare("DELETE FROM recipe_comments WHERE comment_id = ?");
        $stmt->execute([$comment_id]);
        
        // Commit the transaction
        $communityDB->commit();

        if ($is_ajax) {
            echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);
            exit();
        } else {
            header("Location: recipe_feedback.php?id=$recipe_id&deleted=success");
            exit();
        }
    } catch (PDOException $e) {
        // Roll back the transaction on error
        $communityDB->rollBack();
        
        if ($is_ajax) {
            echo json_encode(['success' => false, 'error' => "Error deleting comment: " . $e->getMessage()]);
            exit();
        } else {
            header("Location: recipe_feedback.php?id=$recipe_id&error=" . urlencode("Error deleting comment: " . $e->getMessage()));
            exit();
        }
    }
} else {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        exit();
    } else {
        header("Location: community.php?error=" . urlencode("Invalid parameters."));
        exit();
    }
}
?>