<?php
require_once '../../config/session_config.php';
require_once '../../config/db.php';



header('Content-Type: application/json');

// Check if recipe_id is provided
if (!isset($_POST['recipe_id']) || !is_numeric($_POST['recipe_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid recipe ID']);
    exit;
}

$recipe_id = (int)$_POST['recipe_id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

try {
    // Check if the recipe exists
    $recipe_check_stmt = $RecipeDB->prepare("SELECT user_id FROM Recipes WHERE recipe_id = ?");
    $recipe_check_stmt->execute([$recipe_id]);
    $recipe_data = $recipe_check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipe_data) {
        echo json_encode(['success' => false, 'error' => 'Recipe not found']);
        exit;
    }

    $author_id = $recipe_data['user_id'];

    // Don't count views from the author or non-logged-in users
    if ($user_id && $user_id != $author_id) {
        // Check if this user has already viewed this recipe in this session
        $session_key = 'viewed_recipe_' . $recipe_id;
        if (!isset($_SESSION[$session_key])) {
            // Mark this recipe as viewed in the session
            $_SESSION[$session_key] = true;

            // Increment view count in the database
            $update_stmt = $RecipeDB->prepare("
                UPDATE Recipes 
                SET view_count = COALESCE(view_count, 0) + 1 
                WHERE recipe_id = ?
            ");
            $update_stmt->execute([$recipe_id]);

            // Get the updated view count
            $count_stmt = $RecipeDB->prepare("SELECT COALESCE(view_count, 0) AS view_count FROM Recipes WHERE recipe_id = ?");
            $count_stmt->execute([$recipe_id]);
            $count_data = $count_stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'view_count' => $count_data['view_count']
            ]);
        } else {
            // User already viewed this recipe in this session
            echo json_encode(['success' => false, 'error' => 'Already viewed', 'counted' => false]);
        }
    } else {
        // Author viewing own recipe or non-logged-in user
        echo json_encode(['success' => false, 'error' => 'View not counted', 'counted' => false]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    error_log("Recipe view tracking error: " . $e->getMessage());
}
?>