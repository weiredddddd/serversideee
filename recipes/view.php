<?php
session_start(); // Start session (if needed for user-specific actions)
include '../config/db.php'; // Adjust path if needed
include '../navigation.php'; // Include navigation bar

// Validate recipe ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../error.php?message=Recipe ID is missing");
    exit();
}

$recipe_id = $_GET['id'];

// Fetch recipe details
try {
    $stmt = $pdo->prepare("SELECT r.*, u.username AS author 
                           FROM Recipes r 
                           JOIN Users u ON r.user_id = u.user_id 
                           WHERE r.recipe_id = ?");
    $stmt->execute([$recipe_id]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipe) {
        header("Location: ../error.php?message=Regit pull origin main --rebase
cipe not found");
        exit();
    }
} catch (PDOException $e) {
    header("Location: ../error.php?message=Database error: " . urlencode($e->getMessage()));
    exit();
}

// Fetch steps
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
    <title><?= htmlspecialchars($recipe['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css"> <!-- Adjust path to CSS file -->
</head>
<body>
    
    <div class="container mt-4">
        <h1 class="text-center"><?= htmlspecialchars($recipe['title']) ?></h1>
        <p class="text-center"><strong>By:</strong> <?= htmlspecialchars($recipe['author']) ?></p>
        
        <!-- Main Recipe Image -->
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
                    <p><strong>Step <?= $step['step_no'] ?>:</strong> <?= htmlspecialchars($step['description']) ?></p>
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