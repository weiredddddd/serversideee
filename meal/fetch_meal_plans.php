<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

try {
    $db = $mealplansDB;

    $query = "SELECT mp.*, r.title AS recipe_title, r.image_url, n.calories, n.fat, n.carbs, n.protein
              FROM MealPlans mp 
              LEFT JOIN RecipeDB.Recipes r ON mp.recipe_id = r.recipe_id 
              LEFT JOIN RecipeDB.Nutrition n ON r.recipe_id = n.recipe_id
              WHERE mp.user_id = :user_id";

    $params = [];
    if (isset($_GET['start']) && isset($_GET['end'])) {
        $query .= " AND mp.date BETWEEN :start AND :end";
        $params[':start'] = $_GET['start'];
        $params[':end'] = $_GET['end'];
    }

    if (isset($_GET['meal_id'])) {
        $query .= " AND mp.meal_plan_id = :meal_id";
        $params[':meal_id'] = $_GET['meal_id'];
    }

    $stmt = $db->prepare($query);
    $stmt->execute(array_merge([':user_id' => $_SESSION['user_id']], $params));

    if (isset($_GET['meal_id'])) {
        $meal = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($meal);
    } else {
        $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($meals);
    }

} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
}
?>