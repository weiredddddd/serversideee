<?php
session_start();
include '../includes/navigation.php';
require '../config/db.php';

// Fetch filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$cuisine = $_GET['cuisine'] ?? '';
$spice_level = $_GET['spice_level'] ?? '';
$letter = $_GET['letter'] ?? '';
$my_recipes = $_GET['my_recipes'] ?? '';

// Build query
$query = "SELECT r.*, u.nickname AS author 
          FROM Recipes r 
          JOIN usersDB.users u ON r.user_id = u.user_id 
          WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE :search OR description LIKE :search OR ingredients LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND category = :category";
    $params[':category'] = $category;
}

if (!empty($cuisine)) {
    $query .= " AND cuisine_type = :cuisine";
    $params[':cuisine'] = $cuisine;
}

if (!empty($spice_level)) {
    $query .= " AND spice_level >= :spice_level";
    $params[':spice_level'] = $spice_level;
}

if (!empty($letter)) {
    $query .= " AND title LIKE :letter";
    $params[':letter'] = "$letter%";
}

if (!empty($my_recipes) && isset($_SESSION['user_id'])) {
    $query .= " AND user_id = :user_id";
    $params[':user_id'] = $_SESSION['user_id'];
}

$stmt = $RecipeDB->prepare($query);
$stmt->execute($params);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique categories for filter dropdown
$categories = $RecipeDB->query("SELECT DISTINCT category FROM Recipes")->fetchAll(PDO::FETCH_COLUMN);
$cuisines = $RecipeDB->query("SELECT DISTINCT cuisine_type FROM Recipes WHERE cuisine_type IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipes - NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../recipes/recipe.css">
        
</head>

<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <!-- Sidebar Filters -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Filter Recipes</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="my_recipes" id="myRecipes" <?= $my_recipes ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="myRecipes">My Recipes</label>
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cuisine</label>
                                <select name="cuisine" class="form-select">
                                    <option value="">All Cuisines</option>
                                    <?php foreach ($cuisines as $cus): ?>
                                        <option value="<?= htmlspecialchars($cus) ?>" <?= $cuisine === $cus ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cus) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Spice Level</label>
                                <select name="spice_level" class="form-select">
                                    <option value="">Any Level</option>
                                    <option value="1" <?= $spice_level === '1' ? 'selected' : '' ?>>Mild</option>
                                    <option value="2" <?= $spice_level === '2' ? 'selected' : '' ?>>Medium</option>
                                    <option value="3" <?= $spice_level === '3' ? 'selected' : '' ?>>Spicy</option>
                                    <option value="4" <?= $spice_level === '4' ? 'selected' : '' ?>>Very Spicy</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                            <a href="?" class="btn btn-outline-secondary w-100 mt-2">Reset Filters</a>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="header-container">
                    <h1>
                        <?php
                        if (!empty($category)) echo htmlspecialchars($category) . " Recipes";
                        elseif (!empty($cuisine)) echo htmlspecialchars($cuisine) . " Cuisine Recipes";
                        elseif (!empty($letter)) echo "Recipes Starting With " . strtoupper($letter);
                        elseif ($my_recipes) echo "My Recipes";
                        else echo "All Recipes";
                        ?>
                    </h1>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="add_recipe.php" class="btn btn-success">
                            <i class="fas fa-plus"></i> New Recipe
                        </a>
                    <?php else: ?>
                        <a href="../users/login.php?redirect=add_recipe.php" class="btn btn-success">
                            <i class="fas fa-plus"></i> New Recipe
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Alphabet Navigation -->
                <div class="alphabet-nav mb-4">
                    <?php foreach (range('A', 'Z') as $char): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['letter' => strtolower($char), 'my_recipes' => ''])) ?>"
                            class="<?= $letter === strtolower($char) ? 'active' : '' ?>">
                            <?= $char ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['letter' => '', 'my_recipes' => ''])) ?>"
                        class="<?= empty($letter) ? 'active' : '' ?>">
                        All
                    </a>
                </div>

                <!-- Recipe Cards -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php if (empty($recipes)): ?>
                        <div class="col-12">
                            <div class="alert alert-info">No recipes found matching your criteria. Try different filters.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recipes as $recipe): ?>
                            <div class="col">
                                <div class="card recipe-card h-100">
                                    <?php if (!empty($recipe['image_url'])): ?>
                                        <img src="../uploads/recipe/<?= htmlspecialchars($recipe['image_url']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($recipe['title']) ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/300x200?text=No+Image" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Placeholder">
                                    <?php endif; ?>

                                    <?php if ($recipe['spice_level'] >= 3): ?>
                                        <span class="spicy-indicator">Spicy!</span>
                                    <?php endif; ?>

                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
                                        <p class="card-text text-muted">
                                            <small>
                                                <?= htmlspecialchars($recipe['category']) ?> •
                                                <?= htmlspecialchars($recipe['cuisine_type'] ?? 'International') ?>
                                                <?php if ($recipe['spice_level'] > 0): ?>
                                                    • <?= str_repeat('🌶️', $recipe['spice_level']) ?>
                                                <?php endif; ?>
                                            </small>
                                        </p>
                                        <p class="card-text"><?= htmlspecialchars(substr($recipe['description'], 0, 100)) ?>...</p>
                                        <p class="text-muted"><small>By <?= htmlspecialchars($recipe['author']) ?></small></p>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="view.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-primary">View Recipe</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>