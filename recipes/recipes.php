<?php
session_start();
require '../config/db.php'; // Adjust path to db.php

// Fetch all recipes
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$query = "SELECT * FROM Recipes WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND title LIKE :search";
    $params[':search'] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND category = :category";
    $params[':category'] = $category;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipes - NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .recipe-card {
            transition: transform 0.2s;
        }
        .recipe-card:hover {
            transform: scale(1.02);
        }
    </style>
</head>
<body>
    <?php include '../navigation.php'; ?> <!-- Include navigation bar -->

    <div class="container mt-5">
        <h1 class="text-center mb-4">All Recipes</h1>

        <!-- Search and Filter Bar -->
        <form method="GET" action="" class="mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by title..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <option value="Appetizer" <?= $category === 'Appetizer' ? 'selected' : '' ?>>Appetizer</option>
                        <option value="Main Course" <?= $category === 'Main Course' ? 'selected' : '' ?>>Main Course</option>
                        <option value="Dessert" <?= $category === 'Dessert' ? 'selected' : '' ?>>Dessert</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        <!-- Recipe Cards -->
        <div class="row">
            <?php if (empty($recipes)): ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No recipes found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($recipes as $recipe): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card recipe-card h-100">
                            <?php if (!empty($recipe['image_url'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($recipe['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($recipe['title']) ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300" class="card-img-top" alt="Placeholder Image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($recipe['description']) ?></p>
                                <p class="card-text"><small class="text-muted">Category: <?= htmlspecialchars($recipe['category']) ?></small></p>
                                <a href="view.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-primary">View Recipe</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>