<?php
session_start();
require '../config/db.php'; 
include '../includes/navigation.php'; // Include database connection
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php"); // Redirect to login page
    exit();
}

// Display success message if set
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']); // Clear the message after displaying
}

$user_id = $_SESSION['user_id'];

// Fetch user's recipes
$stmt = $RecipeDB->prepare("SELECT * FROM Recipes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage My Recipes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../recipes/css/recipe.css">

</head>
<body>
<?php include_once '../includes/navigation.php'; ?> <!-- Include navigation bar -->

    <div class="container mt-5">
        <h2>Manage My Recipes</h2>
        <div class="row mt-4">
    <?php if (empty($recipes)): ?>
        <p class="text-muted">You haven't added any recipes yet.</p>
    <?php else: ?>
        <?php foreach ($recipes as $recipe): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
                        <p class="card-text">
                            <strong>Category:</strong> <?= htmlspecialchars($recipe['category']) ?><br>
                            <small class="text-muted">Posted on <?= date('F j, Y', strtotime($recipe['created_at'])) ?></small>
                        </p>
                        <a href="edit.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-warning btn-sm me-2">Edit</a>
                        <a href="delete.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php include_once '../includes/footer.php'; ?>
</body>
</html>