<?php
// Only start the session if one isn't already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get the current user ID safely
function get_current_user_id() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Set a flash message (for one-time display)
function set_flash_message($message) {
    $_SESSION['flash_message'] = $message;
}

// Get and clear flash message
function get_flash_message() {
    $message = isset($_SESSION['flash_message']) ? $_SESSION['flash_message'] : '';
    unset($_SESSION['flash_message']);
    return $message;
}
?>