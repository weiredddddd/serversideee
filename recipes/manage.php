<?php
session_start();
require '../config/db.php'; // Adjust path based on your structure

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's recipes
$stmt = $pdo->prepare("SELECT * FROM Recipes WHERE user_id = ?");
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
</head>
<body>
<?php include '../navigation.php'; ?>

<div class="container mt-5">
    <h2>Manage My Recipes</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recipes as $recipe): ?>
            <tr>
                <td><?= htmlspecialchars($recipe['title']) ?></td>
                <td><?= htmlspecialchars($recipe['category']) ?></td>
                <td><?= htmlspecialchars($recipe['created_at']) ?></td>
                <td>
                    <a href="edit_recipe.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="delete_recipe.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
