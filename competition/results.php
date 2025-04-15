<?php
// Initialize session and include required files
require_once '../config/session_config.php';
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to view results.";
    header("Location: ../users/login.php");
    exit();
}

// Get competition ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid competition ID.";
    header("Location: competitions.php");
    exit();
}

$competition_id = $_GET['id'];

// Get competition details
$competition_sql = "SELECT * FROM competitions WHERE competition_id = ?";
$stmt = $competitionDB->prepare($competition_sql);
$stmt->execute([$competition_id]);
$competition = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$competition) {
    $_SESSION['error'] = "Competition not found.";
    header("Location: competitions.php");
    exit();
}

// Get entries with vote counts, ordered by votes (descending)
$entries_sql = "SELECT ce.entry_id, ce.recipe_id, r.title as recipe_title, u.nickname,
                COUNT(v.vote_id) as vote_count
                FROM competition_entries ce 
                JOIN recipeDB.recipes r ON ce.recipe_id = r.recipe_id 
                JOIN usersDB.users u ON ce.user_id = u.user_id 
                LEFT JOIN votes v ON ce.entry_id = v.entry_id
                WHERE ce.competition_id = ?
                GROUP BY ce.entry_id
                ORDER BY vote_count DESC";

$stmt = $competitionDB->prepare($entries_sql);
$stmt->execute([$competition_id]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total votes
$total_votes_sql = "SELECT COUNT(*) as total FROM votes v
                    JOIN competition_entries ce ON v.entry_id = ce.entry_id
                    WHERE ce.competition_id = ?";
$stmt = $competitionDB->prepare($total_votes_sql);
$stmt->execute([$competition_id]);
$total_votes_result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_votes = $total_votes_result['total'];

// Check if user participated
$participated_sql = "SELECT * FROM competition_entries WHERE competition_id = ? AND user_id = ?";
$stmt = $competitionDB->prepare($participated_sql);
$stmt->execute([$competition_id, $_SESSION['user_id']]);
$user_participated = $stmt->fetch(PDO::FETCH_ASSOC);

//Set page title
$pageTitle = "View Competition Result";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Include the competition CSS file -->
    <link rel="stylesheet" href="competition.css">
</head>
<body>
    <?php include_once '../includes/navigation.php'; ?>

    <div class="competition-content">
        <div class="container mt-4">
            <h1>Competition Results: <?= htmlspecialchars($competition['title']) ?></h1>
            
            <div class="alert alert-info mb-4">
                <strong>Status:</strong> <?= ucfirst($competition['status']) ?>
                <br>
                <strong>Total Votes Cast:</strong> <?= $total_votes ?>
            </div>
            
            <?php if ($competition['status'] != 'completed'): ?>
                <div class="alert alert-warning">
                    This competition is not yet completed. Final results will be available after <?= date('F j, Y', strtotime($competition['voting_end_date'])) ?>.
                    <br>
                    <a href="competition_details.php?id=<?= $competition_id ?>" class="alert-link">Return to competition details</a>
                </div>
            <?php endif; ?>
            
            <h2>Leaderboard</h2>
            
            <?php if (empty($entries)): ?>
                <p>No entries were submitted for this competition.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Recipe</th>
                                <th>Chef</th>
                                <th>Votes</th>
                                <th>Percentage</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $index => $entry): ?>
                                <tr <?= $index === 0 && $competition['status'] === 'completed' ? 'class="table-success"' : '' ?>>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($entry['recipe_title']) ?></td>
                                    <td><?= htmlspecialchars($entry['nickname']) ?></td>
                                    <td><?= $entry['vote_count'] ?></td>
                                    <td>
                                        <?= $total_votes > 0 ? round(($entry['vote_count'] / $total_votes) * 100, 1) : 0 ?>%
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" 
                                                style="width: <?= $total_votes > 0 ? ($entry['vote_count'] / $total_votes) * 100 : 0 ?>%" 
                                                aria-valuenow="<?= $entry['vote_count'] ?>" 
                                                aria-valuemin="0" 
                                                aria-valuemax="<?= $total_votes ?>">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="../recipes/view.php?id=<?= $entry['recipe_id'] ?>" class="btn btn-sm btn-info">View Recipe</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (!empty($entries) && $competition['status'] === 'completed'): ?>
                    <div class="alert alert-success mt-4">
                        <h4 class="alert-heading">Winner: <?= htmlspecialchars($entries[0]['recipe_title']) ?> by <?= htmlspecialchars($entries[0]['nickname']) ?></h4>
                        <p>Congratulations to our competition winner!</p>
                        <hr>
                        <p class="mb-0">Prize: <?= nl2br(htmlspecialchars($competition['prize_description'])) ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($user_participated): ?>
                <div class="mt-4">
                    <h3>Your Participation</h3>
                    <?php
                        $user_entry = null;
                        $user_rank = 0;
                        
                        foreach ($entries as $index => $entry) {
                            if ($entry['nickname'] === $_SESSION['nickname']) {
                                $user_entry = $entry;
                                $user_rank = $index + 1;
                                break;
                            }
                        }
                        
                        if ($user_entry):
                    ?>
                        <div class="alert alert-info">
                            Your recipe "<?= htmlspecialchars($user_entry['recipe_title']) ?>" 
                            ranked #<?= $user_rank ?> out of <?= count($entries) ?> entries
                            with <?= $user_entry['vote_count'] ?> votes!
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="competitions.php" class="btn btn-secondary">Back to Competitions</a>
            </div>
        </div>
    </div>
    
    <?php include_once '../includes/footer.php'; ?>
</body>
</html>