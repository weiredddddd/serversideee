<?php
include 'navigation.php';
include 'config/db.php';
session_start();

// Fetch categories & ingredients for filters
$categories = $pdo->query("SELECT DISTINCT category FROM Recipes")->fetchAll(PDO::FETCH_ASSOC);

// Fetch recipes
$query = "SELECT r.recipe_id, r.title, r.description, r.image_url, r.category, u.username AS author 
          FROM Recipes r 
          JOIN Users u ON r.user_id = u.user_id 
          WHERE 1=1";

$params = [];

// Prepare & execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Explore Cuisine | NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css"> <!-- Custom CSS -->
    <style>
        /* Slideshow Custom Size */
        .carousel-inner img {
            height: 50vh; /* Half of the viewport height */
            object-fit: cover; /* Ensures the image fits properly */
        }
        .carousel-caption {
            top: 50%; /* Center text vertically */
            transform: translateY(-50%);
        }
    </style>
</head>

<body>
<div class="container mt-4">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success text-center">
            <?= $_SESSION['message']; ?>
        </div>
        <?php unset($_SESSION['message']); // Remove message after displaying ?>
    <?php endif; ?>
</div>
    <!-- Slideshow Section -->
    <div id="recipeCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="uploads/slideshow1.jpg" class="d-block w-100" alt="Slideshow 1">
            </div>
            <div class="carousel-item">
                <img src="uploads/slideshow2.jpg" class="d-block w-100" alt="Slideshow 2">
            </div>
            <div class="carousel-item">
                <img src="uploads/slideshow3.jpg" class="d-block w-100" alt="Slideshow 3">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#recipeCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#recipeCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>

        <!-- Overlay Text -->
        <div class="carousel-caption">
            <h1 class="display-4">Explore Cuisine</h1>
            <p>Discover amazing recipes from around the world</p>
        </div>
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
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p>&copy; <?= date('Y') ?> NoiceFoodie. All Rights Reserved.</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>