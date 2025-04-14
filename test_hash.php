<?php
// Simple password verification test
$password = 'yishengpass';
$alice_hash = '$2y$10$CVNeB6iRb6FZ8BiOjPsm5OE.WFdZm7dQTwYvGwwK/WSv9tWcRR7ia';

echo "<h3>Hash Verification Test</h3>";
echo "Password: $password<br>";
echo "Hash: $alice_hash<br>";
echo "Verification result: " . (password_verify($password, $alice_hash) ? "TRUE" : "FALSE") . "<br>";

// Generate a new hash for comparison
$new_hash = password_hash($password, PASSWORD_BCRYPT);
echo "<hr>New hash for 'charliepass': $new_hash<br>";
echo "Verification with new hash: " . (password_verify($password, $new_hash) ? "TRUE" : "FALSE") . "<br>";
?>