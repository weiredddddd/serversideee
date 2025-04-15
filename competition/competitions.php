<?php
// Initialize session and include required files
require_once '../config/session_config.php';
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to access competitions.";
    header("Location: ../users/login.php");
    exit();
}

// Get all competitions based on status
$upcoming_sql = "SELECT * FROM competitions WHERE status = 'upcoming' ORDER BY start_date ASC";
$active_sql = "SELECT * FROM competitions WHERE status = 'active' OR status = 'voting' ORDER BY end_date ASC";
$past_sql = "SELECT * FROM competitions WHERE status = 'completed' ORDER BY end_date DESC";

// Using PDO's fetchAll
$upcoming_stmt = $competitionDB->query($upcoming_sql);
$upcoming_competitions = $upcoming_stmt->fetchAll(PDO::FETCH_ASSOC);

$active_stmt = $competitionDB->query($active_sql);
$active_competitions = $active_stmt->fetchAll(PDO::FETCH_ASSOC);

$past_stmt = $competitionDB->query($past_sql);
$past_competitions = $past_stmt->fetchAll(PDO::FETCH_ASSOC);

// Update competition statuses
$current_date = date('Y-m-d H:i:s');
$competitionDB->query("UPDATE competitions SET status = 'active' WHERE status = 'upcoming' AND start_date <= '$current_date'");
$competitionDB->query("UPDATE competitions SET status = 'voting' WHERE status = 'active' AND end_date <= '$current_date' AND voting_end_date > '$current_date'");
$competitionDB->query("UPDATE competitions SET status = 'completed' WHERE status = 'voting' AND voting_end_date <= '$current_date'");

// Set page title
$pageTitle = "Competition";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous">
    <link rel="stylesheet" href="competition.css">
</head>
<body>
    <?php include_once '../includes/navigation.php'; ?>
    
    <div class="competition-content">
        <div class="container mt-4">
            <div class="competition-hero">
                <h1>Cooking Competitions</h1>
                <p>Show off your culinary skills and compete with other chefs. Win prizes and recognition!</p>
            </div>

            <div class="competitions-section">
                <!-- Active Competitions -->
                <h2>Active Competitions</h2>
                <?php if (empty($active_competitions)): ?>
                    <p>No active competitions at the moment. Check back soon!</p>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($active_competitions as $competition): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card competition-<?= $competition['status'] ?> animate-fade-in">
                                    <div class="card-header position-relative py-2">
                                        <div class="status-badge">
                                            <span class="badge badge-<?= $competition['status'] ?>"><?= ucfirst($competition['status']) ?></span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($competition['title']) ?></h5>
                                        <p class="card-text"><?= substr(htmlspecialchars($competition['description']), 0, 100) ?>...</p>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <?php if ($competition['status'] == 'active'): ?>
                                                    Submissions until: <?= date('F j, Y', strtotime($competition['end_date'])) ?>
                                                <?php else: ?>
                                                    Voting until: <?= date('F j, Y', strtotime($competition['voting_end_date'])) ?>
                                                <?php endif; ?>
                                            </small>
                                        </p>
                                        <a href="competition_details.php?id=<?= $competition['competition_id'] ?>" class="btn btn-info">View Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Upcoming Competitions -->
            <h2>Upcoming Competitions</h2>
            <?php if (empty($upcoming_competitions)): ?>
                <p>No upcoming competitions at the moment. Check back soon!</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($upcoming_competitions as $competition): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card competition-upcoming animate-fade-in">
                                <div class="card-header position-relative py-2">
                                    <div class="status-badge">
                                        <span class="badge badge-upcoming">Upcoming</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($competition['title']) ?></h5>
                                    <p class="card-text"><?= substr(htmlspecialchars($competition['description']), 0, 100) ?>...</p>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            Starts on: <?= date('F j, Y', strtotime($competition['start_date'])) ?>
                                        </small>
                                    </p>
                                    <a href="competition_details.php?id=<?= $competition['competition_id'] ?>" class="btn btn-secondary">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Past Competitions -->
            <h2>Past Competitions</h2>
            <?php if (empty($past_competitions)): ?>
                <p>No past competitions yet.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($past_competitions as $competition): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card competition-completed animate-fade-in">
                                <div class="card-header position-relative py-2">
                                    <div class="status-badge">
                                        <span class="badge badge-completed">Completed</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($competition['title']) ?></h5>
                                    <p class="card-text"><?= substr(htmlspecialchars($competition['description']), 0, 100) ?>...</p>
                                    <a href="results.php?id=<?= $competition['competition_id'] ?>" class="btn btn-success">View Results</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
    
</body>
</html>