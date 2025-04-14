<?php
// Include required files
require_once '../../config/session_config.php';
require_once '../../config/db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Check if post_id is provided
if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
    echo json_encode(['success' => false, 'error' => 'No post ID provided']);
    exit;
}

$post_id = intval($_POST['post_id']);
$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    // Get current like count
    $count_stmt = $communityDB->prepare("SELECT like_count FROM discussion_posts WHERE post_id = ?");
    $count_stmt->execute([$post_id]);
    $current_likes = $count_stmt->fetchColumn();
    
    // Update likes based on action
    if ($action === 'unlike') {
        // Decrement likes (with a minimum of 0)
        $new_count = max(0, $current_likes - 1);
        $update_stmt = $communityDB->prepare("UPDATE discussion_posts SET like_count = ? WHERE post_id = ?");
        $update_stmt->execute([$new_count, $post_id]);
        
        echo json_encode([
            'success' => true,
            'action' => 'unliked',
            'like_count' => $new_count
        ]);
    } else {
        // Increment likes
        $new_count = $current_likes + 1;
        $update_stmt = $communityDB->prepare("UPDATE discussion_posts SET like_count = ? WHERE post_id = ?");
        $update_stmt->execute([$new_count, $post_id]);
        
        echo json_encode([
            'success' => true,
            'action' => 'liked',
            'like_count' => $new_count
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>