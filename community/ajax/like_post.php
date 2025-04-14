<?php
require_once '../../config/session_config.php';
require_once '../../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please log in to like posts']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Validate input
if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid post']);
    exit;
}

$post_id = (int)$_POST['post_id'];

try {
    // Check if post exists
    $stmt = $communityDB->prepare("SELECT post_id FROM discussion_posts WHERE post_id = ?");
    $stmt->execute([$post_id]);
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'Post not found']);
        exit;
    }
    
    // Check if user already liked this post
    $stmt = $communityDB->prepare("SELECT like_id FROM post_likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    $liked = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($liked) {
        // User already liked, so unlike
        $stmt = $communityDB->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
        $action = 'unliked';
    } else {
        // User hasn't liked, so add like
        $stmt = $communityDB->prepare("INSERT INTO post_likes (post_id, user_id, like_date) VALUES (?, ?, NOW())");
        $stmt->execute([$post_id, $user_id]);
        $action = 'liked';
    }
    
    // Get updated like count
    $stmt = $communityDB->prepare("SELECT COUNT(*) AS like_count FROM post_likes WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $like_count = $result['like_count'];
    
    echo json_encode([
        'success' => true,
        'action' => $action,
        'like_count' => $like_count,
        'post_id' => $post_id
    ]);
    
} catch (PDOException $e) {
    error_log("Like error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>