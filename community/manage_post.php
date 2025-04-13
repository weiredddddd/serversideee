<?php
// Initialize session and include required files
require_once '../config/session_config.php';
require_once '../config/db.php';

// Check if user is logged in, redirect if not
if (!isset($_SESSION['user_id'])) {
    header('Location: ../users/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Process delete request if sent via GET (for non-JS users)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $post_id = $_GET['delete'];
    
    // Verify the post belongs to the current user
    $check_stmt = $communityDB->prepare("SELECT post_id FROM discussion_posts WHERE post_id = ? AND user_id = ?");
    $check_stmt->execute([$post_id, $user_id]);
    
    if ($check_stmt->rowCount() > 0) {
        // Delete comments first (foreign key constraint)
        $del_comments = $communityDB->prepare("DELETE FROM post_comments WHERE post_id = ?");
        $del_comments->execute([$post_id]);
        
        // Delete likes
        $del_likes = $communityDB->prepare("DELETE FROM post_likes WHERE post_id = ?");
        $del_likes->execute([$post_id]);
        
        // Delete the post
        $del_post = $communityDB->prepare("DELETE FROM discussion_posts WHERE post_id = ?");
        $del_post->execute([$post_id]);
        
        $success_message = "Post deleted successfully!";
    } else {
        $error_message = "You don't have permission to delete this post or the post doesn't exist.";
    }
}

// Fetch all posts by the current user
$posts_stmt = $communityDB->prepare("
    SELECT dp.*, 
           (SELECT COUNT(*) FROM post_comments WHERE post_id = dp.post_id) AS comment_count,
           (SELECT COUNT(*) FROM post_likes WHERE post_id = dp.post_id) AS like_count
    FROM discussion_posts dp
    WHERE dp.user_id = ?
    ORDER BY dp.post_date DESC
");
$posts_stmt->execute([$user_id]);
$posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);

// Set page title
$pageTitle = "Manage My Posts";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/community.css">
    <style>
        .post-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .post-stats {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .post-stats i {
            margin-right: 5px;
        }
        
        .post-stats span {
            margin-right: 15px;
        }
        
        .empty-posts {
            text-align: center;
            padding: 50px 0;
        }
        
        .empty-posts i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .confirmation-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .confirmation-dialog {
            background-color: white;
            border-radius: 5px;
            padding: 20px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <?php include_once '../includes/navigation.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage My Posts</h1>
            <div>
                <a href="community.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-arrow-left"></i> Return to Community
                </a>
                <a href="create_post.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Create New Post
                </a>
            </div>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>
        
        <?php if (empty($posts)): ?>
            <div class="empty-posts">
                <i class="fas fa-comments"></i>
                <h3>You haven't created any posts yet</h3>
                <p class="text-muted">Share your thoughts with the community by creating your first post</p>
                <a href="create_post.php" class="btn btn-primary mt-2">Create Your First Post</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-12 mb-4" id="post-container-<?= $post['post_id'] ?>">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?= htmlspecialchars($post['title']) ?></h5>
                                <span class="badge bg-secondary"><?= htmlspecialchars($post['category']) ?></span>
                            </div>
                            
                            <div class="card-body">
                                <?php if (!empty($post['image_url'])): ?>
                                    <div class="post-image mb-3">
                                        <?php
                                            if (strpos($post['image_url'], 'uploads/posts/') !== false) {
                                                $image_path = '../' . $post['image_url'];
                                            } else {
                                                $image_path = '../assets/community/discussion_posts_img/' . basename($post['image_url']);
                                            }
                                        ?>
                                        <img src="<?= htmlspecialchars($image_path) ?>" class="img-fluid rounded" 
                                             alt="Post Image" style="max-height: 200px; object-fit: cover;">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="post-content mb-3">
                                    <?= nl2br(htmlspecialchars(substr($post['content'], 0, 300) . (strlen($post['content']) > 300 ? '...' : ''))) ?>
                                </div>
                                
                                <div class="post-stats">
                                    <span><i class="fas fa-calendar-alt"></i> <?= date('M d, Y', strtotime($post['post_date'])) ?></span>
                                    <span><i class="fas fa-heart"></i> <?= $post['like_count'] ?? 0 ?> likes</span>
                                    <span><i class="fas fa-comment"></i> <?= $post['comment_count'] ?? 0 ?> comments</span>
                                    <span><i class="fas fa-eye"></i> <?= $post['view_count'] ?? 0 ?> views</span>
                                </div>
                                
                                <div class="post-actions">
                                    <a href="edit_post.php?id=<?= $post['post_id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger delete-post" data-post-id="<?= $post['post_id'] ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Confirmation Dialog (Hidden by default) -->
    <div class="confirmation-overlay" id="deleteConfirmation" style="display: none;">
        <div class="confirmation-dialog">
            <h4 class="mb-3">Delete Post?</h4>
            <p>Are you sure you want to delete this post? This action cannot be undone.</p>
            <p class="text-danger small">All comments and likes will also be deleted.</p>
            <div class="d-flex justify-content-end mt-4">
                <button id="cancelDelete" class="btn btn-secondary me-2">Cancel</button>
                <button id="confirmDelete" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let postToDelete = null;
            
            // Show confirmation dialog when delete button is clicked
            $('.delete-post').on('click', function() {
                postToDelete = $(this).data('post-id');
                $('#deleteConfirmation').fadeIn(200);
            });
            
            // Hide confirmation dialog when cancel is clicked
            $('#cancelDelete').on('click', function() {
                $('#deleteConfirmation').fadeOut(200);
                postToDelete = null;
            });
            
            // Handle delete confirmation
            $('#confirmDelete').on('click', function() {
                if (postToDelete) {
                    // Show loading state
                    $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Deleting...');
                    
                    // Send AJAX request to delete the post
                    $.ajax({
                        url: 'ajax/delete_post.php',
                        type: 'POST',
                        data: { post_id: postToDelete },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Hide the confirmation dialog
                                $('#deleteConfirmation').fadeOut(200);
                                
                                // Remove the post from the page with animation
                                $('#post-container-' + postToDelete).fadeOut(500, function() {
                                    $(this).remove();
                                    
                                    // If no posts left, show empty state
                                    if ($('.card').length === 0) {
                                        $('.row').html(`
                                            <div class="empty-posts">
                                                <i class="fas fa-comments"></i>
                                                <h3>You haven't created any posts yet</h3>
                                                <p class="text-muted">Share your thoughts with the community by creating your first post</p>
                                                <a href="create_post.php" class="btn btn-primary mt-2">Create Your First Post</a>
                                            </div>
                                        `);
                                    }
                                });
                                
                                // Show success message
                                $('<div class="alert alert-success">Post deleted successfully!</div>')
                                    .insertAfter('.d-flex.justify-content-between.align-items-center.mb-4')
                                    .delay(3000)
                                    .fadeOut(500, function() {
                                        $(this).remove();
                                    });
                            } else {
                                // Show error message
                                alert('Error: ' + response.error);
                                $('#deleteConfirmation').fadeOut(200);
                            }
                            
                            // Reset the button
                            $('#confirmDelete').prop('disabled', false).text('Delete');
                            postToDelete = null;
                        },
                        error: function() {
                            alert('An error occurred while trying to delete the post.');
                            $('#deleteConfirmation').fadeOut(200);
                            $('#confirmDelete').prop('disabled', false).text('Delete');
                            postToDelete = null;
                        }
                    });
                }
            });
        });
    </script>
    
    <?php include_once '../includes/footer.php'; ?>
</body>
</html>
