<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

// Get data from request body
$data = json_decode(file_get_contents('php://input'), true);
$meal_id = $data['meal_id'] ?? null;

if (!$meal_id) {
    http_response_code(400);
    die(json_encode(['error' => 'Meal plan ID is required']));
}

try {
    $db = $MealPlansDB;
    $stmt = $db->prepare("DELETE FROM MealPlans 
                        WHERE meal_plan_id = :meal_id 
                        AND user_id = :user_id");
    $stmt->execute([
        ':meal_id' => $meal_id,
        ':user_id' => $_SESSION['user_id']
    ]);

    echo json_encode(['success' => $stmt->rowCount() > 0]);
    
} catch(PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
}