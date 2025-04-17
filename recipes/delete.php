<?php
session_start();
require '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

// Check if recipe ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage.php");
    exit();
}

$recipe_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch the recipe to ensure it belongs to the user
$stmt = $RecipeDB->prepare("SELECT * FROM Recipes WHERE recipe_id = ? AND user_id = ?");
$stmt->execute([$recipe_id, $user_id]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

// If recipe doesn't exist or doesn't belong to the user, redirect
if (!$recipe) {
    header("Location: manage.php");
    exit();
}

// Delete the recipe
$stmt = $RecipeDB->prepare("DELETE FROM Recipes WHERE recipe_id = ? AND user_id = ?");
$stmt->execute([$recipe_id, $user_id]);

// Redirect to manage recipes page with success message
$_SESSION['success_message'] = "Recipe deleted successfully!";
header("Location: manage.php");
exit();
?>