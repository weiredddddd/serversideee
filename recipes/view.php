<?php
session_start();
include '../config/db.php';
include '../includes/navigation.php';

if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']); // Clear message after displaying
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$recipe_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$recipe_id) {
    header("Location: ../error.php?message=Invalid Recipe ID");
    exit();
}

if (!isset($RecipeDB)) {
    die("Database connection failed. Check config/db.php.");
}

try {
    $stmt = $RecipeDB->prepare("SELECT r.*, u.username AS author 
                           FROM RecipeDB.Recipes r 
                           JOIN usersDB.users u ON r.user_id = u.user_id 
                           WHERE r.recipe_id = ?");
    $stmt->execute([$recipe_id]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipe) {
        header("Location: ../error.php?message=Recipe not found");
        exit();
    }
} catch (PDOException $e) {
    header("Location: ../error.php?message=Database error: " . urlencode($e->getMessage()));
    exit();
}

try {
    // Get steps
    $step_stmt = $RecipeDB->prepare("SELECT * FROM Steps WHERE recipe_id = ? ORDER BY step_no ASC");
    $step_stmt->execute([$recipe_id]);
    $steps = $step_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get ingredients
    $ingredient_stmt = $RecipeDB->prepare("SELECT i.ingredient_name AS name, ri.quantity, ri.unit 
    FROM Recipe_Ingredient ri
    JOIN Ingredients i ON ri.ingredient_id = i.ingredient_id
    WHERE ri.recipe_id = ?
    ORDER BY ri.recipe_id, ri.ingredient_id");

    $ingredient_stmt->execute([$recipe_id]);
    $ingredients = $ingredient_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header("Location: ../error.php?message=Database error: " . urlencode($e->getMessage()));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($recipe['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .recipe-meta {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .meta-item {
            margin-bottom: 8px;
        }
        .spice-indicator {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .spice-icon {
            color: #dc3545;
        }
        .ingredient-list {
            list-style-type: none;
            padding-left: 0;
        }
        .ingredient-item {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .ingredient-quantity {
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    
    <div class="container mt-4">
        <h1 class="text-center"><?= htmlspecialchars($recipe['title']) ?></h1>
        <p class="text-center text-muted"><strong>By:</strong> <?= htmlspecialchars($recipe['author']) ?></p>
        
        <!-- Recipe Meta Information -->
        <div class="recipe-meta">
            <div class="row">
                <div class="col-md-4">
                    <div class="meta-item">
                        <strong>Category:</strong> <?= htmlspecialchars($recipe['category']) ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="meta-item">
                        <strong>Cuisine:</strong> <?= htmlspecialchars($recipe['cuisine_type']) ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="meta-item">
                        <strong>Spice Level:</strong>
                        <span class="spice-indicator">
                            <?php 
                            $spice_level = $recipe['spice_level'] ?? 0;
                            echo str_repeat('<i class="bi bi-pepper-hot spice-icon"></i>', $spice_level);
                            if ($spice_level == 0) echo 'None';
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recipe Image -->
        <div class="text-center mb-4">
            <?php if (!empty($recipe['image_url'])): ?>
                <img src="../uploads/<?= htmlspecialchars($recipe['image_url']) ?>" 
                     alt="Recipe Image" 
                     class="img-fluid rounded shadow" 
                     style="max-height: 300px; width: auto;">
            <?php else: ?>
                <div class="bg-light rounded p-5 text-muted">
                    No image available
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recipe Description -->
        <div class="card mb-4">
            <div class="card-body">
                <h3 class="card-title">About This Recipe</h3>
                <p class="card-text"><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>
            </div>
        </div>

        <!-- Ingredients and Steps in Two Columns -->
        <div class="row">
            <!-- Ingredients Column -->
            <div class="col-md-5 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="mb-0">Ingredients</h3>
                    </div>
                    <div class="card-body">
                        <ul class="ingredient-list">
                        <?php if (!empty($ingredients)): ?>
    <?php foreach ($ingredients as $ingredient): ?>
        <li class="ingredient-item">
            <span class="ingredient-quantity">
                <?= htmlspecialchars($ingredient['quantity']) ?>
                <?= htmlspecialchars($ingredient['unit']) ?>
            </span>
            <?= htmlspecialchars($ingredient['name']) ?> <!-- This should now work -->
        </li>
    <?php endforeach; ?>
<?php else: ?>
    <li class="text-muted">No ingredients listed</li>
<?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Steps Column -->
            <div class="col-md-7 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="mb-0">Cooking Steps</h3>
                    </div>
                    <div class="card-body">
                        <ol class="list-group list-group-numbered">
                            <?php foreach ($steps as $step): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">Step <?= htmlspecialchars($step['step_no']) ?></div>
                                        <?= nl2br(htmlspecialchars($step['description'])) ?>
                                    </div>
                                    <?php if (!empty($step['image_url'])): ?>
                                        <img src="../uploads/<?= htmlspecialchars($step['image_url']) ?>" 
                                             class="img-thumbnail" 
                                             style="max-width: 100px; height: auto;">
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Button (for recipe owner) -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $recipe['user_id']): ?>
            <div class="mt-4 text-center">
                <a href="edit.php?id=<?= $recipe_id ?>" class="btn btn-primary">Edit Recipe</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>