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

// Get competition ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid competition ID.";
    header("Location: competitions.php");
    exit();
}

$competition_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

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

// Check if user has already submitted an entry
$entry_sql = "SELECT ce.*, r.title as recipe_title FROM competition_entries ce 
              JOIN recipeDB.recipes r ON ce.recipe_id = r.recipe_id 
              WHERE ce.competition_id = ? AND ce.user_id = ?";
$stmt = $competitionDB->prepare($entry_sql);
$stmt->execute([$competition_id, $user_id]);
$user_entry = $stmt->fetch(PDO::FETCH_ASSOC);


// Get all entries for the competition
$entries_sql = "SELECT ce.*, r.title as recipe_title, u.username, r.image_url 
                FROM competition_entries ce 
                JOIN recipeDB.recipes r ON ce.recipe_id = r.recipe_id 
                JOIN usersDB.users u ON ce.user_id = u.user_id 
                WHERE ce.competition_id = ?";
$stmt = $competitionDB->prepare($entries_sql);
$stmt->execute([$competition_id]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Check if user has already voted
$voted_sql = "SELECT v.* FROM votes v 
              JOIN competition_entries ce ON v.entry_id = ce.entry_id 
              WHERE ce.competition_id = ? AND v.user_id = ?";
$stmt = $competitionDB->prepare($voted_sql);
$stmt->execute([$competition_id, $user_id]);
$user_voted = $stmt->fetch(PDO::FETCH_ASSOC);


// Get user's recipes for submission
if ($competition['status'] == 'active') {
    $recipes_sql = "SELECT r.recipe_id, r.title FROM recipeDB.recipes r 
                    WHERE r.user_id = ? 
                    AND r.recipe_id NOT IN (
                        SELECT recipe_id FROM competition_entries 
                        WHERE competition_id = ?
                    )";
    $stmt = $competitionDB->prepare($recipes_sql);
    $stmt->execute([$user_id, $competition_id]);
    $user_recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
}
//Set page title
$pageTitle = "Competition Details";
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
            <div class="competition-details-card">
                <h1><?= htmlspecialchars($competition['title']) ?></h1>
                
                <p><?= nl2br(htmlspecialchars($competition['description'])) ?></p>
                
                <div class="competition-timeline">
                    <div class="timeline-item">
                        <span class="timeline-date">Status:</span> 
                        <span class="badge badge-<?= $competition['status'] ?>">
                            <?= ucfirst($competition['status']) ?>
                        </span>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-date">Start Date:</span> <?= date('F j, Y', strtotime($competition['start_date'])) ?>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-date">Submission Deadline:</span> <?= date('F j, Y', strtotime($competition['end_date'])) ?>
                    </div>
                    <?php if ($competition['status'] == 'voting' || $competition['status'] == 'completed'): ?>
                    <div class="timeline-item">
                        <span class="timeline-date">Voting Ends:</span> <?= date('F j, Y', strtotime($competition['voting_end_date'])) ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="competition-rules">
                    <h6>Rules:</h6>
                    <p><?= nl2br(htmlspecialchars($competition['rules'])) ?></p>
                </div>
                
                <div class="competition-prize">
                    <h6>Prize:</h6>
                    <p><?= nl2br(htmlspecialchars($competition['prize_description'])) ?></p>
                </div>
            </div>
        
            <?php if ($competition['status'] == 'active'): ?>
                <!-- Submission Section -->
                <div class="form-section">
                    <h5>Submit Your Entry</h5>
                    
                    <?php if ($user_entry): ?>
                        <div class="alert alert-info">
                            You have already submitted: <strong><?= htmlspecialchars($user_entry['recipe_title']) ?></strong>
                            <br>Submitted on: <strong><?= date('F j, Y', strtotime($user_entry['submission_date'])) ?></strong>
                        </div>
                    <?php elseif (!empty($user_recipes)): ?>
                        <form action="submit_entry.php" method="post">
                            <input type="hidden" name="competition_id" value="<?= $competition_id ?>">
                            
                            <div class="mb-3">
                                <label for="recipe_id" class="form-label">Select one of your recipes to submit:</label>
                                <select name="recipe_id" id="recipe_id" class="form-control" required>
                                    <option value="">-- Select Recipe --</option>
                                    <?php foreach ($user_recipes as $recipe): ?>
                                        <option value="<?= $recipe['recipe_id'] ?>"><?= htmlspecialchars($recipe['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Additional Notes (optional):</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Submit Entry</button>
                        </form>
                    <?php else: ?>
                        <p>You don't have any eligible recipes to submit. <a href="../recipes/add_recipe.php">Create a new recipe</a> first!</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            
            <?php if ($competition['status'] == 'active'): ?>
                <!-- Voting Section -->
                <div class="form-section">
                    <h5>Vote for Your Favorite</h5>
                    <p>Voting ends on: <?= date('F j, Y', strtotime($competition['voting_end_date'])) ?></p>
                    
                    <?php if ($user_voted): ?>
                        <div class="alert alert-info">
                            You have already cast your vote for this competition.
                        </div>
                    <?php else: ?>
                        <form action="vote.php" method="post">
                            <input type="hidden" name="competition_id" value="<?= $competition_id ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Select your favorite entry:</label>
                                <?php foreach ($entries as $entry): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="entry_id" id="entry_<?= $entry['entry_id'] ?>" value="<?= $entry['entry_id'] ?>" required>
                                        <label class="form-check-label" for="entry_<?= $entry['entry_id'] ?>">
                                            <?= htmlspecialchars($entry['recipe_title']) ?> by <?= htmlspecialchars($entry['username']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Submit Vote</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        
            <?php if ($competition['status'] == 'completed' || $competition['status'] == 'active'): ?>
                <div class="alert alert-info">
                    You can view the current standings for the competition here. <a href="results.php?id=<?= $competition_id ?>" class="alert-link">View Results</a>
                </div>
            <?php endif; ?>
            
            <!-- Entries Section -->
            <h2>Competition Entries</h2>
            
            <?php if (empty($entries)): ?>
                <p>No entries have been submitted yet.</p>
            <?php else: ?>
                <div class="entry-grid">
                    <?php foreach ($entries as $entry): ?>
                        <div class="entry-card animate-fade-in">
                            <div class="entry-image">
                                <img src="<?= BASE_URL ?>/uploads/recipe/<?= htmlspecialchars($entry['image_url']) ?>" alt="Recipe Image">
                            </div>
                            <div class="entry-info">
                                <h5 class="entry-title"><?= htmlspecialchars($entry['recipe_title']) ?></h5>
                                <p class="entry-author">By <?= htmlspecialchars($entry['username']) ?></p>
                                <p><small class="text-muted">Submitted on: <?= date('F j, Y', strtotime($entry['submission_date'])) ?></small></p>
                                <a href="../recipes/view.php?id=<?= $entry['recipe_id'] ?>" class="btn btn-secondary">View Recipe</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    

    <?php include_once '../includes/footer.php'; ?>
</body>
</html>