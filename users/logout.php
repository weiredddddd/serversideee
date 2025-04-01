<?php
session_start();
session_unset();  // Unset all session variables
session_destroy(); // Destroy the session

// Prevent back button from accessing the cached page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
// Store logout success message in session
session_start(); // Start a new session to store the message
$_SESSION['message'] = "You have successfully logged out.";


// Redirect to login page
header("Location: ../index.php");
exit();
?>
