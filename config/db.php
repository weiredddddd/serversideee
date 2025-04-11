<?php
$host = "localhost";
$username = "root";
$password = "";

try {
    // Connect to usersDB
    $usersDB = new PDO("mysql:host=$host;dbname=usersDB;charset=utf8", $username, $password);
    $usersDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Connect to RecipeDB
    $RecipeDB = new PDO("mysql:host=$host;dbname=RecipeDB;charset=utf8", $username, $password);
    $RecipeDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Connect to communityDB
    $communityDB = new PDO("mysql:host=$host;dbname=communityDB;charset=utf8", $username, $password);
    $communityDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
