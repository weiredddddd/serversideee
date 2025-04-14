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

// Handle search functionality
$search_query = isset($_GET['search']) ? trim(htmlspecialchars($_GET['search'])) : '';

// Handle sort functionality
$sort_by = isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : 'newest';

// Handle recipe category filter
$recipe_category = isset($_GET['recipe_category']) ? htmlspecialchars($_GET['recipe_category']) : '';

// Handle recipe sorting
$recipe_sort = isset($_GET['recipe_sort']) ? htmlspecialchars($_GET['recipe_sort']) : 'newest';

// Fetch discussion posts with search and category filters
$discussions_sql = "SELECT dp.*, u.nickname, u.avatar, 
        (SELECT COUNT(*) FROM post_comments WHERE post_id = dp.post_id) AS comment_count
        FROM discussion_posts dp
        JOIN usersDB.users u ON dp.user_id = u.user_id";

// Build WHERE clause based on filters
$where_conditions = [];
$params = [];

if (!empty($category_filter)) {
    $where_conditions[] = "dp.category = :category";
    $params[':category'] = $category_filter;
}

if (!empty($search_query)) {
    $where_conditions[] = "(dp.title LIKE :search OR dp.content LIKE :search)";
    $params[':search'] = "%{$search_query}%";
}

// Add WHERE clause if there are conditions
if (!empty($where_conditions)) {
    $discussions_sql .= " WHERE " . implode(' AND ', $where_conditions);
}

// Add ORDER BY clause based on sort selection
switch ($sort_by) {
    case 'most_likes':
        $discussions_sql .= " ORDER BY dp.like_count DESC, dp.post_date DESC";
        break;
    case 'most_views':
        $discussions_sql .= " ORDER BY dp.view_count DESC, dp.post_date DESC";
        break;
    case 'most_comments':
        $discussions_sql .= " ORDER BY comment_count DESC, dp.post_date DESC";
        break;
    case 'newest':
    default:
        $discussions_sql .= " ORDER BY dp.post_date DESC";
        break;
}

// Prepare and execute the query
$stmt = $communityDB->prepare($discussions_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$discussions_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all available categories for the dropdown
$categories_sql = "SELECT DISTINCT category FROM discussion_posts ORDER BY category";
$cat_stmt = $communityDB->prepare($categories_sql);
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch recipes for feedback with filters
$recipes_sql = "SELECT r.*, u.nickname,
               (SELECT COUNT(*) FROM recipe_comments WHERE recipe_id = r.recipe_id) AS comment_count,
               (SELECT AVG(rating_value) FROM recipe_ratings WHERE recipe_id = r.recipe_id) AS avg_rating,
               (SELECT COUNT(*) FROM recipe_ratings WHERE recipe_id = r.recipe_id) AS rating_count,
               COALESCE(r.view_count, 0) AS view_count
               FROM RecipeDB.recipes r
               JOIN usersDB.users u ON r.user_id = u.user_id";

// Build WHERE clause for recipes
$recipes_where = [];
$recipes_params = [];

if (!empty($recipe_category)) {
    $recipes_where[] = "r.category = :recipe_category";
    $recipes_params[':recipe_category'] = $recipe_category;
}

if (!empty($search_query)) {
    $recipes_where[] = "(r.title LIKE :recipe_search OR r.description LIKE :recipe_search)";
    $recipes_params[':recipe_search'] = "%{$search_query}%";
}

// Add WHERE clause if there are conditions
if (!empty($recipes_where)) {
    $recipes_sql .= " WHERE " . implode(' AND ', $recipes_where);
}

// Add ORDER BY clause based on sort selection
switch ($recipe_sort) {
    case 'most_ratings':
        // Changed to sort by the actual average rating value first, not the count of ratings
        $recipes_sql .= " ORDER BY avg_rating DESC, rating_count DESC, r.created_at DESC";
        break;
    case 'most_views':
        $recipes_sql .= " ORDER BY r.view_count DESC, r.created_at DESC";
        break;
    case 'most_comments':
        $recipes_sql .= " ORDER BY comment_count DESC, r.created_at DESC";
        break;
    case 'newest':
    default:
        $recipes_sql .= " ORDER BY r.created_at DESC";
        break;
}

$stmt = $communityDB->prepare($recipes_sql);
foreach ($recipes_params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$recipes_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all available recipe categories for the dropdown
$recipe_categories_sql = "SELECT DISTINCT category FROM RecipeDB.recipes ORDER BY category";
$recipe_cat_stmt = $communityDB->prepare($recipe_categories_sql);
$recipe_cat_stmt->execute();
$recipe_categories = $recipe_cat_stmt->fetchAll(PDO::FETCH_COLUMN);

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
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Community</h1>
                
                <?php if (isset($_GET['created']) && $_GET['created'] == 'success'): ?>
                    <div class="alert alert-success">Your post has been created successfully!</div>
                <?php endif; ?>
                
                <?php if (isset($_GET['edited']) && $_GET['edited'] == 'success'): ?>
                    <div class="alert alert-success">Your post has been updated successfully!</div>
                <?php endif; ?>
                
                <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 'success'): ?>
                    <div class="alert alert-success">Your post has been deleted successfully!</div>
                <?php endif; ?>
                
                <!-- Search bar at the top level -->
                <form class="d-flex mb-4" method="GET" action="community.php">
                    <input type="hidden" name="tab" value="<?= $active_tab ?>">
                    <?php if (!empty($category_filter)): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($category_filter) ?>">
                    <?php endif; ?>
                    <?php if (!empty($sort_by)): ?>
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort_by) ?>">
                    <?php endif; ?>
                    <input class="form-control me-2" type="search" name="search" placeholder="Search..." value="<?= htmlspecialchars($search_query); ?>">
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </form>

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
            </div>
        </div>
        
        <!-- Tab content -->
        <div class="tab-content">
            <!-- Discussion Posts Tab -->
            <div class="tab-pane <?php echo ($active_tab == 'discussions') ? 'active' : 'fade'; ?>">
                <div class="row">
                    <!-- Filter sidebar - now as a regular column -->
                    <?php if ($active_tab == 'discussions'): ?>
                    <div class="col-md-3">
                        <div class="filter-sidebar">
                            <h4 class="filter-heading">Post Filters</h4>
                            
                            <!-- Categories Section -->
                            <div class="filter-section mb-4">
                                <h5 class="filter-subheading">Categories</h5>
                                <div class="dropdown w-100 mb-3">
                                    <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <?= !empty($category_filter) ? htmlspecialchars($category_filter) : 'Select Category' ?>
                                    </button>
                                    <ul class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton">
                                        <li><a class="dropdown-item <?= empty($category_filter) ? 'active' : '' ?>" 
                                            href="?tab=discussions<?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?><?= !empty($sort_by) ? '&sort='.$sort_by : '' ?>">
                                            All Categories
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <?php foreach($categories as $category): ?>
                                        <li><a class="dropdown-item <?= ($category_filter == $category) ? 'active' : '' ?>" 
                                            href="?tab=discussions&category=<?= urlencode($category) ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?><?= !empty($sort_by) ? '&sort='.$sort_by : '' ?>">
                                            <?= htmlspecialchars($category) ?>
                                        </a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Sort Options Section -->
                            <div class="filter-section">
                                <h5 class="filter-subheading">Sort By</h5>
                                <div class="list-group">
                                    <a href="?tab=discussions<?= !empty($category_filter) ? '&category='.urlencode($category_filter) : '' ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>&sort=newest" 
                                    class="list-group-item <?= ($sort_by == 'newest' || empty($sort_by)) ? 'active' : '' ?>">
                                        <i class="fas fa-clock me-2"></i> Newest First
                                    </a>
                                    <a href="?tab=discussions<?= !empty($category_filter) ? '&category='.urlencode($category_filter) : '' ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>&sort=most_likes" 
                                    class="list-group-item <?= ($sort_by == 'most_likes') ? 'active' : '' ?>">
                                        <i class="fas fa-heart me-2"></i> Most Likes
                                    </a>
                                    <a href="?tab=discussions<?= !empty($category_filter) ? '&category='.urlencode($category_filter) : '' ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>&sort=most_views" 
                                    class="list-group-item <?= ($sort_by == 'most_views') ? 'active' : '' ?>">
                                        <i class="fas fa-eye me-2"></i> Most Views
                                    </a>
                                    <a href="?tab=discussions<?= !empty($category_filter) ? '&category='.urlencode($category_filter) : '' ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>&sort=most_comments" 
                                    class="list-group-item <?= ($sort_by == 'most_comments') ? 'active' : '' ?>">
                                        <i class="fas fa-comments me-2"></i> Most Comments
                                    </a>
                                </div>
                            </div>
                            
                            <?php if (!empty($category_filter) || !empty($sort_by) && $sort_by !== 'newest'): ?>
                            <!-- Clear Filters -->
                            <div class="mt-4">
                                <a href="?tab=discussions<?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>" 
                                class="btn btn-outline-secondary btn-sm w-100">
                                    <i class="fas fa-times-circle me-1"></i> Clear All Filters
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Main content -->
                    <div class="<?= ($active_tab == 'discussions') ? 'col-md-9' : 'col-md-12' ?>">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="mb-0">
                                Recent Discussions
                                <?php if (!empty($sort_by) && $sort_by != 'newest'): ?>
                                    <small class="text-muted fs-6">
                                        (Sorted by: <?= str_replace('_', ' ', ucfirst($sort_by)) ?>)
                                    </small>
                                <?php endif; ?>
                            </h2>
                            <div>
                                <?php if ($user_id): ?>
                                    <a href="manage_post.php" class="btn btn-sm btn-outline-primary me-2">
                                        <i class="fas fa-tasks"></i> Manage My Posts
                                    </a>
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
                                <!-- Post cards remain unchanged -->
                                <div class="card post-card mb-4">
                                    <div class="post-header">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($row['title']); ?></h5>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($row['category']); ?></span>
                                        </div>
                                        <small class="text-muted">
                                            Posted by 
                                            <span class="fw-bold">
                                                <?php echo htmlspecialchars($row['nickname']); ?>
                                            </span> 
                                            on <?php echo date('M d, Y', strtotime($row['post_date'])); ?>
                                        </small>
                                    </div>

                                    <?php if (!empty($row['image_url'])): ?>
                                        <div class="post-image">
                                        <?php
                                            // Check if it's a full path from uploads folder or just a filename for assets folder
                                            if (strpos($row['image_url'], 'uploads/discussion_post_img/') !== false) {
                                                // It's a real uploaded image in the new folder
                                                $image_path = '../' . $row['image_url'];
                                            } else if (strpos($row['image_url'], 'uploads/posts/') !== false) {
                                                // It's a real uploaded image in the old folder
                                                $image_path = '../' . $row['image_url'];
                                            } else {
                                                // It's a dummy data filename
                                                $image_path = '../assets/community/discussion_posts_img/' . basename($row['image_url']);
                                            }
                                        ?>
                                            <img src="<?php echo htmlspecialchars($image_path); ?>" class="img-fluid" alt="Post Image">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="card-body">
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($row['content'], 0, 200) . (strlen($row['content']) > 200 ? '...' : ''))); ?></p>
                                    </div>
                                    <div class="post-actions">
                                        <div class="d-flex">
                                            <?php 
                                            // Since we don't track user likes anymore, set default state to unliked
                                            $has_liked = false;
                                            $like_btn_class = 'btn-outline-danger';
                                            ?>
                                            
                                            <button class="btn btn-sm <?php echo $like_btn_class; ?> like-btn" 
                                                    data-post-id="<?php echo $row['post_id']; ?>"
                                                    data-liked="<?php echo $has_liked ? 'true' : 'false'; ?>">
                                                <i class="fas fa-heart"></i> Like <span class="like-count">(<?php echo $row['like_count']; ?>)</span>
                                            </button>
                                            
                                            <button class="btn btn-sm btn-outline-primary ms-2 comment-btn" 
                                                    data-post-id="<?php echo $row['post_id']; ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#commentModal"
                                                    data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                                    data-author="<?php echo htmlspecialchars($row['nickname']); ?>"
                                                    data-author-id="<?php echo $row['user_id']; ?>"
                                                    data-content="<?php echo htmlspecialchars($row['content']); ?>"
                                                    data-image="<?php echo !empty($row['image_url']) ? ($row['image_url']) : ''; ?>"
                                                    data-category="<?php echo htmlspecialchars($row['category']); ?>"
                                                    data-date="<?php echo $row['post_date']; ?>"
                                                    data-like-count="<?php echo $row['like_count']; ?>"
                                                    data-comment-count="<?php echo $row['comment_count']; ?>"
                                                    data-liked="<?php echo $has_liked ? 'true' : 'false'; ?>"
                                                    data-author-avatar="<?php echo isset($row['avatar']) ? $row['avatar'] : 0; ?>">
                                                <i class="fas fa-comment"></i> Comments (<?php echo $row['comment_count']; ?>)
                                            </button>
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
                </div>
            </div>
            
            <!-- Recipe Feedback Tab -->
            <div class="tab-pane <?php echo ($active_tab == 'recipes') ? 'active' : 'fade'; ?>">
                <div class="row">
                    <!-- Recipe Filter sidebar -->
                    <div class="col-md-3">
                        <div class="filter-sidebar">
                            <h4 class="filter-heading">Recipe Filters</h4>
                            
                            <!-- Recipe Categories Section -->
                            <div class="filter-section mb-4">
                                <h5 class="filter-subheading">Categories</h5>
                                <div class="dropdown w-100 mb-3">
                                    <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button" id="recipeDropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <?= !empty($recipe_category) ? htmlspecialchars($recipe_category) : 'Select Category' ?>
                                    </button>
                                    <ul class="dropdown-menu w-100" aria-labelledby="recipeDropdownMenuButton">
                                        <li><a class="dropdown-item <?= empty($recipe_category) ? 'active' : '' ?>" 
                                            href="?tab=recipes<?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?><?= !empty($recipe_sort) ? '&recipe_sort='.$recipe_sort : '' ?>">
                                            All Categories
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <?php foreach($recipe_categories as $category): ?>
                                        <li><a class="dropdown-item <?= ($recipe_category == $category) ? 'active' : '' ?>" 
                                            href="?tab=recipes&recipe_category=<?= urlencode($category) ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?><?= !empty($recipe_sort) ? '&recipe_sort='.$recipe_sort : '' ?>">
                                            <?= htmlspecialchars($category) ?>
                                        </a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Sort Options Section - Update the label to "Highest Rated" -->
                            <div class="filter-section">
                                <h5 class="filter-subheading">Sort By</h5>
                                <div class="list-group">
                                    <a href="?tab=recipes<?= !empty($recipe_category) ? '&recipe_category='.urlencode($recipe_category) : '' ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>&recipe_sort=newest" 
                                    class="list-group-item <?= ($recipe_sort == 'newest' || empty($recipe_sort)) ? 'active' : '' ?>">
                                        <i class="fas fa-clock me-2"></i> Newest First
                                    </a>
                                    <a href="?tab=recipes<?= !empty($recipe_category) ? '&recipe_category='.urlencode($recipe_category) : '' ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>&recipe_sort=most_ratings" 
                                    class="list-group-item <?= ($recipe_sort == 'most_ratings') ? 'active' : '' ?>">
                                        <i class="fas fa-star me-2"></i> Highest Rated
                                    </a>
                                    <a href="?tab=recipes<?= !empty($recipe_category) ? '&recipe_category='.urlencode($recipe_category) : '' ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>&recipe_sort=most_views" 
                                    class="list-group-item <?= ($recipe_sort == 'most_views') ? 'active' : '' ?>">
                                        <i class="fas fa-eye me-2"></i> Most Views
                                    </a>
                                    <a href="?tab=recipes<?= !empty($recipe_category) ? '&recipe_category='.urlencode($recipe_category) : '' ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>&recipe_sort=most_comments" 
                                    class="list-group-item <?= ($recipe_sort == 'most_comments') ? 'active' : '' ?>">
                                        <i class="fas fa-comments me-2"></i> Most Comments
                                    </a>
                                </div>
                            </div>
                            
                            <?php if (!empty($recipe_category) || !empty($recipe_sort) && $recipe_sort !== 'newest'): ?>
                            <!-- Clear Filters -->
                            <div class="mt-4">
                                <a href="?tab=recipes<?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>" 
                                class="btn btn-outline-secondary btn-sm w-100">
                                    <i class="fas fa-times-circle me-1"></i> Clear All Filters
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recipe main content -->
                    <div class="col-md-9">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="mb-0">
                                Recipe Feedback
                                <?php if (!empty($recipe_sort) && $recipe_sort != 'newest'): ?>
                                    <small class="text-muted fs-6">
                                        (Sorted by: <?= $recipe_sort == 'most_ratings' ? 'Highest Rated' : str_replace('_', ' ', ucfirst($recipe_sort)) ?>)
                                    </small>
                                <?php endif; ?>
                                <?php if (!empty($recipe_category)): ?>
                                    <small class="text-muted fs-6">
                                        - <?= htmlspecialchars($recipe_category) ?>
                                    </small>
                                <?php endif; ?>
                            </h2>
                        </div>
                        
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
                                                    <div>
                                                        <span class="text-muted me-3"><i class="fas fa-comment"></i> <?php echo $recipe['comment_count']; ?></span>
                                                        <span class="text-muted"><i class="fas fa-eye"></i> <?php echo isset($recipe['view_count']) ? intval($recipe['view_count']) : 0; ?></span>
                                                    </div>
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
                            <div class="alert alert-info">
                                <?php if (!empty($recipe_category)): ?>
                                    No recipes found in the "<?= htmlspecialchars($recipe_category) ?>" category.
                                <?php else: ?>
                                    No recipes available for feedback.
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Comments Modal -->
    <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2 text-center">
                    <h5 class="modal-title w-100" id="commentModalLabel"><span id="modal-post-author-title"></span>'s Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <!-- Scrollable content area -->
                    <div class="modal-scroll-area">
                        <!-- Post Content -->
                        <div class="post-preview mb-3">
                            <h5 id="modal-post-title" class="mb-2"></h5>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="text-muted small d-flex align-items-center">
                                    <img id="modal-author-avatar" src="../assets/avatars/avatar1.png" class="rounded-circle me-2" alt="Profile" style="width: 24px; height: 24px; object-fit: cover;">
                                    Posted by <span id="modal-post-author" class="fw-bold ms-1"></span>
                                    <span class="ms-1">on</span><span id="modal-post-date" class="ms-1"></span>
                                </div>
                            </div>
                            
                            <!-- Image container -->
                            <div id="modal-post-image" class="mb-3 d-none text-center">
                                <img src="" class="img-fluid rounded mx-auto" alt="Post Image">
                            </div>
                            
                            <!-- Content with scroll if needed -->
                            <div id="modal-post-content" class="mb-3 pb-2"></div>
                            
                            <!-- Like section for the post -->
                            <div class="d-flex justify-content-between align-items-center border-top border-bottom py-2 mb-3">
                                <div>
                                    <button id="modal-like-btn" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-heart"></i> Like <span id="modal-like-count">(0)</span>
                                    </button>
                                </div>
                                <div>
                                    <span class="text-muted"><i class="fas fa-comment"></i> <span id="modal-comment-count">0</span> comments</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Comments Section - This will scroll independently -->
                        <div>
                            <h6 class="mb-2"><i class="fas fa-comments"></i> Comments</h6>
                            <div id="comments-container" class="mb-3">
                                <div class="text-center p-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Extra space to ensure scrolling works properly -->
                        <div style="height: 20px;"></div>
                    </div>
                    
                    <!-- Comment form stays at the bottom of modal and doesn't scroll -->
                    <div id="comment-form-container">
                        <?php if ($user_id): ?>
                        <form id="comment-form">
                            <input type="hidden" id="comment-post-id" name="post_id" value="">
                            <div class="input-group">
                                <textarea class="form-control" id="comment-text" name="comment" rows="2" placeholder="Write a comment..."></textarea>
                                <button type="submit" class="btn btn-primary send-comment-btn">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-info py-2">
                            <a href="../users/login.php">Login</a> to post comments.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize liked posts from localStorage with user-specific key
            let likedPosts = [];
            <?php if ($user_id): ?>
            try {
                // Make the localStorage key user-specific to prevent sharing across accounts
                const savedLikes = localStorage.getItem('likedPosts_user_<?php echo $user_id; ?>');
                if (savedLikes) {
                    likedPosts = JSON.parse(savedLikes);
                }
            } catch (e) {
                console.error("Error loading liked posts from localStorage:", e);
                likedPosts = [];
            }
            <?php endif; ?>
            
            // Apply liked status to buttons on page load
            function initializeLikeButtons() {
                <?php if ($user_id): ?>
                $('.like-btn').each(function() {
                    const postId = parseInt($(this).data('post-id'));
                    if (likedPosts.includes(postId)) {
                        $(this).removeClass('btn-outline-danger').addClass('btn-danger');
                        $(this).data('liked', 'true');
                    }
                });
                <?php endif; ?>
            }
            
            // Call initialize on page load
            initializeLikeButtons();
            
            // Unified like button handler for both page and modal buttons
            $(document).on('click', '.like-btn, #modal-like-btn', function() {
                // Only proceed if user is logged in
                <?php if (!$user_id): ?>
                    window.location.href = '../users/login.php';
                    return;
                <?php endif; ?>
                
                const button = $(this);
                const postId = parseInt(button.data('post-id'));
                const isModalBtn = button.attr('id') === 'modal-like-btn';
                
                // Get the like count span based on button type
                const likeCountSpan = isModalBtn ? $('#modal-like-count') : button.find('.like-count');
                const currentCount = parseInt(likeCountSpan.text().replace(/[()]/g, '')) || 0;
                const isLiked = button.data('liked') === 'true';
                
                // Find related button (either modal or main page)
                const relatedBtn = isModalBtn ? 
                    $('.like-btn[data-post-id="' + postId + '"]') : 
                    $('#modal-like-btn').filter(function() {
                        return $(this).data('post-id') == postId;
                    });
                const relatedCountSpan = isModalBtn ? 
                    relatedBtn.find('.like-count') : 
                    $('#modal-like-count');
                
                // Change button appearance 
                if (isLiked) {
                    // User is unliking
                    button.removeClass('btn-danger').addClass('btn-outline-danger');
                    button.data('liked', 'false');
                    likeCountSpan.text('(' + Math.max(0, currentCount - 1) + ')');
                    
                    // Update related button if it exists
                    if (relatedBtn.length) {
                        relatedBtn.removeClass('btn-danger').addClass('btn-outline-danger');
                        relatedBtn.data('liked', 'false');
                        relatedCountSpan.text('(' + Math.max(0, currentCount - 1) + ')');
                    }
                    
                    // Update localStorage - with user-specific key
                    likedPosts = likedPosts.filter(id => id !== postId);
                    localStorage.setItem('likedPosts_user_<?php echo $user_id; ?>', JSON.stringify(likedPosts));
                } else {
                    // User is liking
                    button.removeClass('btn-outline-danger').addClass('btn-danger');
                    button.data('liked', 'true');
                    likeCountSpan.text('(' + (currentCount + 1) + ')');
                    
                    // Animation for new like
                    button.find('i').addClass('fa-beat');
                    setTimeout(function() {
                        button.find('i').removeClass('fa-beat');
                    }, 800);
                    
                    // Update related button if it exists
                    if (relatedBtn.length) {
                        relatedBtn.removeClass('btn-outline-danger').addClass('btn-danger');
                        relatedBtn.data('liked', 'true');
                        relatedCountSpan.text('(' + (currentCount + 1) + ')');
                    }
                    
                    // Update localStorage - with user-specific key
                    if (!likedPosts.includes(postId)) {
                        likedPosts.push(postId);
                        localStorage.setItem('likedPosts_user_<?php echo $user_id; ?>', JSON.stringify(likedPosts));
                    }
                }
                
                // Disable button temporarily
                button.prop('disabled', true);
                if (relatedBtn.length) relatedBtn.prop('disabled', true);
                
                // Send the Ajax request with the appropriate action
                $.ajax({
                    url: 'ajax/like_post.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        post_id: postId,
                        action: isLiked ? 'unlike' : 'like'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update with server data to ensure accuracy
                            var newCount = '(' + response.like_count + ')';
                            likeCountSpan.text(newCount);
                            if (relatedBtn.length) relatedCountSpan.text(newCount);
                        } else {
                            console.error(response.error);
                            alert('Error: ' + response.error);
                            
                            // Revert changes on error
                            if (isLiked) {
                                // Revert unlike
                                button.removeClass('btn-outline-danger').addClass('btn-danger');
                                button.data('liked', 'true');
                                if (relatedBtn.length) {
                                    relatedBtn.removeClass('btn-outline-danger').addClass('btn-danger');
                                    relatedBtn.data('liked', 'true');
                                }
                                // Restore to liked posts
                                if (!likedPosts.includes(postId)) {
                                    likedPosts.push(postId);
                                    localStorage.setItem('likedPosts_user_<?php echo $user_id; ?>', JSON.stringify(likedPosts));
                                }
                            } else {
                                // Revert like
                                button.removeClass('btn-danger').addClass('btn-outline-danger');
                                button.data('liked', 'false');
                                if (relatedBtn.length) {
                                    relatedBtn.removeClass('btn-danger').addClass('btn-outline-danger');
                                    relatedBtn.data('liked', 'false');
                                }
                                // Remove from liked posts
                                likedPosts = likedPosts.filter(id => id !== postId);
                                localStorage.setItem('likedPosts_user_<?php echo $user_id; ?>', JSON.stringify(likedPosts));
                            }
                            
                            likeCountSpan.text('(' + currentCount + ')');
                            if (relatedBtn.length) relatedCountSpan.text('(' + currentCount + ')');
                        }
                        
                        // Re-enable buttons
                        button.prop('disabled', false);
                        if (relatedBtn.length) relatedBtn.prop('disabled', false);
                    },
                    error: function() {
                        // Revert changes on error
                        if (isLiked) {
                            button.removeClass('btn-outline-danger').addClass('btn-danger');
                            button.data('liked', 'true');
                            if (relatedBtn.length) {
                                relatedBtn.removeClass('btn-outline-danger').addClass('btn-danger');
                                relatedBtn.data('liked', 'true');
                            }
                            // Restore to liked posts
                            if (!likedPosts.includes(postId)) {
                                likedPosts.push(postId);
                                localStorage.setItem('likedPosts_user_<?php echo $user_id; ?>', JSON.stringify(likedPosts));
                            }
                        } else {
                            button.removeClass('btn-danger').addClass('btn-outline-danger');
                            button.data('liked', 'false');
                            if (relatedBtn.length) {
                                relatedBtn.removeClass('btn-danger').addClass('btn-outline-danger');
                                relatedBtn.data('liked', 'false');
                            }
                            // Remove from liked posts
                            likedPosts = likedPosts.filter(id => id !== postId);
                            localStorage.setItem('likedPosts_user_<?php echo $user_id; ?>', JSON.stringify(likedPosts));
                        }
                        
                        likeCountSpan.text('(' + currentCount + ')');
                        if (relatedBtn.length) relatedCountSpan.text('(' + currentCount + ')');
                        
                        console.error('AJAX request failed');
                        alert('Network error. Please try again.');
                        button.prop('disabled', false);
                        if (relatedBtn.length) relatedBtn.prop('disabled', false);
                    }
                });
            });
            
            // Handle comment modal
            $('#commentModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var postId = button.data('post-id');
                var title = button.data('title');
                var author = button.data('author');
                var content = button.data('content');
                var image = button.data('image');
                var postDate = button.data('date');
                // Get author ID to fetch the most current avatar
                var authorId = button.data('author-id');
                // CRITICAL FIX: Always get the current like status from the main page button
                var mainPageBtn = $('.like-btn[data-post-id="' + postId + '"]');
                var isLiked = mainPageBtn.hasClass('btn-danger');
                var likeCount = parseInt(mainPageBtn.find('.like-count').text().replace(/[()]/g, '')) || 0;
                
                var commentCount = button.data('comment-count');
                
                var modal = $(this);
                
                // Set post details
                modal.find('#modal-post-title').text(title);
                modal.find('#modal-post-author').text(author);
                modal.find('#modal-post-author-title').text(author);
                modal.find('#modal-post-content').text(content);
                
                // Track post view when modal opens (only for non-authors and logged-in users)
                $.ajax({
                    url: 'ajax/track_view.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { post_id: postId },
                    success: function(response) {
                        if (response.success) {
                            // Update view count on the post card if the view was counted
                            var viewCountSpan = $('.comment-btn[data-post-id="' + postId + '"]')
                                .closest('.post-actions')
                                .find('.text-muted');
                                
                            if (viewCountSpan.length) {
                                viewCountSpan.html('<i class="fas fa-eye"></i> ' + response.view_count + ' views');
                            }
                        }
                    },
                    error: function() {
                        console.log('Error tracking view');
                    }
                });
                
                // Fetch the author's current avatar
                $.ajax({
                    url: 'ajax/get_user_avatar.php',
                    type: 'GET',
                    dataType: 'json',
                    data: { user_id: authorId },
                    success: function(response) {
                        if (response.success) {
                            // Calculate the avatar path based on the avatar number from the database
                            var authorAvatarNum = parseInt(response.avatar);
                            var authorAvatarFile = 'avatar' + (authorAvatarNum + 1) + '.png';
                            var authorAvatarPath = '../assets/avatars/' + authorAvatarFile;
                            
                            // Update the avatar in the modal
                            modal.find('#modal-author-avatar').attr('src', authorAvatarPath);
                        } else {
                            // Fallback to default avatar
                            modal.find('#modal-author-avatar').attr('src', '../assets/avatars/avatar1.png');
                        }
                    },
                    error: function() {
                        // Fallback to default avatar on error
                        modal.find('#modal-author-avatar').attr('src', '../assets/avatars/avatar1.png');
                    }
                });
                
                // Format and set date
                if (postDate) {
                    var date = new Date(postDate);
                    var options = { year: 'numeric', month: 'long', day: 'numeric' };
                    var formattedDate = date.toLocaleDateString('en-US', options);
                    modal.find('#modal-post-date').text(formattedDate);
                } else {
                    modal.find('#modal-post-date').text('');
                }
                
                // Set like button and counts - Always use the main page's current state
                var likeBtn = modal.find('#modal-like-btn');
                likeBtn.data('post-id', postId);
                
                // Update data-liked attribute to match current state
                likeBtn.data('liked', isLiked ? 'true' : 'false');
                
                // Reset button appearance and apply correct styling
                likeBtn.removeClass('btn-danger btn-outline-danger');
                if (isLiked) {
                    likeBtn.addClass('btn-danger');
                } else {
                    likeBtn.addClass('btn-outline-danger');
                }
                
                modal.find('#modal-like-count').text('(' + likeCount + ')');
                
                // Handle image
                if (image) {
                    var imagePath;
                    if (image.includes('uploads/discussion_post_img/')) {
                        // New folder path
                        imagePath = '../' + image;
                    } else if (image.includes('uploads/posts/')) {
                        // Old folder path
                        imagePath = '../' + image;
                    } else {
                        // Dummy images path
                        imagePath = '../assets/community/discussion_posts_img/' + image.split('/').pop();
                    }
                    modal.find('#modal-post-image').removeClass('d-none');
                    modal.find('#modal-post-image img').attr('src', imagePath);
                } else {
                    modal.find('#modal-post-image').addClass('d-none');
                }
                
                // Store post ID for comment form
                $('#comment-post-id').val(postId);
                
                // Load comments
                loadComments(postId);

                // Ensure the modal is properly sized after content is loaded
                setTimeout(function() {
                    $(window).trigger('resize');
                }, 200);
                
                // Also add code to check if this post is in likedPosts (with user check)
                <?php if ($user_id): ?>
                var isLiked = likedPosts.includes(parseInt(postId));
                likeBtn.data('liked', isLiked ? 'true' : 'false');
                
                // Reset button appearance and apply correct styling
                likeBtn.removeClass('btn-danger btn-outline-danger');
                if (isLiked) {
                    likeBtn.addClass('btn-danger');
                } else {
                    likeBtn.addClass('btn-outline-danger');
                }
                <?php endif; ?>
            });
            
            // Function to load comments
            function loadComments(postId) {
                $('#comments-container').html('<div class="text-center p-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
                
                $.ajax({
                    url: 'ajax/load_comments.php',
                    type: 'GET',
                    dataType: 'json',
                    data: { post_id: postId },
                    success: function(response) {
                        if (response.success) {
                            // Update the comment count display in the modal
                            if (response.comment_count !== undefined) {
                                $('#modal-comment-count').text(response.comment_count);
                            } else {
                                // If comment_count is not provided, use the length of comments array
                                $('#modal-comment-count').text(response.comments.length);
                            }
                            
                            if (response.comments.length > 0) {
                                var html = '<div class="comment-list">';
                                for (var i = 0; i < response.comments.length; i++) {
                                    html += renderComment(response.comments[i]);
                                }
                                // Add a scroll indicator at the end
                                html += '<div class="scroll-indicator"><i class="fas fa-arrow-down fa-xs"></i> End of comments</div>';
                                html += '</div>';
                                $('#comments-container').html(html);
                                
                                // Always scroll to top when loading comments
                                setTimeout(function() {
                                    var scrollArea = $('.modal-scroll-area');
                                    scrollArea.scrollTop(0);
                                }, 100);
                            } else {
                                $('#comments-container').html('<div class="alert alert-light text-center">No comments yet. Be the first to comment!</div>');
                                // Set comment count to 0 if there are no comments
                                $('#modal-comment-count').text('0');
                            }
                        } else {
                            $('#comments-container').html('<div class="alert alert-danger">Error loading comments</div>');
                        }
                    },
                    error: function() {
                        $('#comments-container').html('<div class="alert alert-danger">Error connecting to server</div>');
                    }
                });
            }
            
            // Function to render a comment 
            function renderComment(comment) {
                var date = new Date(comment.comment_date);
                // Format date as "April 13, 2025" style
                var options = { year: 'numeric', month: 'long', day: 'numeric' };
                var formattedDate = date.toLocaleDateString('en-US', options);
                
                // Map avatar number from database (0-5) to avatar filename (avatar1.png - avatar6.png) 
                var avatarNum = parseInt(comment.avatar);
                
                // Convert avatar value (0-5) to avatar filename (avatar1.png - avatar6.png)
                // In the database: 0 → avatar1.png, 1 → avatar2.png, etc.
                var avatarFile = 'avatar' + (avatarNum + 1) + '.png';
                var profileImage = '../assets/avatars/' + avatarFile;
                
                var html = '<div class="comment-item mb-2">' +
                    '<div class="d-flex">' +
                        '<div class="flex-shrink-0 me-2">' +
                            '<img src="' + profileImage + '" alt="Profile" class="rounded-circle" ' +
                            'style="width: 32px; height: 32px; object-fit: cover;" ' +
                            'onerror="this.onerror=null; this.src=\'../assets/avatars/avatar1.png\';">' +
                        '</div>' +
                        '<div class="flex-grow-1">' +
                            '<div class="bg-light p-2 rounded">' +
                                '<div class="d-flex justify-content-between">' +
                                    '<div><strong class="small">' + comment.nickname + '</strong></div>' +
                                    '<small class="text-muted ms-2" style="font-size:0.7rem;">' + formattedDate + '</small>' +
                                '</div>' +
                                '<div class="comment-text">' + comment.comment_text + '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
                
                return html;
            }
            
            // Handle comment submission
            $('#comment-form').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var postId = $('#comment-post-id').val();
                var commentText = $('#comment-text').val().trim();
                var submitButton = form.find('button[type="submit"]');
                
                if (commentText === '') {
                    return;
                }
                
                // Disable submit button and show loading state
                submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                
                // Remember current scroll position
                var scrollArea = $('.modal-scroll-area');
                var currentScrollPosition = scrollArea.scrollTop();
                
                $.ajax({
                    url: 'ajax/add_comment.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        post_id: postId,
                        comment: commentText
                    },
                    success: function(response) {
                        if (response.success) {
                            // Clear form
                            $('#comment-text').val('');
                            
                            // Add new comment to the comment list
                            var newCommentHtml = renderComment(response.comment);
                            
                            if ($('#comments-container .comment-list').length) {
                                // Add the comment to the list - append instead of prepend to maintain chronology
                                $('#comments-container .comment-list').append(newCommentHtml);
                                
                                // Remove the scroll indicator first if it exists
                                $('#comments-container .comment-list .scroll-indicator').remove();
                                
                                // Re-add the scroll indicator
                                $('#comments-container .comment-list').append(
                                    '<div class="scroll-indicator"><i class="fas fa-arrow-down fa-xs"></i> End of comments</div>'
                                );
                                
                                // Restore scroll position with a slight offset to show there's something new
                                setTimeout(function() {
                                    // Scroll to the bottom to see new comment
                                    scrollArea.scrollTop(scrollArea[0].scrollHeight);
                                }, 100);
                            } else {
                                $('#comments-container').html('<div class="comment-list">' + newCommentHtml + 
                                    '<div class="scroll-indicator"><i class="fas fa-arrow-down fa-xs"></i> End of comments</div></div>');
                                
                                // Scroll to see the comment
                                setTimeout(function() {
                                    scrollArea.scrollTop(scrollArea[0].scrollHeight - 400);
                                }, 100);
                            }
                            
                            // Update comment count in button that opened the modal
                            $('.comment-btn[data-post-id="' + postId + '"]').html(
                                '<i class="fas fa-comment"></i> Comments (' + response.comment_count + ')'
                            );
                            
                            // Update modal comment count
                            $('#modal-comment-count').text(response.comment_count);
                        } else {
                            alert('Error posting comment: ' + response.error);
                        }
                        
                        // Reset button
                        submitButton.prop('disabled', false).html('<i class="fas fa-paper-plane"></i>');
                    },
                    error: function() {
                        alert('Error connecting to server. Please try again.');
                        submitButton.prop('disabled', false).html('<i class="fas fa-paper-plane"></i>');
                    }
                });
            });

            // Add function to ensure comment form is always visible after resize
            $(window).on('resize', function() {
                if ($('#commentModal').hasClass('show')) {
                    // Adjust padding if needed
                    var formHeight = $('#comment-form-container').outerHeight();
                    $('.modal-scroll-area').css('padding-bottom', (formHeight + 20) + 'px');
                }
            });
            
            // Ensure the modal layout is correct when it's opened
            $('#commentModal').on('shown.bs.modal', function() {
                var formHeight = $('#comment-form-container').outerHeight();
                $('.modal-scroll-area').css('padding-bottom', (formHeight + 20) + 'px');
            });
        });
    </script>
    <!-- Footer -->                       
    <?php include_once '../includes/footer.php'; ?>
</body>
</html>