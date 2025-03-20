<?php
session_start();
require '../config/db.php'; // Adjust path to db.php

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php"); // Redirect to login page
    exit();
}

// Check if recipe ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage.php"); // Redirect to manage page
    exit();
}

$recipe_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch the recipe to edit
$stmt = $pdo->prepare("SELECT * FROM Recipes WHERE recipe_id = ? AND user_id = ?");
$stmt->execute([$recipe_id, $user_id]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

// If recipe doesn't exist or doesn't belong to the user, redirect
if (!$recipe) {
    header("Location: manage.php");
    exit();
}

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);

    // Validate inputs
    if (empty($title)) {
        $errors[] = "Title is required.";
    }
    if (empty($description)) {
        $errors[] = "Description is required.";
    }
    if (empty($category)) {
        $errors[] = "Category is required.";
    }

    // Handle image upload
    $image_url = $recipe['image_url']; // Keep the existing image by default
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = '../uploads/'; // Directory to store uploaded images
        $image_name = basename($_FILES['image']['name']);
        $target_file = $upload_dir . $image_name;

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF files are allowed.";
        }

        // Move uploaded file to the uploads directory
        if (empty($errors)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = $image_name; // Save the new file name in the database
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    // If no errors, update the recipe
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE Recipes SET title = ?, description = ?, category = ?, image_url = ? WHERE recipe_id = ? AND user_id = ?");
        $stmt->execute([$title, $description, $category, $image_url, $recipe_id, $user_id]);

        // Redirect to manage recipes page with success message
        $_SESSION['success_message'] = "Recipe updated successfully!";
        header("Location: manage.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../navigation.php'; ?> <!-- Include navigation bar -->

    <div class="container mt-5">
        <h2>Edit Recipe</h2>

        <!-- Display Errors -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Edit Recipe Form -->
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($recipe['title']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($recipe['description']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-control" required>
                    <option value="Appetizer" <?= $recipe['category'] === 'Appetizer' ? 'selected' : '' ?>>Appetizer</option>
                    <option value="Main Course" <?= $recipe['category'] === 'Main Course' ? 'selected' : '' ?>>Main Course</option>
                    <option value="Dessert" <?= $recipe['category'] === 'Dessert' ? 'selected' : '' ?>>Dessert</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Recipe Image</label>
                <input type="file" name="image" class="form-control">
                <?php if (!empty($recipe['image_url'])): ?>
                    <p class="mt-2">Current Image: <a href="../uploads/<?= htmlspecialchars($recipe['image_url']) ?>" target="_blank"><?= htmlspecialchars($recipe['image_url']) ?></a></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Update Recipe</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>