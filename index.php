<?php
require_once 'config/session_config.php';
require_once 'config/db.php';

// Simple message handling
$message = '';
$message_type = '';

// Check for messages (from logout or delete account)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'success';
    
    // Clear message after retrieving it
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Also check for legacy logout_message
if (isset($_SESSION['logout_message'])) {
    $message = $_SESSION['logout_message'];
    $message_type = $_SESSION['message_type'] ?? 'success';
    
    unset($_SESSION['logout_message']);
    unset($_SESSION['message_type']);
    unset($_SESSION['message_expiry']);
}

// Fetch categories & ingredients for filters
$categories = $RecipeDB->query("SELECT DISTINCT category FROM Recipes")->fetchAll(PDO::FETCH_ASSOC);
// Fetch recipes
$query = "SELECT r.recipe_id, r.title, r.description, r.image_url, r.category, u.username AS author 
          FROM RecipeDB.Recipes r 
          JOIN usersDB.users u ON r.user_id = u.user_id 
          WHERE 1=1";

$params = [];

// Prepare & execute query
$stmt = $RecipeDB->prepare($query);
$stmt->execute($params);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Cuisine | NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* Slideshow container */
        .carousel-item {
            height: 80vh;
            min-height: 400px;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
        }

        /* Dark overlay on each slide */
        .carousel-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        /* Caption styling */
        .carousel-caption {
            z-index: 2;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #fff;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.7);
        }

        .carousel-caption h1 {
            font-size: 3rem;
            font-weight: bold;
        }

        .carousel-caption p {
            font-size: 1.25rem;
        }

        @media (max-width: 768px) {
            .carousel-caption h1 {
                font-size: 2rem;
            }

            .carousel-caption p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include_once 'includes/navigation.php'; ?>    
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show m-0">
        <div class="container">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Slideshow Section -->
    <div id="recipeCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active" style="background-image: url('assets/bg/slideshow1.jpg');">
                <div class="carousel-caption">
                    <h1>Explore Cuisine</h1>
                    <p>Discover amazing recipes from around the world</p>
                </div>
            </div>
            <div class="carousel-item" style="background-image: url('assets/bg/slideshow2.jpg');">
                <div class="carousel-caption">
                    <h1>Delicious Dishes</h1>
                    <p>Handpicked meals for every craving</p>
                </div>
            </div>
            <div class="carousel-item" style="background-image: url('assets/bg/slideshow3.jpg');">
                <div class="carousel-caption">
                    <h1>Cook with Passion</h1>
                    <p>Turn everyday meals into masterpieces</p>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#recipeCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#recipeCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
    </div>

    <!-- Recipe Grid Section -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Trending Recipes</h2>
        <div class="row">
            <?php foreach ($recipes as $recipe): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <img src="uploads/<?= htmlspecialchars($recipe['image_url']) ?>" class="card-img-top" alt="Recipe Image">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($recipe['description']) ?></p>
                            <p class="text-muted"><small>By <?= htmlspecialchars($recipe['author']) ?></small></p>
                            <a href="recipes/view.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-primary">View Recipe</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- Footer -->
    <?php include_once 'includes/footer.php'; ?>
</body>

</html>