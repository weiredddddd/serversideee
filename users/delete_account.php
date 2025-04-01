<?php
ob_start(); // Prevent output issues
session_start();
require '../config/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // Delete user from database
    $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = ?");
    $stmt->execute([$user_id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("User not found or already deleted.");
    }

    $pdo->commit();

    // Destroy session
    session_unset();
    session_destroy();

    // Show success message and redirect after 3 seconds
    echo "
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Account Deleted</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body class='bg-light d-flex justify-content-center align-items-center' style='height:100vh;'>
        <div class='text-center'>
            <h2 class='text-danger'>Your account has been deleted successfully.</h2>
            <p class='text-muted'>Redirecting to homepage...</p>
            <div class='spinner-border text-danger' role='status'></div>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = '../index.php';
            }, 3000); // Redirect after 3 seconds
        </script>
    </body>
    </html>";

    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Failed to delete account. Error: " . $e->getMessage();
    header("Location: ../users/profile.php");
    exit();
}
