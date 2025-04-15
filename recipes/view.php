<?php
session_start();
include '../config/db.php';

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
    $stmt = $RecipeDB->prepare("SELECT r.*, u.nickname AS author 
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
$nutrition_stmt = $RecipeDB->prepare("SELECT * FROM Nutrition WHERE recipe_id = ?");
$nutrition_stmt->execute([$recipe_id]);
$nutrition = $nutrition_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($recipe['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../recipes/css/recipe.css">


</head>

<body>
    <?php include_once '../includes/navigation.php'; ?> <!-- Include navigation bar -->
    <div class="container mt-4">
        <h1 class="text-center"><?= htmlspecialchars($recipe['title']) ?></h1>
        <p class="text-center text-muted"><strong>By:</strong> <?= htmlspecialchars($recipe['author']) ?></p>


        <!-- Recipe Details Section -->
        <div class="mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Recipe Details</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between flex-wrap">
                        <div>
                            <strong>Category:</strong> <?= htmlspecialchars($recipe['category']) ?>
                        </div>
                        <div>
                            <strong>Cuisine:</strong> <?= htmlspecialchars($recipe['cuisine_type']) ?>
                        </div>
                        <div>
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
        </div>


        <!-- Recipe Image -->
        <div class="text-center mb-4">
            <?php if (!empty($recipe['image_url'])): ?>
                <img src="../uploads/recipe/<?= htmlspecialchars($recipe['image_url']) ?>"
                    alt="Recipe Image"
                    class="img-fluid rounded shadow"
                    style="max-height: 400px; width: auto;">
            <?php else: ?>
                <div class="bg-light rounded p-5 text-muted">
                    No image available
                </div>
            <?php endif; ?>
        </div>

        <!-- Recipe Description -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="mb-0">About This Recipe</h3>
            </div>
            <div class="card-body">
                <p class="card-text"><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>
            </div>
        </div>

        <!-- Ingredients Section -->
        <div class="mb-4">
            <div class="card">
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
                                    <?= htmlspecialchars($ingredient['name']) ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-muted">No ingredients listed</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Steps Section -->
        <div class="mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Cooking Steps</h3>
                </div>
                <div class="card-body">
                    <ol class="list-group list-group-numbered">
                        <?php foreach ($steps as $step): ?>
                            <li class="list-group-item">
                                <div class="fw-bold">Step <?= htmlspecialchars($step['step_no']) ?></div>
                                <p><?= nl2br(htmlspecialchars($step['description'])) ?></p>
                                <?php if (!empty($step['image_url'])): ?>
                                    <div class="mt-2">
                                        <img src="../uploads/recipe/<?= htmlspecialchars($step['image_url']) ?>"
                                            class="img-thumbnail step-image"
                                            style="max-width: 250px; height: auto;">
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Nutrition Facts Section -->
        <?php if ($nutrition): ?>
            <div class="mb-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Nutrition Facts</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>Calories</th>
                                    <td><?= htmlspecialchars($nutrition['calories']) ?> kcal</td>
                                </tr>
                                <tr>
                                    <th>Fat</th>
                                    <td><?= htmlspecialchars($nutrition['fat']) ?> g</td>
                                </tr>
                                <tr>
                                    <th>Carbs</th>
                                    <td><?= htmlspecialchars($nutrition['carbs']) ?> g</td>
                                </tr>
                                <tr>
                                    <th>Protein</th>
                                    <td><?= htmlspecialchars($nutrition['protein']) ?> g</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Edit Button (for recipe owner) -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $recipe['user_id']): ?>
            <div class="mt-4 text-center">
                <a href="edit.php?id=<?= $recipe_id ?>" class="btn btn-primary">Edit Recipe</a>
                <a href="delete.php?id=<?= $recipe_id ?>" class="btn btn-danger">Delete Recipe</a>
            </div>
        <?php endif; ?>


    </div>
    <?php include_once '../includes/footer.php'; ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const recipeId = <?= json_encode($recipe_id) ?>;

            // Send AJAX request to track recipe view
            fetch('../recipes/ajax/track_recipe_view.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `recipe_id=${recipeId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log(`View count updated: ${data.view_count}`);
                        // Optionally update the view count on the page
                        const viewCountElement = document.getElementById('view-count');
                        if (viewCountElement) {
                            viewCountElement.textContent = data.view_count;
                        }
                    } else {
                        console.warn(`View not counted: ${data.error}`);
                    }
                })
                .catch(error => {
                    console.error('Error tracking recipe view:', error);
                });
        });
    </script>
    

</body>

</html>