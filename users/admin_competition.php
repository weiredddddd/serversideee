<?php
require_once '../config/session_config.php';
require_once '../config/db.php';

// Admin check
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== '1') {
    header("Location: login.php");
    exit();
}

try {
    // Fetch all competitions with entry count
    $query = "
        SELECT c.competition_id, c.title, c.start_date, c.end_date, c.voting_end_date, 
               c.status, COUNT(ce.entry_id) AS entry_count
        FROM competitions c
        LEFT JOIN competition_entries ce ON c.competition_id = ce.competition_id
        GROUP BY c.competition_id
        ORDER BY c.created_at DESC
    ";
    $stmt = $competitionDB->prepare($query);
    $stmt->execute();
    $competitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching competitions: " . $e->getMessage();
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        try {
            // Delete related votes
            $vote_query = "
                DELETE v FROM votes v
                JOIN competition_entries ce ON v.entry_id = ce.entry_id
                WHERE ce.competition_id = :id
            ";
            $vote_stmt = $competitionDB->prepare($vote_query);
            $vote_stmt->execute(['id' => $delete_id]);

            // Delete entries
            $entry_query = "DELETE FROM competition_entries WHERE competition_id = :id";
            $entry_stmt = $competitionDB->prepare($entry_query);
            $entry_stmt->execute(['id' => $delete_id]);

            // Delete competition
            $comp_query = "DELETE FROM competitions WHERE competition_id = :id";
            $comp_stmt = $competitionDB->prepare($comp_query);
            $comp_stmt->execute(['id' => $delete_id]);

            $_SESSION['success'] = "Competition deleted successfully.";
            header("Location: admin_competition.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error deleting competition: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Competitions - NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table th, .table td { vertical-align: middle; }
        .badge { font-size: 0.9em; }
    </style>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4">Manage Competitions</h2>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Create New Competition Button -->
        <div class="mb-3">
            <a href="../competition/create_competition.php" class="btn btn-primary">Create New Competition</a>
        </div>

        <!-- Competitions Table -->
        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Voting Ends</th>
                            <th>Entries</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($competitions)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No competitions found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($competitions as $comp): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($comp['title']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php
                                            echo $comp['status'] === 'active' ? 'success' :
                                                 ($comp['status'] === 'upcoming' ? 'warning' : 'secondary');
                                        ?>">
                                            <?php echo ucfirst($comp['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($comp['start_date'])); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($comp['end_date'])); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($comp['voting_end_date'])); ?></td>
                                    <td><?php echo $comp['entry_count']; ?></td>
                                    <td>
                                        <a href="admin_view_entries.php?competition_id=<?php echo $comp['competition_id']; ?>" 
                                           class="btn btn-sm btn-info">View Entries</a>
                                        <a href="admin_edit_competition.php?competition_id=<?php echo $comp['competition_id']; ?>" 
                                           class="btn btn-sm btn-warning">Edit</a>
                                        <form action="admin_competition.php" method="POST" 
                                              style="display:inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this competition?');">
                                            <input type="hidden" name="delete_id" value="<?php echo $comp['competition_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
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