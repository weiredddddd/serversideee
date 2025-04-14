<?php
// Initialize session and include required files
require_once '../../config/session_config.php';
require_once '../../config/db.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Check if post_id is provided
if (!isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
    exit;
}

$post_id = $_GET['post_id'];

// Fetch comments with user information using JOIN
try {
    // Modify the SQL query to include post_id
    $stmt = $communityDB->prepare("
        SELECT pc.*, u.nickname, u.avatar, pc.post_id 
        FROM post_comments pc
        JOIN usersDB.users u ON pc.user_id = u.user_id
        WHERE pc.post_id = ?
        ORDER BY pc.comment_date ASC
    ");
    
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get comment count
    $count_stmt = $communityDB->prepare("SELECT COUNT(*) as count FROM post_comments WHERE post_id = ?");
    $count_stmt->execute([$post_id]);
    $comment_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo json_encode([
        'success' => true,
        'comments' => $comments,
        'comment_count' => $comment_count
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>