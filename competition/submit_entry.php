<?php
// Initialize session and include required files
require_once '../config/session_config.php';
require_once '../config/db.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to submit entries.";
    header("Location: ../users/login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: competitions.php");
    exit();
}


// Validate inputs
if (!isset($_POST['competition_id']) || !is_numeric($_POST['competition_id']) || 
    !isset($_POST['recipe_id']) || !is_numeric($_POST['recipe_id'])) {
    $_SESSION['error'] = "Invalid competition or recipe ID.";
    header("Location: competitions.php");
    exit();
}

$competition_id = $_POST['competition_id'];
$recipe_id = $_POST['recipe_id'];
$user_id = $_SESSION['user_id'];
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

try {
    // Verify competition exists and is active
    $competition_sql = "SELECT * FROM competitions WHERE competition_id = ? AND status = 'active'";
    $stmt = $competitionDB->prepare($competition_sql);
    $stmt->execute([$competition_id]);
    $competition = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$competition) {
        $_SESSION['error'] = "Competition not found or not accepting entries.";
        header("Location: competitions.php");
        exit();
    }

    // Verify recipe belongs to user
    $recipe_sql = "SELECT * FROM recipeDB.recipes WHERE recipe_id = ? AND user_id = ?";
    $stmt = $competitionDB->prepare($recipe_sql);
    $stmt->execute([$recipe_id, $user_id]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipe) {
        $_SESSION['error'] = "Recipe not found or doesn't belong to you.";
        header("Location: competition_details.php?id=$competition_id");
        exit();
    }

    // Check if user already has an entry for this competition
    $entry_sql = "SELECT * FROM competition_entries WHERE competition_id = ? AND user_id = ?";
    $stmt = $competitionDB->prepare($entry_sql);
    $stmt->execute([$competition_id, $user_id]);
    $existing_entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_entry) {
        $_SESSION['error'] = "You have already submitted an entry for this competition.";
        header("Location: competition_details.php?id=$competition_id");
        exit();
    }

    // Insert entry
    $insert_sql = "INSERT INTO competition_entries (competition_id, recipe_id, user_id, notes) 
                   VALUES (?, ?, ?, ?)";
    $stmt = $competitionDB->prepare($insert_sql);
    $stmt->execute([$competition_id, $recipe_id, $user_id, $notes]);

    $_SESSION['success'] = "Your entry has been submitted successfully!";
    header("Location: competition_details.php?id=$competition_id");
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Error submitting entry: " . $e->getMessage();
    header("Location: competition_details.php?id=$competition_id");
    exit();
}
?>
