<?php
require_once '../config/db.php';

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// Initialize session and CSRF token
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$message_type = '';
$username = ''; // Will store the retrieved username
$show_form = true; // Control whether to display the form

// Rate limiting
if (!isset($_SESSION['username_lookup_attempts'])) {
    $_SESSION['username_lookup_attempts'] = 0;
    $_SESSION['username_first_attempt'] = time();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security verification failed");
    }
    
    // Check rate limiting (allow 5 attempts per hour)
    if ($_SESSION['username_lookup_attempts'] >= 5 && (time() - $_SESSION['username_first_attempt']) < 3600) {
        $message = "Too many attempts. Please try again later.";
        $message_type = 'error';
    } else {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address.";
            $message_type = 'error';
        } else {
            try {
                $stmt = $usersDB->prepare("SELECT username FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $_SESSION['username_lookup_attempts']++;
                
                if ($user) {
                    // Found the username - hide the form
                    $username = $user['username'];
                    $message = "We found your account!";
                    $message_type = 'success';
                    $show_form = false; // Hide the form on success
                } else {
                    // No account found - direct message
                    $message = "No account found with this email address.";
                    $message_type = 'error';
                }
            } catch (PDOException $e) {
                error_log("Username lookup error: " . $e->getMessage());
                $message = "A system error occurred. Please try again later.";
                $message_type = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find My Username | NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include_once '../includes/navigation.php'; ?>

    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        <div class="card p-4 w-100 shadow" style="max-width: 450px;">
            <h2 class="text-center mb-4">Find My Username</h2>
            
            <?php if ($message && $message_type !== 'success'): ?>
                <div class="alert alert-<?= $message_type === 'error' ? 'danger' : 'info' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($username)): ?>
                <!-- Clean success display -->
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success fa-3x"></i>
                    </div>
                    <h4 class="mb-3">Username Found!</h4>
                    <div class="bg-light p-3 rounded mb-4">
                        <p class="text-muted mb-1">Your username is:</p>
                        <h3 class="user-select-all border-bottom pb-2 mb-0"><?= htmlspecialchars($username) ?></h3>
                    </div>
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Proceed to Login
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if ($show_form): ?>
                <form method="POST" class="mb-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" 
                                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                placeholder="Enter your registered email" required>
                        </div>
                        <small class="form-text text-muted">Enter the email address associated with your account.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Find My Username</button>
                </form>
            <?php endif; ?>
            
            <div class="text-center mt-3">
                <div class="mb-2">
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
                <a href="login.php" >Back to Login</a>
            </div>
        </div>
    </div>

    <style>
        body {
            background: url('../assets/bg/login-bg.jpg') no-repeat center center fixed;
            background-size: cover;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>