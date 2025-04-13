<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $db = $mealplansDB;
    if(empty($data['recipe_id']) && empty($data['custom_meal_name'])) {
        http_response_code(400);
        die(json_encode(['error' => 'Recipe or Custom Meal required']));
    }
    if(!empty($data['meal_plan_id'])) {
        // Update existing meal
        $stmt = $db->prepare("UPDATE MealPlans SET 
                            date = :date,
                            time_slot = :time_slot,
                            recipe_id = :recipe_id,
                            custom_meal_name = :custom_meal_name,
                            custom_meal_description = :custom_meal_description
                            WHERE meal_plan_id = :id AND user_id = :user_id");
        $stmt->execute([
            ':date' => $data['date'],
            ':time_slot' => $data['time_slot'],
            ':recipe_id' => $data['recipe_id'] ?: null,
            ':custom_meal_name' => $data['custom_meal_name'] ?: null,
            ':custom_meal_description' => $data['custom_meal_description'] ?: null,
            ':id' => $data['meal_plan_id'],
            ':user_id' => $_SESSION['user_id']
        ]);
    } else {
        // Create new meal
        $stmt = $db->prepare("INSERT INTO MealPlans 
                            (user_id, date, time_slot, recipe_id, custom_meal_name, custom_meal_description)
                            VALUES (:user_id, :date, :time_slot, :recipe_id, :custom_meal_name, :custom_meal_description)");
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':date' => $data['date'],
            ':time_slot' => $data['time_slot'],
            ':recipe_id' => $data['recipe_id'] ?: null,
            ':custom_meal_name' => $data['custom_meal_name'] ?: null,
            ':custom_meal_description' => $data['custom_meal_description'] ?: null
        ]);
    }
    
    echo json_encode(['success' => true]);
    
} catch(PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
}