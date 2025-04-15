<?php
require_once '../config/session_config.php';
require_once '../config/db.php'; 

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';
$username_value = ''; // To repopulate the form after error
$login_type = 'user'; // Default login type

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $login_type = isset($_POST['login_type']) ? trim($_POST['login_type']) : 'user';
    
    // Server-side username validation
    if (empty($username)) {
        $error = "Username is required.";
    } 
    // Username format validation - alphanumeric, underscore, hyphen only
    else if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
        $error = "Username can only contain letters, numbers, underscores and hyphens.";
        $username_value = $username; 
    }
    // Length validation
    else if (strlen($username) < 3 || strlen($username) > 20) {
        $error = "Username must be between 3 and 20 characters.";
        $username_value = $username; 
    }
    else {
        // First check if the user exists and verify password
        $stmt = $usersDB->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nickname'] = $user['nickname'] ?? $user['username']; // Store nickname in session
            $_SESSION['avatar'] = $user['avatar'] ?? 0;
            $_SESSION['is_admin'] = $user['is_admin'] ?? 0; // Add this line

            // Redirect to homepage
            header("Location: ../index.php");
            exit();
            // Check if user is admin
            $is_admin = $user['is_admin'] == 1;

            // Proceed with login process based on login type
            if ($login_type == 'admin') {
                // Admin login logic - check if the user is actually an admin
                if ($is_admin) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nickname'] = $user['nickname'] ?? $user['username']; // Store nickname in session
                    $_SESSION['avatar'] = $user['avatar'] ?? 0;
                    $_SESSION['is_admin'] = true; // Mark as admin in session

                    // Redirect to admin dashboard
                    header("Location: ../admin/index.php");
                    exit();
                } else {
                    $error = "You don't have admin privileges!";
                    $username_value = $username;
                }
            } else {
                // Regular user login - only non-admin users can log in as regular users
                if (!$is_admin) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nickname'] = $user['nickname'] ?? $user['username'];
                    $_SESSION['avatar'] = $user['avatar'] ?? 0;
                    $_SESSION['is_admin'] = false;

                    // Redirect to homepage
                    header("Location: ../index.php");
                    exit();
                } else {
                    $error = "Admin users must login using the Admin Panel option.";
                    $username_value = $username;
                }
            }
        } else {
            $error = "Invalid username or password!";
            $username_value = $username; // Save for form repopulation
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php include '../includes/navigation.php'; ?> 
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="card shadow p-4" style="width: 400px;">
            <h2 class="text-center mb-4">Login to NoiceFoodie</h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" id="loginForm" novalidate>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" id="username" class="form-control" 
                               placeholder="Enter your username" required pattern="[a-zA-Z0-9_\-]+"
                               title="Username can only contain letters, numbers, underscores and hyphens"
                               minlength="3" maxlength="20"
                               value="<?php echo htmlspecialchars($username_value); ?>">
                        <div class="invalid-feedback">
                            Username can only contain letters, numbers, underscores and hyphens (3-20 characters).
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" 
                               placeholder="Enter your password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label d-block">Login as:</label>
                    <div class="login-type-selector d-flex justify-content-center mb-3">
                        <div class="form-check form-check-inline login-option">
                            <input class="form-check-input" type="radio" name="login_type" id="userLogin" value="user" checked>
                            <label class="form-check-label login-label" for="userLogin">
                                <i class="fas fa-user login-icon"></i>
                                <span>User</span>
                            </label>
                        </div>
                        <div class="form-check form-check-inline login-option">
                            <input class="form-check-input" type="radio" name="login_type" id="adminLogin" value="admin">
                            <label class="form-check-label login-label" for="adminLogin">
                                <i class="fas fa-user-shield login-icon"></i>
                                <span>Admin</span>
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <div class="text-center mt-3">
                <a href="forgot_password.php">Forgot Password?</a>
                <a href="forgot_username.php" class="ms-3">Forgot Username?</a>
            </div>
                        
            <div class="text-center mt-2">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>
    <style>
        body {
            background: url('../assets/bg/login-bg.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        
        /* Login type selector styles */
        .login-type-selector {
            gap: 20px;
        }
        
        .login-option {
            text-align: center;
            margin: 0;
        }
        
        .login-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        
        .login-label {
            display: block;
            padding: 15px 10px;
            background-color: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            width: 130px;
        }
        
        .login-icon {
            display: block;
            font-size: 24px;
            margin-bottom: 5px;
            color: #6c757d;
        }
        
        .login-option input[type="radio"]:checked + .login-label {
            border-color: #0d6efd;
            background-color: #e8f0fe;
        }
        
        .login-option input[type="radio"]:checked + .login-label .login-icon {
            color: #0d6efd;
        }
    </style>
    <script>
        // Client-side validation
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            const usernameInput = document.getElementById('username');
            const username = usernameInput.value.trim();
            let isValid = true;
            
            // Reset previous validation state
            usernameInput.classList.remove('is-invalid');
            
            // Check username format
            const usernamePattern = /^[a-zA-Z0-9_\-]+$/;
            if (!usernamePattern.test(username)) {
                usernameInput.classList.add('is-invalid');
                isValid = false;
            }
            
            // Check username length
            if (username.length < 3 || username.length > 20) {
                usernameInput.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
        
        // Password visibility toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>

    <!-- Footer -->
    <?php include_once '../includes/footer.php'; ?>
</body>
</html>