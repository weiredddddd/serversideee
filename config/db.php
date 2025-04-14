<?php
$host = "localhost";
$username = "root";
$password = "";

try {
    // Connect to usersDB
    $usersDB = new PDO("mysql:host=$host;dbname=usersdb;charset=utf8", $username, $password);
    $usersDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Connect to RecipeDB
    $RecipeDB = new PDO("mysql:host=$host;dbname=recipedb;charset=utf8", $username, $password);
    $RecipeDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Connect to communityDB
    $communityDB = new PDO("mysql:host=$host;dbname=communitydb;charset=utf8", $username, $password);
    $communityDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $mealplansDB = new PDO("mysql:host=$host;dbname=mealplansDB;charset=utf8", $username, $password);
    $mealplansDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Connect to competitionDB
    $competitionDB = new PDO("mysql:host=$host;dbname=competitiondb;charset=utf8", $username, $password);
    $competitionDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Competition database connection
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
