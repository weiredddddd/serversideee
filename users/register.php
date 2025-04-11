<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/db.php'; // Ensure the correct path
$success = "";
$errors = []; // Changed from single error to array of errors
$show_redirect_button = false; // New flag for showing redirect button

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) > 30) {
        $errors[] = "Username cannot exceed 30 characters";
    }

    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    } else {
        // Check if email already exists
        $stmt = $usersDB->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email is already registered. Please use a different email or login.";
        }
    }

    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }

    // Validate confirm password
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert user into database (lowercase 'users')
            $stmt = $usersDB->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashedPassword])) {
                $success = "Account successfully registered!";
                $show_redirect_button = true; // Set flag to show button
            } else {
                $errors[] = "Error creating account!";
            }
        } catch (PDOException $e) {
            // More user-friendly error messages based on error code
            if ($e->getCode() == 23000) { // Integrity constraint violation
                $errors[] = "This email address is already registered.";
            } else {
                $errors[] = "Database error: " . $e->getMessage();
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
    <title>Register - NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php include_once '../includes/navigation.php'; ?>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card p-4 shadow" style="width: 400px;">
            <h2 class="text-center">Create Account</h2>

            <!-- Success message -->
            <?php if ($success): ?>
                <div class="alert alert-success text-center">
                    <?= htmlspecialchars($success) ?>
                    <?php if ($show_redirect_button): ?>
                        <div class="mt-3">
                            <a href="login.php" class="btn btn-success">Proceed to Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Error messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!$success): // Only show form if not successful ?>
            <form method="POST" id="registerForm">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" 
                        value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter email" 
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                    <small id="password-feedback" class="text-danger" style="display: none;">Password must be at least 8 characters</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>

            <p class="text-center mt-3">
                Already have an account? <a href="login.php">Login here</a>
            </p>
            <?php endif; ?>
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
        // Client-side validation (additional layer)
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }
        });

        // Add this new code for dynamic feedback
        const passwordInput = document.getElementById('password');
        const passwordFeedback = document.getElementById('password-feedback');

        passwordInput?.addEventListener('input', function() {
            if (this.value.length > 0 && this.value.length < 8) {
                // Show warning if password is being typed but too short
                passwordFeedback.style.display = 'block';
            } else {
                // Hide warning if password is empty or long enough
                passwordFeedback.style.display = 'none';
            }
        });

        // Also check when field loses focus
        passwordInput?.addEventListener('blur', function() {
            if (this.value.length > 0 && this.value.length < 8) {
                passwordFeedback.style.display = 'block';
            } else {
                passwordFeedback.style.display = 'none';
            }
        });
    </script>

    <?php include_once '../includes/footer.php'; ?>

</body>
</html>