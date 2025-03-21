<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config/db.php'; // Ensure the correct path

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate empty fields
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } else {
        try {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert user into database
            $stmt = $pdo->prepare("INSERT INTO Users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashedPassword])) {
                $success = "Account successfully registered! Redirecting to login...";
                header("refresh:3;url=login.php"); // Redirect after 3 seconds
            } else {
                $error = "Error creating account!";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
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
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card p-4 shadow" style="width: 400px;">
        <h2 class="text-center">Create Account</h2>

        <!-- Success message -->
        <?php if ($success): ?>
            <div class="alert alert-success text-center"><?= $success ?></div>
        <?php endif; ?>

        <!-- Error message -->
        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <p class="text-center mt-3">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
