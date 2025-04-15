<?php
require_once '../config/session_config.php';
require_once '../config/db.php';

// Admin check
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    header("Location: login.php");
    exit();
}

// Validate competition_id
$competition_id = filter_input(INPUT_GET, 'competition_id', FILTER_VALIDATE_INT);
if (!$competition_id) {
    header("Location: admin_competition.php");
    exit();
}

try {
    // Fetch competition details
    $comp_query = "SELECT title FROM competitions WHERE competition_id = :id";
    $comp_stmt = $competitionDB->prepare($comp_query);
    $comp_stmt->execute(['id' => $competition_id]);
    $competition = $comp_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$competition) {
        $_SESSION['error'] = "Competition not found.";
        header("Location: admin_competition.php");
        exit();
    }

    // Fetch entries with recipe and user details
    $query = "
        SELECT ce.entry_id, ce.notes, ce.submission_date, 
               r.title AS recipe_title, u.username, 
               COUNT(v.vote_id) AS vote_count
        FROM competition_entries ce
        JOIN RecipeDB.Recipes r ON ce.recipe_id = r.recipe_id
        JOIN usersDB.users u ON ce.user_id = u.user_id
        LEFT JOIN votes v ON ce.entry_id = v.entry_id
        WHERE ce.competition_id = :id
        GROUP BY ce.entry_id
        ORDER BY ce.submission_date DESC
    ";
    $stmt = $competitionDB->prepare($query);
    $stmt->execute(['id' => $competition_id]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching entries: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Entries - <?php echo htmlspecialchars($competition['title']); ?> - NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table th, .table td { vertical-align: middle; }
    </style>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4">Entries for <?php echo htmlspecialchars($competition['title']); ?></h2>

        <!-- Error Message -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="mb-3">
            <a href="admin_competition.php" class="btn btn-secondary">Back to Manage Competitions</a>
        </div>

        <!-- Entries Table -->
        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Recipe</th>
                            <th>Submitted By</th>
                            <th>Notes</th>
                            <th>Submission Date</th>
                            <th>Votes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($entries)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No entries found for this competition.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($entries as $entry): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($entry['recipe_title']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['username']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['notes'] ?? ''); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($entry['submission_date'])); ?></td>
                                    <td><?php echo $entry['vote_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>