<?php
session_start(); // Start session for user authentication
include '../config/db.php'; // Database connection
include '../navigation.php'; // Include navigation bar

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validate `recipe_id` from GET request
$recipe_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$recipe_id) {
    header("Location: ../error.php?message=Invalid Recipe ID");
    exit();
}

// Check database connection
if (!isset($pdo)) {
    die("Database connection failed. Check config/db.php.");
}

// Fetch recipe details
try {
    $stmt = $pdo->prepare("SELECT r.*, u.username AS author 
                           FROM Recipes r 
                           JOIN Users u ON r.user_id = u.user_id 
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

// Fetch recipe steps
try {
    $step_stmt = $pdo->prepare("SELECT * FROM Steps WHERE recipe_id = ? ORDER BY step_no ASC");
    $step_stmt->execute([$recipe_id]);
    $steps = $step_stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="../assets/style.css"> <!-- Ensure this path is correct -->
</head>
<body>
    
    <div class="container mt-4">
        <h1 class="text-center"><?= htmlspecialchars($recipe['title']) ?></h1>
        <p class="text-center"><strong>By:</strong> <?= htmlspecialchars($recipe['author']) ?></p>
        
        <!-- Recipe Image -->
        <div class="text-center">
            <?php if (!empty($recipe['image_url'])): ?>
                <img src="../uploads/<?= htmlspecialchars($recipe['image_url']) ?>" 
                     alt="Recipe Image" 
                     class="img-fluid rounded" 
                     style="max-width: 100%; height: auto;">
            <?php else: ?>
                <p class="text-muted">No image available</p>
            <?php endif; ?>
        </div>
        
        <!-- Recipe Description -->
        <p class="mt-3"><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>

        <!-- Steps -->
        <h3 class="mt-4">Steps</h3>
        <ol class="list-group list-group-numbered">
            <?php foreach ($steps as $step): ?>
                <li class="list-group-item">
                    <p><strong>Step <?= htmlspecialchars($step['step_no']) ?>:</strong> <?= nl2br(htmlspecialchars($step['description'])) ?></p>
                    <?php if (!empty($step['image_url'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($step['image_url']) ?>" 
                             class="img-fluid rounded" 
                             style="max-width: 100%; height: auto;">
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
