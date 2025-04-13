<?php
require_once '../../config/session_config.php';
require_once '../../config/db.php';

// Check if user ID is provided
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
    exit;
}

$user_id = (int)$_GET['user_id'];

try {
    // Fetch the user's avatar
    $stmt = $communityDB->prepare("SELECT avatar FROM usersDB.users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'avatar' => $user['avatar']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'User not found']);
    }
    
} catch (PDOException $e) {
    error_log("Error fetching user avatar: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
