<?php
include '../config/db.php';

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

// Verify required database columns exist
try {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM Users LIKE 'reset_token'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        die("System configuration error. Please contact administrator.");
    }
} catch (PDOException $e) {
    die("Database connection error");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security verification failed");
    }

    // Rate limiting
    if (!isset($_SESSION['reset_attempts'])) {
        $_SESSION['reset_attempts'] = 0;
        $_SESSION['first_attempt'] = time();
    }

    if ($_SESSION['reset_attempts'] > 3 && (time() - $_SESSION['first_attempt']) < 3600) {
        $message = "Too many attempts. Please try again later.";
        $message_type = 'error';
    } else {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address.";
            $message_type = 'error';
        } elseif (strlen($email) > 100) {
            $message = "Email address is too long.";
            $message_type = 'error';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $reset_token = bin2hex(random_bytes(32));
                    $expiry_time = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    $stmt = $pdo->prepare("UPDATE Users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
                    if ($stmt->execute([$reset_token, $expiry_time, $email])) {
                        $_SESSION['reset_attempts']++;
                        header("Location: reset_password.php?token=" . $reset_token);
                        exit();
                    } else {
                        $message = "System error. Please try again.";
                        $message_type = 'error';
                    }
                } else {
                    $message = "If an account exists with this email, a reset link will be sent.";
                    $message_type = 'success';
                    $_SESSION['reset_attempts']++;
                }
            } catch (PDOException $e) {
                error_log("Password reset error: " . $e->getMessage());
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
    <title>Password Reset | NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
   
    
</head>
<body>
    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        <div class="reset-card card p-4 w-100" style="max-width: 450px;">
            <h2 class="text-center mb-4">Reset Your Password</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= ($message_type == 'success') ? 'success' : 'danger' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="mb-3">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="Enter your registered email" required maxlength="100">
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
            </form>
            
            <div class="text-center">
                <a href="login.php" class="text-decoration-none">Back to Login</a>
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