<?php
// Initialize session and include required files
require_once '../../config/session_config.php';
require_once '../../config/db.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'You must be logged in to delete a post']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if post_id is provided
if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
    exit;
}

$post_id = $_POST['post_id'];

// Verify the post belongs to the current user
$check_stmt = $communityDB->prepare("SELECT post_id, image_url FROM discussion_posts WHERE post_id = ? AND user_id = ?");
$check_stmt->execute([$post_id, $user_id]);
$post = $check_stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    echo json_encode(['success' => false, 'error' => 'You do not have permission to delete this post or it does not exist']);
    exit;
}

try {
    // Start a transaction for safer deletion
    $communityDB->beginTransaction();
    
    // Delete comments first (foreign key constraint)
    $del_comments = $communityDB->prepare("DELETE FROM post_comments WHERE post_id = ?");
    $del_comments->execute([$post_id]);
    
    // Delete the post
    $del_post = $communityDB->prepare("DELETE FROM discussion_posts WHERE post_id = ?");
    $success = $del_post->execute([$post_id]);
    
    if ($success) {
        // Delete the associated image file if it exists and is an uploaded file (not a default)
        if (!empty($post['image_url'])) {
            // Check both possible image path formats
            if (strpos($post['image_url'], 'uploads/posts/') !== false || 
                strpos($post['image_url'], 'uploads/discussion_post_img/') !== false) {
                $image_path = '../../' . $post['image_url'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }
        
        // Commit the transaction
        $communityDB->commit();
        
        echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);
    } else {
        // Rollback on error
        $communityDB->rollBack();
        echo json_encode(['success' => false, 'error' => 'Failed to delete the post']);
    }
} catch (Exception $e) {
    // Rollback on exception
    $communityDB->rollBack();
    echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]);
}
?>
