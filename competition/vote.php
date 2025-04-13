<?php
// Initialize session and include required files
require_once '../config/session_config.php';
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to vote.";
    header("Location: ../users/login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: competitions.php");
    exit();
}

// Validate inputs
if (!isset($_POST['competition_id']) || !is_numeric($_POST['competition_id']) || 
    !isset($_POST['entry_id']) || !is_numeric($_POST['entry_id'])) {
    $_SESSION['error'] = "Invalid competition or entry ID.";
    header("Location: competitions.php");
    exit();
}

$competition_id = $_POST['competition_id'];
$entry_id = $_POST['entry_id'];
$user_id = $_SESSION['user_id'];

try {
    // Verify competition exists and is in active stage
    $competition_sql = "SELECT * FROM competitions WHERE competition_id = ? AND status = 'active'";
    $stmt = $competitionDB->prepare($competition_sql);
    $stmt->execute([$competition_id]);
    $competition = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$competition) {
        $_SESSION['error'] = "Competition not found or not in active stage.";
        header("Location: competitions.php");
        exit();
    }

    // Verify entry belongs to the competition
    $entry_sql = "SELECT * FROM competition_entries WHERE entry_id = ? AND competition_id = ?";
    $stmt = $competitionDB->prepare($entry_sql);
    $stmt->execute([$entry_id, $competition_id]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entry) {
        $_SESSION['error'] = "Entry not found or not approved.";
        header("Location: competition_details.php?id=$competition_id");
        exit();
    }

    // Check if user has already voted in this competition
    $vote_sql = "SELECT v.* FROM votes v 
                 JOIN competition_entries ce ON v.entry_id = ce.entry_id 
                 WHERE ce.competition_id = ? AND v.user_id = ?";
    $stmt = $competitionDB->prepare($vote_sql);
    $stmt->execute([$competition_id, $user_id]);
    $existing_vote = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_vote) {
        $_SESSION['error'] = "You have already voted in this competition.";
        header("Location: competition_details.php?id=$competition_id");
        exit();
    }

    // Insert vote
    $insert_sql = "INSERT INTO votes (entry_id, user_id) VALUES (?, ?)";
    $stmt = $competitionDB->prepare($insert_sql);
    $stmt->execute([$entry_id, $user_id]);

    $_SESSION['success'] = "Your vote has been recorded successfully!";
    header("Location: competition_details.php?id=$competition_id");
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Error recording vote: " . $e->getMessage();
    header("Location: competition_details.php?id=$competition_id");
    exit();
}
?>