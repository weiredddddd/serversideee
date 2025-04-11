<?php
require_once '../config/session_config.php'; 

// Save username for message (if available)
$username = $_SESSION['username'] ?? 'User';

// Clear all user data
session_destroy();

// Start a fresh session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// CHANGE THIS LINE - use 'message' instead of 'logout_message'
$_SESSION['message'] = "You have been logged out successfully. Come back soon, $username!";
$_SESSION['message_type'] = "success";

// Prevent back button from accessing the cached page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Redirect to home page
header("Location: ../index.php");
exit();
?>