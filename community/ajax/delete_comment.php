<?php
// Initialize session and include required files
require_once '../../config/session_config.php';
require_once '../../config/db.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Check if comment_id is provided
if (!isset($_POST['comment_id']) || !is_numeric($_POST['comment_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid comment ID']);
    exit;
}

$comment_id = (int)$_POST['comment_id'];
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['is_admin'] ?? 0) === 1;

try {
    // First check if comment exists and belongs to user (or if user is admin)
    $stmt = $communityDB->prepare("
        SELECT post_id, user_id 
        FROM post_comments 
        WHERE comment_id = ?
    ");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$comment) {
        echo json_encode(['success' => false, 'error' => 'Comment not found']);
        exit;
    }
    
    // Verify ownership or admin status
    if ($comment['user_id'] != $user_id && !$is_admin) {
        echo json_encode(['success' => false, 'error' => 'You can only delete your own comments']);
        exit;
    }
    
    // Get post_id for updating comment count later
    $post_id = $comment['post_id'];
    
    // Delete the comment
    $delete_stmt = $communityDB->prepare("DELETE FROM post_comments WHERE comment_id = ?");
    $delete_stmt->execute([$comment_id]);
    
    // Get updated comment count
    $count_stmt = $communityDB->prepare("SELECT COUNT(*) as count FROM post_comments WHERE post_id = ?");
    $count_stmt->execute([$post_id]);
    $comment_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo json_encode([
        'success' => true, 
        'message' => 'Comment deleted successfully',
        'post_id' => $post_id,
        'comment_count' => $comment_count
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>