<?php
session_start();
require '../config/db.php';

// Fetch filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$cuisine = $_GET['cuisine'] ?? '';
$spice_level = $_GET['spice_level'] ?? '';

// Build query
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

if (!empty($cuisine)) {
    $query .= " AND cuisine_type = :cuisine";
    $params[':cuisine'] = $cuisine;
}

if (!empty($spice_level)) {
    $query .= " AND spice_level >= :spice_level";
    $params[':spice_level'] = $spice_level;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique categories for filter dropdown
$categories = $pdo->query("SELECT DISTINCT category FROM Recipes")->fetchAll(PDO::FETCH_COLUMN);
$cuisines = $pdo->query("SELECT DISTINCT cuisine_type FROM Recipes WHERE cuisine_type IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
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
            height: 100%;
        }
        .recipe-card:hover {
            transform: scale(1.02);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .spicy-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
        }
        .filter-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include '../navigation.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Explore Recipes</h1>

        <!-- Enhanced Filter Section -->
        <div class="filter-section mb-4">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search recipes..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="cuisine" class="form-control">
                            <option value="">All Cuisines</option>
                            <?php foreach ($cuisines as $cus): ?>
                                <option value="<?= htmlspecialchars($cus) ?>" <?= $cuisine === $cus ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cus) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="spice_level" class="form-control">
                            <option value="">Any Spice Level</option>
                            <option value="1" <?= $spice_level === '1' ? 'selected' : '' ?>>Mild</option>
                            <option value="2" <?= $spice_level === '2' ? 'selected' : '' ?>>Medium</option>
                            <option value="3" <?= $spice_level === '3' ? 'selected' : '' ?>>Spicy</option>
                            <option value="4" <?= $spice_level === '4' ? 'selected' : '' ?>>Very Spicy</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Recipe Cards -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (empty($recipes)): ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">No recipes match your filters. Try different criteria.</div>
                </div>
            <?php else: ?>
                <?php foreach ($recipes as $recipe): ?>
                    <div class="col">
                        <div class="card recipe-card">
                            <?php if (!empty($recipe['image_url'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($recipe['image_url']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($recipe['title']) ?>">
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