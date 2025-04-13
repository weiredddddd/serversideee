<?php
// Initialize session and include required files
require_once '../config/session_config.php';
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../users/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Check if post ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_post.php');
    exit;
}

$post_id = $_GET['id'];

// Get post details and verify ownership
$post_stmt = $communityDB->prepare("
    SELECT * FROM discussion_posts 
    WHERE post_id = ? AND user_id = ?
");
$post_stmt->execute([$post_id, $user_id]);

if ($post_stmt->rowCount() === 0) {
    // Post doesn't exist or doesn't belong to current user
    header('Location: manage_post.php');
    exit;
}

$post = $post_stmt->fetch(PDO::FETCH_ASSOC);

// Define available categories
$categories = ['Cooking Tips', 'Recipe Questions', 'Kitchen Equipment', 'General Discussion'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form inputs
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    // Basic validation
    if (empty($title) || strlen($title) > 100) {
        $error_message = 'Title is required and must be less than 100 characters.';
    } elseif (empty($category) || !in_array($category, $categories)) {
        $error_message = 'Please select a valid category.';
    } elseif (empty($content)) {
        $error_message = 'Content is required.';
    } else {
        // Handle image upload if a new image was uploaded
        $image_url = $post['image_url']; // Default to current image
        
        if (!empty($_FILES['image']['name'])) {
            // A new image was uploaded
            $upload_dir = '../uploads/posts/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                $error_message = 'Only JPG, JPEG, PNG, and GIF files are allowed.';
            } elseif ($_FILES['image']['size'] > 5000000) { // 5MB limit
                $error_message = 'File size must be less than 5MB.';
            } else {
                $new_filename = 'post_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old image if it exists and is not a default image
                    if (!empty($post['image_url']) && strpos($post['image_url'], 'uploads/posts/') !== false) {
                        $old_image_path = '../' . $post['image_url'];
                        if (file_exists($old_image_path)) {
                            unlink($old_image_path);
                        }
                    }
                    
                    // Update image URL
                    $image_url = 'uploads/posts/' . $new_filename;
                } else {
                    $error_message = 'Failed to upload image. Please try again.';
                }
            }
        } elseif (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
            // User wants to remove the current image
            if (!empty($post['image_url']) && strpos($post['image_url'], 'uploads/posts/') !== false) {
                $old_image_path = '../' . $post['image_url'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
            $image_url = null;
        }
        
        // If no errors, update the post
        if (empty($error_message)) {
            $update_stmt = $communityDB->prepare("
                UPDATE discussion_posts 
                SET title = ?, category = ?, content = ?, image_url = ?
                WHERE post_id = ? AND user_id = ?
            ");
            
            $result = $update_stmt->execute([
                $title, 
                $category, 
                $content,
                $image_url,
                $post_id,
                $user_id
            ]);
            
            if ($result) {
                $success_message = 'Post updated successfully!';
                
                // Refresh post data
                $post_stmt->execute([$post_id, $user_id]);
                $post = $post_stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error_message = 'Failed to update post. Please try again.';
            }
        }
    }
}

// Set page title
$pageTitle = "Edit Post";
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
        .current-image {
            max-width: 100%;
            max-height: 300px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .image-preview-container {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .remove-image-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .remove-image-btn:hover {
            background-color: rgba(255, 0, 0, 0.1);
        }
        
        #imagePreview {
            max-width: 100%;
            max-height: 300px;
            display: none;
            margin-top: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include_once '../includes/navigation.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Edit Post</h1>
                    <a href="manage_post.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to My Posts
                    </a>
                </div>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?= $error_message ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?= $success_message ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?= htmlspecialchars($post['title']) ?>" required maxlength="100">
                            </div>
                            
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category ?>" <?= $post['category'] === $category ? 'selected' : '' ?>>
                                            <?= $category ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="8" required><?= htmlspecialchars($post['content']) ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Image (Optional)</label>
                                
                                <?php if (!empty($post['image_url'])): ?>
                                    <div class="image-preview-container">
                                        <?php
                                        if (strpos($post['image_url'], 'uploads/posts/') !== false) {
                                            $image_path = '../' . $post['image_url'];
                                        } else {
                                            $image_path = '../assets/community/discussion_posts_img/' . basename($post['image_url']);
                                        }
                                        ?>
                                        <img src="<?= htmlspecialchars($image_path) ?>" class="current-image" alt="Current Post Image">
                                        <div class="remove-image-btn" id="removeImageBtn">
                                            <i class="fas fa-times text-danger"></i>
                                        </div>
                                    </div>
                                    <input type="hidden" id="removeImage" name="remove_image" value="0">
                                <?php endif; ?>
                                
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="text-muted">Maximum file size: 5MB. Supported formats: JPG, JPEG, PNG, GIF.</small>
                                <img id="imagePreview" src="#" alt="Image Preview">
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="manage_post.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Post</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Image preview functionality
            $('#image').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').attr('src', e.target.result).show();
                        $('.image-preview-container').hide(); // Hide current image when new one is selected
                        $('#removeImage').val('0'); // Reset remove flag as we're uploading a new image
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#imagePreview').hide();
                    $('.image-preview-container').show(); // Show current image again if file selection is canceled
                }
            });
            
            // Handle remove image button
            $('#removeImageBtn').click(function() {
                $('.image-preview-container').hide();
                $('#removeImage').val('1');
                $('#image').val(''); // Clear file input
                $('#imagePreview').hide();
            });
        });
    </script>
    
    <?php include_once '../includes/footer.php'; ?>
</body>
</html>
