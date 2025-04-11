<?php
require_once '../config/session_config.php';
require_once '../config/db.php'; 

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Fetch user data
    $stmt = $usersDB->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Debug - remove after fixing
        error_log("User found: " . $user['username']);
        error_log("Password from form: " . $password);
        error_log("Stored hash: " . $user['password']);
        error_log("Verify result: " . (password_verify($password, $user['password']) ? "TRUE" : "FALSE"));
    } else {
        error_log("No user found with email: " . $email);
    }

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        // Redirect to homepage
        header("Location: ../index.php");
        exit();
    } else {
        $error = "Invalid email or password!";
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
</head>
<body class="bg-light">
    <?php include '../includes/navigation.php'; ?> 
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="card shadow p-4" style="width: 400px;">
            <h2 class="text-center mb-4">Login to NoiceFoodie</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <div class="text-center mt-3">
                <a href="forgot_password.php">Forgot Password?</a>
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
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Footer -->
    <?php include_once '../includes/footer.php'; ?>

</body>
</html>