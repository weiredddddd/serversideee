<?php
require_once '../../config/session_config.php';
require_once '../../config/db.php';

header('Content-Type: application/json');

// Check if post_id is provided
if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
    exit;
}

$post_id = (int)$_POST['post_id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

try {
    // First check if the post exists
    $post_check_stmt = $communityDB->prepare("SELECT user_id FROM discussion_posts WHERE post_id = ?");
    $post_check_stmt->execute([$post_id]);
    $post_data = $post_check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post_data) {
        echo json_encode(['success' => false, 'error' => 'Post not found']);
        exit;
    }
    
    $author_id = $post_data['user_id'];
    
    // Don't count views from the author or non-logged in users
    if (($user_id && $user_id != $author_id) && !$is_admin) {
        // Check if this user has already viewed this post in this session
        $session_key = 'viewed_post_' . $post_id;
        if (!isset($_SESSION[$session_key])) {
            // Mark this post as viewed in the session
            $_SESSION[$session_key] = true;
            
            // Increment view count in database
            $update_stmt = $communityDB->prepare("
                UPDATE discussion_posts 
                SET view_count = view_count + 1 
                WHERE post_id = ?
            ");
            $update_stmt->execute([$post_id]);
            
            // Get updated view count
            $count_stmt = $communityDB->prepare("SELECT view_count FROM discussion_posts WHERE post_id = ?");
            $count_stmt->execute([$post_id]);
            $count_data = $count_stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true, 
                'view_count' => $count_data['view_count']
            ]);
        } else {
            // User already viewed this post in this session
            echo json_encode(['success' => false, 'error' => 'Already viewed', 'counted' => false]);
        }
    } else {
        // Author viewing own post or non-logged in user
        echo json_encode(['success' => false, 'error' => 'View not counted', 'counted' => false]);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    error_log("View tracking error: " . $e->getMessage());
}
?>