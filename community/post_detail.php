<?php
// Initialize session and include required files
require_once '../config/session_config.php';
require_once '../config/db.php';

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Get post ID from URL
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$post_id) {
    header("Location: community.php?tab=discussions");
    exit;
}

// Update view count
$update_views = "UPDATE discussion_posts SET view_count = view_count + 1 WHERE post_id = :post_id";
$stmt = $communityDB->prepare($update_views);
$stmt->bindParam(':post_id', $post_id);
$stmt->execute();

// Fetch post details
$sql = "SELECT dp.*, u.nickname FROM discussion_posts dp
        JOIN usersDB.users u ON dp.user_id = u.user_id  
        WHERE dp.post_id = :post_id";
$stmt = $communityDB->prepare($sql);
$stmt->bindParam(':post_id', $post_id);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header("Location: community.php?tab=discussions");
    exit;
}

// Handle comment submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (!$user_id) {
        $message = "You must be logged in to comment.";
    } else {
        $comment = $_POST['comment'];
        $comment_sql = "INSERT INTO post_comments (post_id, user_id, comment_text) 
                        VALUES (:post_id, :user_id, :comment_text)";
        
        $stmt = $communityDB->prepare($comment_sql);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':comment_text', $comment);
        
        if ($stmt->execute()) {
            $message = "Comment added successfully!";
            header("Location: post_detail.php?id=$post_id");
            exit;
        } else {
            $message = "Error: " . implode(" ", $stmt->errorInfo());
        }
    }
}

// Fetch comments
$comments_sql = "SELECT pc.*, u.nickname FROM post_comments pc
                JOIN usersDB.users u ON pc.user_id = u.user_id
                WHERE pc.post_id = :post_id
                ORDER BY pc.comment_date ASC";
$stmt = $communityDB->prepare($comments_sql);
$stmt->bindParam(':post_id', $post_id);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get like count
$likes_sql = "SELECT COUNT(*) as like_count FROM post_likes WHERE post_id = :post_id";
$stmt = $communityDB->prepare($likes_sql);
$stmt->bindParam(':post_id', $post_id);
$stmt->execute();
$like_count = $stmt->fetch(PDO::FETCH_ASSOC)['like_count'];

// Check if current user liked this post
$user_liked = false;
if ($user_id) {
    $user_like_sql = "SELECT * FROM post_likes WHERE post_id = :post_id AND user_id = :user_id";
    $stmt = $communityDB->prepare($user_like_sql);
    $stmt->bindParam(':post_id', $post_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user_liked = ($stmt->rowCount() > 0);
}

// Set the page title
$pageTitle = htmlspecialchars($post['title']) . " - Community Forum";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Include the community CSS file -->
    <link rel="stylesheet" href="css/community.css">
</head>
<body>
    <?php include_once '../includes/navigation.php'; ?>
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="community.php?tab=discussions">Community</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($post['title']); ?></li>
            </ol>
        </nav>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0"><?php echo htmlspecialchars($post['title']); ?></h2>
                    <small class="text-muted">
                        Posted by 
                        <a href="user_profile.php?id=<?php echo $post['user_id']; ?>">
                            <?php echo htmlspecialchars($post['nickname']); ?>
                        </a> 
                        on <?php echo date('M d, Y g:i a', strtotime($post['post_date'])); ?>
                    </small>
                </div>
                <span class="badge bg-secondary"><?php echo htmlspecialchars($post['category']); ?></span>
            </div>
            
            <?php if (!empty($post['image_url'])): ?>
                <div class="post-image">
                    <img src="<?php echo htmlspecialchars('../' . $post['image_url']); ?>" class="img-fluid" alt="Post Image">
                </div>
            <?php endif; ?>
            
            <div class="card-body">
                <p class="card-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <div>
                    <a href="like_post.php?id=<?php echo $post_id; ?>" class="btn btn-sm <?php echo $user_liked ? 'btn-danger' : 'btn-outline-danger'; ?>">
                        <i class="fas fa-heart"></i> Like <?php if ($like_count > 0) echo "($like_count)"; ?>
                    </a>
                </div>
                <div>
                    <span class="text-muted"><i class="fas fa-eye"></i> <?php echo $post['view_count']; ?> views</span>
                </div>
            </div>
        </div>

        <h3 class="mb-3">Comments</h3>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="comment" class="form-label">Add a Comment</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3" required <?php if (!$user_id) echo 'disabled'; ?>></textarea>
                        <?php if (!$user_id): ?>
                            <small class="text-muted">You must be <a href="../users/login.php">logged in</a> to comment.</small>
                        <?php endif; ?>
                    </div>
                    <button type="submit" name="submit_comment" class="btn btn-primary" <?php if (!$user_id) echo 'disabled'; ?>>Submit Comment</button>
                </form>
            </div>
        </div>

        <?php if (!empty($comments)): ?>
            <?php foreach($comments as $comment): ?>
                <div class="card comment-card mb-2">
                    <div class="comment-header">
                        <div class="d-flex justify-content-between">
                            <a href="user_profile.php?id=<?php echo $comment['user_id']; ?>">
                                <?php echo htmlspecialchars($comment['username']); ?>
                            </a>
                            <small class="text-muted"><?php echo date('M d, Y g:i a', strtotime($comment['comment_date'])); ?></small>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <p class="card-text mb-0"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">No comments yet. Be the first to comment!</div>
        <?php endif; ?>
    </div>
    <!-- Footer -->
    <?php include_once '../includes/footer.php'; ?>
</body>
</html>