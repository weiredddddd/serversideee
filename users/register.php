<?php
// filepath: c:\xampp\htdocs\asm\users\register.php
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
    } elseif (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, underscores and hyphens";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Username must be between 3 and 20 characters";
    } else {
        // Check if username already exists
        $stmt = $usersDB->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username already taken. Please choose a different username.";
        }
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

            // Insert user into database with nickname set to username
            $stmt = $usersDB->prepare("INSERT INTO users (username, email, password, nickname) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashedPassword, $username])) {
                $success = "Account successfully registered!";
                $show_redirect_button = true; // Set flag to show button
            } else {
                $errors[] = "Error creating account!";
            }
        } catch (PDOException $e) {
            // More user-friendly error messages based on error code
            if ($e->getCode() == 23000) { // Integrity constraint violation
                if (strpos($e->getMessage(), 'username') !== false) {
                    $errors[] = "This username is already taken.";
                } else {
                    $errors[] = "This email address is already registered.";
                }
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
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" id="username" class="form-control" 
                            placeholder="Enter username" 
                            pattern="[a-zA-Z0-9_\-]+" 
                            title="Username can only contain letters, numbers, underscores and hyphens"
                            minlength="3" maxlength="20"
                            value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" 
                            required>
                    </div>
                    <div class="invalid-feedback" id="username-feedback"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" id="email" class="form-control" 
                               placeholder="Enter email" 
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                               required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" 
                               placeholder="Enter password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <small id="password-feedback" class="text-danger" style="display: none;">Password must be at least 8 characters</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                               placeholder="Confirm password" required>
                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <small id="confirm-feedback" class="text-danger" style="display: none;">Passwords do not match</small>
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
        // Client-side validation for all fields
        const form = document.getElementById('registerForm');
        const usernameInput = document.getElementById('username');
        const usernameFeedback = document.getElementById('username-feedback');
        const passwordInput = document.getElementById('password');
        const passwordFeedback = document.getElementById('password-feedback');
        const confirmInput = document.getElementById('confirm_password');
        const confirmFeedback = document.getElementById('confirm-feedback');
        
        // Username validation
        usernameInput?.addEventListener('input', function() {
            const username = this.value.trim();
            const usernamePattern = /^[a-zA-Z0-9_\-]+$/;
            
            if (username.length > 0) {
                if (!usernamePattern.test(username)) {
                    this.classList.add('is-invalid');
                    usernameFeedback.textContent = 'Username can only contain letters, numbers, underscores and hyphens';
                    usernameFeedback.style.display = 'block';
                } else if (username.length < 3 || username.length > 20) {
                    this.classList.add('is-invalid');
                    usernameFeedback.textContent = 'Username must be between 3 and 20 characters';
                    usernameFeedback.style.display = 'block';
                } else {
                    this.classList.remove('is-invalid');
                    usernameFeedback.style.display = 'none';
                }
            } else {
                this.classList.remove('is-invalid');
                usernameFeedback.style.display = 'none';
            }
        });
        
        // Password validation
        passwordInput?.addEventListener('input', function() {
            if (this.value.length > 0 && this.value.length < 8) {
                passwordFeedback.style.display = 'block';
            } else {
                passwordFeedback.style.display = 'none';
                
                // Check password match if confirm has content
                if (confirmInput.value.length > 0) {
                    if (this.value !== confirmInput.value) {
                        confirmFeedback.style.display = 'block';
                    } else {
                        confirmFeedback.style.display = 'none';
                    }
                }
            }
        });
        
        // Confirm password validation
        confirmInput?.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                confirmFeedback.style.display = 'block';
            } else {
                confirmFeedback.style.display = 'none';
            }
        });
        
        // Form submission validation
        form?.addEventListener('submit', function(e) {
            let isValid = true;
            const username = usernameInput.value.trim();
            const password = passwordInput.value;
            const confirmPassword = confirmInput.value;
            const usernamePattern = /^[a-zA-Z0-9_\-]+$/;
            
            // Username validation
            if (!usernamePattern.test(username)) {
                usernameInput.classList.add('is-invalid');
                usernameFeedback.textContent = 'Username can only contain letters, numbers, underscores and hyphens';
                usernameFeedback.style.display = 'block';
                isValid = false;
            } else if (username.length < 3 || username.length > 20) {
                usernameInput.classList.add('is-invalid');
                usernameFeedback.textContent = 'Username must be between 3 and 20 characters';
                usernameFeedback.style.display = 'block';
                isValid = false;
            }
            
            // Password validation
            if (password.length < 8) {
                passwordFeedback.style.display = 'block';
                isValid = false;
            }
            
            // Confirm password validation
            if (password !== confirmPassword) {
                confirmFeedback.style.display = 'block';
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Toggle password visibility
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            togglePasswordVisibility(passwordInput, this.querySelector('i'));
        });
        
        document.getElementById('toggleConfirmPassword')?.addEventListener('click', function() {
            togglePasswordVisibility(confirmInput, this.querySelector('i'));
        });
        
        function togglePasswordVisibility(input, icon) {
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

    <?php include_once '../includes/footer.php'; ?>
</body>
</html>