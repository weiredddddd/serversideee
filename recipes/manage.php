<?php
session_start();
require '../config/db.php'; 

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
$stmt = $pdo->prepare("SELECT * FROM Recipes WHERE user_id = ? ORDER BY created_at DESC");
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
    <?php include '../navigation.php'; ?> <!-- Include navigation bar -->

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
                <?php if (empty($recipes)): ?>
                    <tr>
                        <td colspan="4" class="text-center">No recipes found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recipes as $recipe): ?>
                        <tr>
                            <td><?= htmlspecialchars($recipe['title']) ?></td>
                            <td><?= htmlspecialchars($recipe['category']) ?></td>
                            <td><?= htmlspecialchars($recipe['created_at']) ?></td>
                            <td>
                                <a href="edit.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>