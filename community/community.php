<?php
// Initialize session and include required files
require_once '../config/session_config.php';
require_once '../config/db.php';

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Set default active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'discussions';

// Handle category filter for discussions
$category_filter = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';

// Fetch discussion posts
$discussions_sql = "SELECT dp.*, u.nickname, 
        (SELECT COUNT(*) FROM post_comments WHERE post_id = dp.post_id) AS comment_count,
        (SELECT COUNT(*) FROM post_likes WHERE post_id = dp.post_id) AS like_count
        FROM discussion_posts dp
        JOIN usersDB.users u ON dp.user_id = u.user_id";

// Add category filter if specified
if (!empty($category_filter)) {
    $discussions_sql .= " WHERE dp.category = :category";
    $stmt = $communityDB->prepare($discussions_sql);
    $stmt->bindParam(':category', $category_filter);
    $stmt->execute();
} else {
    $discussions_sql .= " ORDER BY dp.post_date DESC";
    $stmt = $communityDB->prepare($discussions_sql);
    $stmt->execute();
}

$discussions_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recipes for feedback
$recipes_sql = "SELECT r.*, u.nickname,
               (SELECT COUNT(*) FROM recipe_comments WHERE recipe_id = r.recipe_id) AS comment_count,
               (SELECT AVG(rating_value) FROM recipe_ratings WHERE recipe_id = r.recipe_id) AS avg_rating
               FROM RecipeDB.recipes r
               JOIN usersDB.users u ON r.user_id = u.user_id
               ORDER BY r.created_at DESC";
$stmt = $communityDB->prepare($recipes_sql);
$stmt->execute();
$recipes_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set page title
$pageTitle = "Community Forum";
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
        <h1 class="mb-4">Community</h1>
        
        <?php if (isset($_GET['created']) && $_GET['created'] == 'success'): ?>
            <div class="alert alert-success">Your post has been created successfully!</div>
        <?php endif; ?>

        <!-- Tab navigation -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_tab == 'discussions') ? 'active' : ''; ?>" href="?tab=discussions">
                    <i class="fas fa-comments"></i> Discussion Posts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_tab == 'recipes') ? 'active' : ''; ?>" href="?tab=recipes">
                    <i class="fas fa-utensils"></i> Recipe Feedback
                </a>
            </li>
        </ul>

        <!-- Tab content -->
        <div class="tab-content">
            <!-- Discussion Posts Tab -->
            <div class="tab-pane <?php echo ($active_tab == 'discussions') ? 'active' : 'fade'; ?>">
                <div class="row">
                    <div class="col-md-8">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">Recent Discussions</h2>
                        <div>
                            <?php if (!empty($category_filter)): ?>
                                <a href="community.php?tab=discussions" class="btn btn-sm btn-outline-secondary me-2">Clear Filter</a>
                            <?php endif; ?>
                            <?php if ($user_id): ?>
                                <a href="create_post.php" class="btn btn-sm btn-success">
                                    <i class="fas fa-plus"></i> New Post
                                </a>
                            <?php else: ?>
                                <a href="../users/login.php" class="btn btn-sm btn-success">
                                    <i class="fas fa-lock"></i> Login to Post
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                        <?php if (!empty($discussions_result)): ?>
                            <?php foreach($discussions_result as $row): ?>
                                <div class="card post-card">
                                    <div class="post-header">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($row['title']); ?></h5>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($row['category']); ?></span>
                                        </div>
                                        <small class="text-muted">
                                            Posted by 
                                            <a href="user_profile.php?id=<?php echo $row['user_id']; ?>">
                                                <?php echo htmlspecialchars($row['nickname']); ?>
                                            </a> 
                                            on <?php echo date('M d, Y', strtotime($row['post_date'])); ?>
                                        </small>
                                    </div>
                                    
                                    <?php if (!empty($row['image_url'])): ?>
                                        <div class="post-image">
                                            <img src="<?php echo htmlspecialchars('../' . $row['image_url']); ?>" class="img-fluid" alt="Post Image">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="card-body">
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($row['content'], 0, 200) . (strlen($row['content']) > 200 ? '...' : ''))); ?></p>
                                        <?php if (strlen($row['content']) > 200): ?>
                                            <a href="post_detail.php?id=<?php echo $row['post_id']; ?>" class="text-primary">Read more</a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="post-actions">
                                        <div>
                                            <a href="post_detail.php?id=<?php echo $row['post_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-comment"></i> Comments (<?php echo $row['comment_count']; ?>)
                                            </a>
                                            <a href="like_post.php?id=<?php echo $row['post_id']; ?>" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-heart"></i> Like (<?php echo $row['like_count']; ?>)
                                            </a>
                                        </div>
                                        <div>
                                            <span class="text-muted"><i class="fas fa-eye"></i> <?php echo isset($row['view_count']) ? $row['view_count'] : 0; ?> views</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <?php if (!empty($category_filter)): ?>
                                    No discussions found in this category. Be the first to start a conversation!
                                <?php else: ?>
                                    No discussions yet. Be the first to start a conversation!
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Community Categories</h5>
                            </div>
                            <div class="list-group list-group-flush">
                                <a href="?tab=discussions&category=Cooking Tips" class="list-group-item list-group-item-action <?php echo ($category_filter == 'Cooking Tips') ? 'active' : ''; ?>">Cooking Tips</a>
                                <a href="?tab=discussions&category=Recipe Questions" class="list-group-item list-group-item-action <?php echo ($category_filter == 'Recipe Questions') ? 'active' : ''; ?>">Recipe Questions</a>
                                <a href="?tab=discussions&category=Kitchen Equipment" class="list-group-item list-group-item-action <?php echo ($category_filter == 'Kitchen Equipment') ? 'active' : ''; ?>">Kitchen Equipment</a>
                                <a href="?tab=discussions&category=General Discussion" class="list-group-item list-group-item-action <?php echo ($category_filter == 'General Discussion') ? 'active' : ''; ?>">General Discussion</a>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Featured Recipes</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $featured_sql = "SELECT recipe_id, title, image_url FROM RecipeDB.recipes ORDER BY RAND() LIMIT 3";
                                $featured_stmt = $communityDB->query($featured_sql);
                                $featured_recipes = $featured_stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (!empty($featured_recipes)) {
                                    foreach($featured_recipes as $recipe) {
                                        echo '<div class="featured-recipe">';
                                        echo '<div>';
                                        if (!empty($recipe['image_url'])) {
                                            echo '<img src="' . htmlspecialchars($recipe['image_url']) . '" class="featured-recipe-img" alt="Recipe Image">';
                                        } else {
                                            echo '<div class="featured-recipe-img-placeholder"><i class="fas fa-utensils text-muted"></i></div>';
                                        }
                                        echo '</div>';
                                        echo '<div>';
                                        echo '<h6><a href="../recipes/recipe_detail.php?id=' . $recipe['recipe_id'] . '">' . htmlspecialchars($recipe['title']) . '</a></h6>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<p class="text-muted">No recipes available</p>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Join the Community</h5>
                            </div>
                            <div class="card-body">
                                <p>Share your culinary knowledge, ask questions, and connect with other food enthusiasts!</p>
                                <?php if (!$user_id): ?>
                                    <a href="../users/login.php" class="btn btn-primary">Login to Participate</a>
                                <?php else: ?>
                                    <p class="text-muted mb-0">You're logged in and ready to participate!</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recipe Feedback Tab -->
            <div class="tab-pane <?php echo ($active_tab == 'recipes') ? 'active' : 'fade'; ?>">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="mb-4">Recipe Feedback</h2>
                        
                        <?php if (!empty($recipes_result)): ?>
                            <div class="row">
                                <?php foreach($recipes_result as $recipe): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 recipe-card">
                                            <div class="card-img-top">
                                                <?php if (!empty($recipe['image_url'])): ?>
                                                    <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="img-fluid recipe-img">
                                                <?php else: ?>
                                                    <div class="recipe-img-placeholder">
                                                        <i class="fas fa-utensils fa-3x"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($recipe['title']); ?></h5>
                                                <p class="card-text"><?php echo htmlspecialchars(substr($recipe['description'], 0, 100) . (strlen($recipe['description']) > 100 ? '...' : '')); ?></p>
                                                
                                                <!-- Rating Display -->
                                                <div class="recipe-rating mb-2">
                                                    <?php 
                                                        $avg_rating = isset($recipe['avg_rating']) ? round($recipe['avg_rating'], 1) : 0;
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            if ($i <= $avg_rating) {
                                                                echo '<i class="fas fa-star text-warning"></i>';
                                                            } elseif ($i - 0.5 <= $avg_rating) {
                                                                echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                                            } else {
                                                                echo '<i class="far fa-star text-warning"></i>';
                                                            }
                                                        }
                                                        echo ' <small class="text-muted">(' . $avg_rating . '/5)</small>';
                                                    ?>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted"><i class="fas fa-comment"></i> <?php echo $recipe['comment_count']; ?> comments</span>
                                                    <a href="recipe_feedback.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn btn-sm btn-outline-primary">View Recipe</a>
                                                </div>
                                            </div>
                                            <div class="card-footer text-muted">
                                                <small>By <?php echo htmlspecialchars($recipe['nickname']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No recipes available for feedback.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
     <!-- Footer -->                       
     <?php include_once '../includes/footer.php'; ?>
</body>
</html>