<?php
include 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("UPDATE Users SET password = ?, reset_token = NULL WHERE reset_token = ?");
    if ($stmt->execute([$new_password, $token])) {
        echo "Password updated! <a href='login.php'>Login</a>";
    } else {
        echo "Invalid token!";
    }
}
?>

<form method="POST">
    <input type="hidden" name="token" value="<?= $_GET['token'] ?>">
    <input type="password" name="password" placeholder="New Password" required>
    <button type="submit">Reset Password</button>
</form>
