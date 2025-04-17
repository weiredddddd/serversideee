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

// Check if necessary parameters are provided
if (!isset($_POST['comment_id']) || !is_numeric($_POST['comment_id']) || 
    !isset($_POST['comment_text']) || trim($_POST['comment_text']) === '') {
    echo json_encode(['success' => false, 'error' => 'Invalid input parameters']);
    exit;
}

$comment_id = (int)$_POST['comment_id'];
$comment_text = trim($_POST['comment_text']);
$user_id = $_SESSION['user_id'];

try {
    // First check if comment exists and belongs to user
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
    
    // Verify ownership (only owner can edit)
    if ($comment['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'error' => 'You can only edit your own comments']);
        exit;
    }
    
    // Update the comment
    $update_stmt = $communityDB->prepare("
        UPDATE post_comments 
        SET comment_text = ?
        WHERE comment_id = ?
    ");
    $update_stmt->execute([$comment_text, $comment_id]);
    
    // Get updated comment data
    $get_stmt = $communityDB->prepare("
        SELECT pc.*, u.nickname, u.avatar 
        FROM post_comments pc
        JOIN usersDB.users u ON pc.user_id = u.user_id
        WHERE pc.comment_id = ?
    ");
    $get_stmt->execute([$comment_id]);
    $updated_comment = $get_stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Comment updated successfully',
        'comment' => $updated_comment
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>