<?php
require_once '../config/db.php';

$error = '';
$success = '';

// Check if token exists in URL
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("Location: forgot_password.php");
    exit();
}

$token = $_GET['token'];

// Verify token before showing form
try {
    // DEBUG: Log the token being checked
    error_log("Verifying token: " . $token);
    
    // Changed 'id' to 'user_id' to match your table structure
    $stmt = $usersDB->prepare("SELECT user_id, reset_token_expiry FROM Users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $error = "Invalid token. Please request a new password reset link.";
    } elseif (strtotime($user['reset_token_expiry']) < time()) {
        $error = "Expired token. Please request a new password reset link.";
        
        // DEBUG: Log expiry information
        error_log("Token expired. Current time: " . date('Y-m-d H:i:s') . 
                 ", Token expiry: " . $user['reset_token_expiry']);
    }
} catch (PDOException $e) {
    $error = "A system error occurred. Please try again later.";
    error_log("Token verification error: " . $e->getMessage());
    error_log("SQL Error Info: " . json_encode($stmt->errorInfo()));
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error)) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($password) || empty($confirm_password)) {
        $error = "Both password fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // DEBUG: Log the update attempt
            error_log("Attempting to update password for token: " . $token);
            
            $stmt = $usersDB->prepare("UPDATE Users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
            
            if ($stmt->execute([$hashed_password, $token])) {
                $success = "Password updated successfully! You can now <a href='login.php' class='alert-link'>login</a> with your new password.";
                
                // DEBUG: Log success
                error_log("Password updated successfully for token: " . $token);
            } else {
                $error = "Failed to update password. Please try again.";
                
                // DEBUG: Log failure
                error_log("Password update failed for token: " . $token);
                error_log("SQL Error Info: " . json_encode($stmt->errorInfo()));
            }
        } catch (PDOException $e) {
            $error = "A system error occurred. Please try again later.";
            error_log("Password update error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .password-card {
            max-width: 450px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .password-strength {
            height: 5px;
            margin-top: 5px;
            background-color: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s, background-color 0.3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="password-card card p-4">
                    <h2 class="text-center mb-4">Set New Password</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                            <?php if (strpos($error, 'system error') !== false): ?>
                                <div class="mt-2 small">Administrators have been notified.</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php elseif (empty($error)): ?>
                        <form method="POST" id="resetForm">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="password-strength">
                                    <div class="password-strength-bar" id="passwordStrength"></div>
                                </div>
                                <div class="form-text">Minimum 8 characters</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <div class="invalid-feedback" id="confirmError"></div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Update Password</button>
                        </form>
                    <?php endif; ?>
                    
                    <?php if ($error && (strpos($error, 'expired') !== false || strpos($error, 'Invalid') !== false)): ?>
                        <div class="text-center mt-3">
                            <a href="forgot_password.php" class="text-decoration-none">Request new reset link</a>
                        </div>
                    <?php endif; ?>
                </div>
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
    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('passwordStrength');
            let strength = 0;
            
            if (password.length >= 8) strength += 1;
            if (password.match(/[a-z]/)) strength += 1;
            if (password.match(/[A-Z]/)) strength += 1;
            if (password.match(/[0-9]/)) strength += 1;
            if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
            
            // Update strength bar
            const width = (strength / 5) * 100;
            strengthBar.style.width = width + '%';
            
            // Update color
            if (strength <= 2) {
                strengthBar.style.backgroundColor = '#dc3545';
            } else if (strength <= 4) {
                strengthBar.style.backgroundColor = '#ffc107';
            } else {
                strengthBar.style.backgroundColor = '#28a745';
            }
        });
        
        // Password confirmation validation
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const confirmError = document.getElementById('confirmError');
            
            if (password !== confirmPassword) {
                e.preventDefault();
                document.getElementById('confirm_password').classList.add('is-invalid');
                confirmError.textContent = 'Passwords do not match!';
            }
        });
    </script>
</body>

</html>