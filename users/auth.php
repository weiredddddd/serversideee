<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: ./users/login.php");
    exit();
}
?>