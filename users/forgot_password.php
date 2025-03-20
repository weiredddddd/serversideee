<?php
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $reset_token = bin2hex(random_bytes(50));
        $stmt = $pdo->prepare("UPDATE Users SET reset_token = ? WHERE email = ?");
        $stmt->execute([$reset_token, $email]);

        $reset_link = "http://localhost/reset_password.php?token=" . $reset_token;
        echo "Reset link: <a href='$reset_link'>$reset_link</a>";
    } else {
        echo "Email not found!";
    }
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send Reset Link</button>
</form>
