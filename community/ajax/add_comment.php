<?php
// filepath: c:\xampp\htdocs\asm\community\ajax\add_comment.php
require_once '../../config/session_config.php';
require_once '../../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please log in to comment']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Validate input
if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id']) || empty($_POST['comment'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid comment data']);
    exit;
}

$post_id = (int)$_POST['post_id'];
$comment_text = trim($_POST['comment']);

try {
    // Check if post exists
    $stmt = $communityDB->prepare("SELECT post_id FROM discussion_posts WHERE post_id = ?");
    $stmt->execute([$post_id]);
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'Post not found']);
        exit;
    }
    
    // Add comment
    $stmt = $communityDB->prepare("
        INSERT INTO post_comments (post_id, user_id, comment_text, comment_date) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$post_id, $user_id, $comment_text]);
    $comment_id = $communityDB->lastInsertId();
    
    // Get user info for the response
    $user_stmt = $communityDB->prepare("
        SELECT nickname, avatar FROM usersDB.users WHERE user_id = ?
    ");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get updated comment count
    $count_stmt = $communityDB->prepare("
        SELECT COUNT(*) as count FROM post_comments WHERE post_id = ?
    ");
    $count_stmt->execute([$post_id]);
    $count = $count_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Just pass the raw avatar number from the database
    // The frontend will handle converting it to the right filename
    
    echo json_encode([
        'success' => true,
        'comment' => [
            'comment_id' => $comment_id,
            'comment_text' => $comment_text,
            'comment_date' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar']  // Pass the raw avatar number (0-5)
        ],
        'comment_count' => $count['count']
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error adding comment: ' . $e->getMessage()]);
    error_log("Comment error: " . $e->getMessage());
}
?>