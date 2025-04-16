<?php
// Initialize session and include required files
require_once '../config/session_config.php';
require_once '../config/db.php';

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Get recipe ID from URL
$recipe_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$recipe_id) {
    header("Location: community.php?tab=recipes");
    exit;
}

// Fetch recipe details
$recipe_sql = "SELECT r.*, u.nickname FROM RecipeDB.recipes r
              JOIN usersDB.users u ON r.user_id = u.user_id
              WHERE r.recipe_id = :recipe_id";
$stmt = $communityDB->prepare($recipe_sql);
$stmt->bindParam(':recipe_id', $recipe_id);
$stmt->execute();
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    header("Location: community.php?tab=recipes");
    exit;
}

// Fetch recipe ingredients
$ingredients_sql = "SELECT i.ingredient_name, ri.quantity, ri.unit
                   FROM RecipeDB.recipe_ingredient ri
                   JOIN RecipeDB.ingredients i ON ri.ingredient_id = i.ingredient_id
                   WHERE ri.recipe_id = :recipe_id";
$stmt = $communityDB->prepare($ingredients_sql);
$stmt->bindParam(':recipe_id', $recipe_id);
$stmt->execute();
$ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recipe steps
$steps_sql = "SELECT * FROM RecipeDB.steps WHERE recipe_id = :recipe_id ORDER BY step_no ASC";
$stmt = $communityDB->prepare($steps_sql);
$stmt->bindParam(':recipe_id', $recipe_id);
$stmt->execute();
$steps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle comment and rating submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (!$user_id) {
        $message = "You must be logged in to leave feedback.";
    } else {
        $comment = $_POST['comment'];
        $rating = intval($_POST['rating']);
        
        if ($rating < 1 || $rating > 5) {
            $message = "Rating must be between 1 and 5.";
        } else {
            // Step 1: Handle the comment in recipe_comments table
            $comment_sql = "INSERT INTO recipe_comments (recipe_id, user_id, comment_text) 
                            VALUES (:recipe_id, :user_id, :comment_text)
                            ON DUPLICATE KEY UPDATE comment_text = :comment_text";
            
            $stmt = $communityDB->prepare($comment_sql);
            $stmt->bindParam(':recipe_id', $recipe_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':comment_text', $comment);
            $comment_success = $stmt->execute();
            
            // Step 2: Handle the rating in recipe_ratings table
            $rating_sql = "INSERT INTO recipe_ratings (recipe_id, user_id, rating_value) 
                           VALUES (:recipe_id, :user_id, :rating)
                           ON DUPLICATE KEY UPDATE rating_value = :rating";
            
            $stmt = $communityDB->prepare($rating_sql);
            $stmt->bindParam(':recipe_id', $recipe_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':rating', $rating);
            $rating_success = $stmt->execute();
            
            if ($comment_success && $rating_success) {
                $message = "Thank you for your feedback!";
                // Refresh the page to show updated data
                header("Location: recipe_feedback.php?id=$recipe_id&success=1");
                exit;
            } else {
                $message = "Error submitting feedback.";
            }
        }
    }
}

// Success message from redirect
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = "Thank you for your feedback!";
}

// Fetch comments
$comments_sql = "SELECT rc.*, u.nickname, rr.rating_value 
                FROM recipe_comments rc
                JOIN usersDB.users u ON rc.user_id = u.user_id
                LEFT JOIN recipe_ratings rr ON rc.recipe_id = rr.recipe_id AND rc.user_id = rr.user_id
                WHERE rc.recipe_id = :recipe_id
                ORDER BY rc.comment_date DESC";
$stmt = $communityDB->prepare($comments_sql);
$stmt->bindParam(':recipe_id', $recipe_id);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate average rating directly from ratings table
$avg_sql = "SELECT AVG(rating_value) as avg_rating, COUNT(*) as rating_count 
            FROM recipe_ratings 
            WHERE recipe_id = :recipe_id";
$stmt = $communityDB->prepare($avg_sql);
$stmt->bindParam(':recipe_id', $recipe_id);
$stmt->execute();
$rating_data = $stmt->fetch(PDO::FETCH_ASSOC);

$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$rating_count = $rating_data['rating_count'];

// Check if user has already rated
$user_rating = 0;
$user_comment = '';
if ($user_id) {
    // Get user's rating
    $user_rating_sql = "SELECT rating_value FROM recipe_ratings 
                        WHERE recipe_id = :recipe_id AND user_id = :user_id";
    $stmt = $communityDB->prepare($user_rating_sql);
    $stmt->bindParam(':recipe_id', $recipe_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user_rating_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_rating = $user_rating_data ? $user_rating_data['rating_value'] : 0;
    
    // Get user's comment
    $user_comment_sql = "SELECT comment_text FROM recipe_comments 
                         WHERE recipe_id = :recipe_id AND user_id = :user_id";
    $stmt = $communityDB->prepare($user_comment_sql);
    $stmt->bindParam(':recipe_id', $recipe_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user_comment_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_comment = $user_comment_data ? $user_comment_data['comment_text'] : '';
}

// Set page title
$pageTitle = htmlspecialchars($recipe['title']) . " - Recipe Feedback";
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
    <style>
        .recipe-detail-img {
            height: 400px;
            object-fit: cover;
            width: 100%;
        }
        /* Removing custom styling for similar recipes to keep their original appearance */
    </style>
</head>
<body>
<?php include_once '../includes/navigation.php'; ?>
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="community.php?tab=recipes">Recipe Feedback</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($recipe['title']); ?></li>
            </ol>
        </nav>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 'success'): ?>
            <div class="alert alert-success">Comment deleted successfully!</div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0"><?php echo htmlspecialchars($recipe['title']); ?></h2>
                            <small class="text-muted">
                                By <?php echo htmlspecialchars($recipe['nickname']); ?> on 
                                <?php echo date('M d, Y', strtotime($recipe['created_at'])); ?>
                            </small>
                        </div>
                        <div>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($recipe['category']); ?></span>
                            <?php if (!empty($recipe['cuisine_type'])): ?>
                                <span class="badge bg-info"><?php echo htmlspecialchars($recipe['cuisine_type']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($recipe['image_url'])): ?>
                        <?php
                        // Check if it's a full path or just a filename
                        if (strpos($recipe['image_url'], 'uploads/recipe/') !== false) {
                            // It's a real uploaded image in the recipes folder
                            $image_path = '../' . $recipe['image_url'];
                        } else if (strpos($recipe['image_url'], 'http') === 0) {
                            // It's an external URL
                            $image_path = $recipe['image_url'];
                        } else {
                            // If it's just a filename without path, construct proper path
                            // First check if it might be in uploads/recipe folder without the prefix
                            if (file_exists('../uploads/recipe/' . $recipe['image_url'])) {
                                $image_path = '../uploads/recipe/' . $recipe['image_url'];
                            } else {
                                // Fallback to assets folder
                                $image_path = '../assets/recipe/' . basename($recipe['image_url']);
                            }
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($image_path); ?>" class="card-img-top recipe-detail-img" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5>Description</h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
                        
                        <div class="recipe-meta d-flex justify-content-between mb-4">
                            <div>
                                <h5>Average Rating</h5>
                                <div class="recipe-rating">
                                    <?php 
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $avg_rating) {
                                                echo '<i class="fas fa-star text-warning"></i>';
                                            } elseif ($i - 0.5 <= $avg_rating) {
                                                echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                            } else {
                                                echo '<i class="far fa-star text-warning"></i>';
                                            }
                                        }
                                        echo ' <span>(' . number_format($avg_rating, 1) . '/5 from ' . $rating_count . ' reviews)</span>';
                                    ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($recipe['spice_level'])): ?>
                                <div>
                                    <h5>Spice Level</h5>
                                    <div class="spice-level">
                                        <?php 
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $recipe['spice_level']) {
                                                    echo '<i class="fas fa-pepper-hot text-danger"></i>';
                                                } else {
                                                    echo '<i class="far fa-pepper-hot text-muted"></i>';
                                                }
                                            }
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Ingredients</h5>
                                <?php if (!empty($ingredients)): ?>
                                    <ul class="list-group">
                                        <?php foreach($ingredients as $ingredient): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?php echo htmlspecialchars($ingredient['ingredient_name']); ?>
                                                <span class="badge bg-primary rounded-pill">
                                                    <?php echo htmlspecialchars($ingredient['quantity'] . ' ' . $ingredient['unit']); ?>
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted">No ingredients listed.</p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>Steps</h5>
                                <?php if (!empty($steps)): ?>
                                    <ol class="list-group list-group-numbered">
                                        <?php foreach($steps as $step): ?>
                                            <li class="list-group-item"><?php echo htmlspecialchars($step['description']); ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                <?php else: ?>
                                    <p class="text-muted">No steps listed.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="mb-0">Leave Your Feedback</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating</label>
                                <div class="rating-selection">
                                    <div class="btn-group" role="group">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <input type="radio" class="btn-check" name="rating" id="rating<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo ($user_rating == $i) ? 'checked' : ''; ?> required>
                                            <label class="btn btn-outline-warning" for="rating<?php echo $i; ?>">
                                                <?php echo $i; ?> <i class="fas fa-star"></i>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="comment" class="form-label">Your Comments</label>
                                <textarea class="form-control" id="comment" name="comment" rows="4" required <?php if (!$user_id) echo 'disabled'; ?>><?php echo htmlspecialchars($user_comment); ?></textarea>
                                <?php if (!$user_id): ?>
                                    <div class="form-text text-danger">You must be <a href="../users/login.php">logged in</a> to leave feedback.</div>
                                <?php endif; ?>
                            </div>
                            
                            <button type="submit" name="submit_comment" class="btn btn-primary" <?php if (!$user_id) echo 'disabled'; ?>>
                                <?php echo ($user_rating > 0) ? 'Update Feedback' : 'Submit Feedback'; ?>
                            </button>
                        </form>
                    </div>
                </div>
                
                <h3 class="mb-3">Community Feedback (<?php echo $rating_count; ?>)</h3>
                
                <?php if (!empty($comments)): ?>
                    <?php foreach($comments as $comment): ?>
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold"><?php echo htmlspecialchars($comment['nickname']); ?></span>
                                        <div class="recipe-rating">
                                            <?php 
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $comment['rating_value']) {
                                                        echo '<i class="fas fa-star text-warning"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star text-warning"></i>';
                                                    }
                                                }
                                            ?>
                                        </div>
                                    </div>
                                        <small class="text-muted"><?php echo date('M d, Y g:i a', strtotime($comment['comment_date'])); ?></small>
                                    </div>
                                    <?php if (($_SESSION['is_admin'] ?? 0) === '1'): ?>
                                        <a href="admin_delete_comment.php?comment_id=<?= $comment['comment_id'] ?>&recipe_id=<?= $recipe_id ?>" 
                                            class="btn btn-sm btn-danger ms-2"
                                            onclick="return confirm('Delete this comment permanently?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">No feedback yet. Be the first to review this recipe!</div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Similar Recipes</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $similar_sql = "SELECT recipe_id, title, image_url FROM RecipeDB.recipes 
                                        WHERE recipe_id != :recipe_id 
                                        AND (category = :category OR cuisine_type = :cuisine_type)
                                        ORDER BY RAND() LIMIT 3";
                        $stmt = $communityDB->prepare($similar_sql);
                        $stmt->bindParam(':recipe_id', $recipe_id);
                        $stmt->bindParam(':category', $recipe['category']);
                        $stmt->bindParam(':cuisine_type', $recipe['cuisine_type']);
                        $stmt->execute();
                        $similar_recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (!empty($similar_recipes)) {
                            foreach($similar_recipes as $similar) {
                                echo '<div class="featured-recipe">';
                                echo '<div>';
                                if (!empty($similar['image_url'])) {
                                    // Check if it's a full path or just a filename
                                    if (strpos($similar['image_url'], 'uploads/recipe/') !== false) {
                                        // It's a real uploaded image in the recipes folder
                                        $image_path = '../' . $similar['image_url'];
                                    } else if (strpos($similar['image_url'], 'http') === 0) {
                                        // It's an external URL
                                        $image_path = $similar['image_url'];
                                    } else {
                                        // If it's just a filename without path, construct proper path
                                        if (file_exists('../uploads/recipe/' . $similar['image_url'])) {
                                            $image_path = '../uploads/recipe/' . $similar['image_url'];
                                        } else {
                                            // Fallback to assets folder
                                            $image_path = '../assets/recipe/' . basename($similar['image_url']);
                                        }
                                    }
                                    echo '<a href="recipe_feedback.php?id=' . $similar['recipe_id'] . '">';
                                    echo '<img src="' . htmlspecialchars($image_path) . '" class="featured-recipe-img" alt="' . htmlspecialchars($similar['title']) . '">';
                                    echo '</a>';
                                } else {
                                    echo '<a href="recipe_feedback.php?id=' . $similar['recipe_id'] . '">';
                                    echo '<div class="featured-recipe-img-placeholder"><i class="fas fa-utensils text-muted"></i></div>';
                                    echo '</a>';
                                }
                                echo '</div>';
                                echo '<div>';
                                echo '<h6><a href="recipe_feedback.php?id=' . $similar['recipe_id'] . '">' . htmlspecialchars($similar['title']) . '</a></h6>';
                                echo '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="text-muted">No similar recipes found</p>';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Cooking Tips</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php 
                            $tips_sql = "SELECT dp.post_id, dp.title FROM discussion_posts dp 
                                        WHERE dp.category = 'Cooking Tips' 
                                        ORDER BY dp.post_date DESC LIMIT 5";
                            $stmt = $communityDB->prepare($tips_sql);
                            $stmt->execute();
                            $cooking_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (!empty($cooking_tips)) {
                                foreach($cooking_tips as $tip) {
                                    // Direct link to the community page with discussion posts filtered by Cooking Tips category
                                    echo '<a href="community.php?tab=discussions&category=Cooking+Tips" class="list-group-item list-group-item-action">';
                                    echo '<i class="fas fa-lightbulb text-warning me-2"></i> ' . htmlspecialchars($tip['title']);
                                    echo '</a>';
                                }
                            } else {
                                echo '<p class="text-muted">No cooking tips available</p>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="community.php?tab=discussions&category=Cooking Tips" class="btn btn-sm btn-outline-primary">View All Tips</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include_once '../includes/footer.php'; ?>

    <!-- jQuery (required for AJAX) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>