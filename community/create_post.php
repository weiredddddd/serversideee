<?php
// Initialize session and include required files
require_once '../config/session_config.php';
require_once '../config/db.php';

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// If not logged in, redirect to login
if (!$user_id) {
    header("Location: ../users/login.php");
    exit;
}

// Get the user's current avatar
$avatar_stmt = $communityDB->prepare("SELECT avatar FROM usersDB.users WHERE user_id = ?");
$avatar_stmt->execute([$user_id]);
$user_avatar = $avatar_stmt->fetch(PDO::FETCH_ASSOC);

// Convert avatar value (0-5) to the correct avatar filename (avatar1.png - avatar6.png)
$avatar_num = isset($user_avatar['avatar']) ? (int)$user_avatar['avatar'] : 0;
$avatar_file = 'avatar' . ($avatar_num + 1) . '.png';
$avatar_path = '../assets/avatars/' . $avatar_file;

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $image_url = null;
    
    // Handle image upload if present
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (in_array($_FILES['post_image']['type'], $allowed_types) && $_FILES['post_image']['size'] <= $max_size) {
            // Create uploads directory if it doesn't exist
            $upload_dir = '../uploads/posts/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate a unique filename
            $filename = uniqid() . '_' . basename($_FILES['post_image']['name']);
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['post_image']['tmp_name'], $upload_path)) {
                $image_url = 'uploads/posts/' . $filename; // Store relative path
            } else {
                $message = "Error uploading image.";
            }
        } else {
            $message = "Invalid image format or size exceeded (max 2MB).";
        }
    }
    
    // If there was no error with the image upload (or no image was uploaded)
    if (empty($message)) {
        $sql = "INSERT INTO discussion_posts (user_id, title, content, category, image_url) 
                VALUES (:user_id, :title, :content, :category, :image_url)";
        $stmt = $communityDB->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':category', $category); 
        $stmt->bindParam(':image_url', $image_url);
        
        if ($stmt->execute()) {
            // Redirect back to community page with success message
            header("Location: community.php?tab=discussions&created=success");
            exit;
        } else {
            $message = "Error creating post: " . implode(" ", $stmt->errorInfo());
        }
    }
}

// Set page title
$pageTitle = "Create New Post";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Include the community CSS file -->
    <link rel="stylesheet" href="css/community.css">
</head>
<body>
    <?php include_once '../includes/navigation.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Create New Post</h2>
                        <a href="community.php?tab=discussions" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Community
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-danger"><?php echo $message; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required 
                                    value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="Cooking Tips" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Cooking Tips') ? 'selected' : ''; ?>>
                                        Cooking Tips
                                    </option>
                                    <option value="Recipe Questions" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Recipe Questions') ? 'selected' : ''; ?>>
                                        Recipe Questions
                                    </option>
                                    <option value="Kitchen Equipment" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Kitchen Equipment') ? 'selected' : ''; ?>>
                                        Kitchen Equipment
                                    </option>
                                    <option value="General Discussion" <?php echo (isset($_POST['category']) && $_POST['category'] == 'General Discussion') ? 'selected' : ''; ?>>
                                        General Discussion
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="8" required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="post_image" class="form-label">Image (Optional)</label>
                                <input type="file" class="form-control" id="post_image" name="post_image" accept="image/*">
                                <small class="text-muted">Supported formats: JPG, PNG, GIF (Max 2MB)</small>
                            </div>
                            <div class="d-flex justify-content-end">
                                <a href="community.php?tab=discussions" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" name="create_post" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Publish Post
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <?php include_once '../includes/footer.php'; ?>
</body>
</html>