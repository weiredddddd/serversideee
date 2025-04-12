<?php
// Initialize session and include required files
require_once '../config/session_config.php';
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$post_id) {
    header("Location: community.php?tab=discussions");
    exit;
}

// Check if user already liked this post
$check_sql = "SELECT * FROM post_likes WHERE post_id = :post_id AND user_id = :user_id";
$stmt = $communityDB->prepare($check_sql);
$stmt->bindParam(':post_id', $post_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$existing_like = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$existing_like) {
    // User hasn't liked this post yet, add the like
    $like_sql = "INSERT INTO post_likes (post_id, user_id) VALUES (:post_id, :user_id)";
    $stmt = $communityDB->prepare($like_sql);
    $stmt->bindParam(':post_id', $post_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
} else {
    // User already liked this post, remove the like (toggle functionality)
    $unlike_sql = "DELETE FROM post_likes WHERE post_id = :post_id AND user_id = :user_id";
    $stmt = $communityDB->prepare($unlike_sql);
    $stmt->bindParam(':post_id', $post_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
}

// Redirect back to the post or community page
$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'community.php?tab=discussions';
header("Location: $referrer");
exit;