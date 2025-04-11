<?php
require_once '../config/session_config.php';
require_once '../config/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // First, check if the user exists
    $check = $usersDB->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $check->execute([$user_id]);
    
    if ($check->rowCount() === 0) {
        throw new Exception("User account not found.");
    }

    $usersDB->beginTransaction();

    // Delete user's related data first
    $stmt = $RecipeDB->prepare("DELETE FROM Recipes WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Delete the user from database
    $stmt = $usersDB->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);

    $usersDB->commit();
    
    // Save message for after session destruction
    $message = "Your account has been successfully deleted.";
    
    // Destroy the session
    session_unset();
    session_destroy();
    
    // Start new session for flash message
    session_start();
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = "success";
    
    // Redirect to homepage
    header("Location: ../index.php");
    exit();
    
} catch (Exception $e) {
    if ($usersDB->inTransaction()) {
        $usersDB->rollBack();
    }
    
    $_SESSION['error_message'] = "Failed to delete account: " . $e->getMessage();
    header("Location: profile.php");
    exit();
}
?>