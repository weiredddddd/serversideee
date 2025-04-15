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
    $_SESSION['error'] = "Invalid competition ID.";
    header("Location: admin_competition.php");
    exit();
}

try {
    // Fetch competition details
    $query = "
        SELECT competition_id, title, description, start_date, end_date, 
               voting_end_date, rules, prize_description, status
        FROM competitions 
        WHERE competition_id = :id
    ";
    $stmt = $competitionDB->prepare($query);
    $stmt->execute(['id' => $competition_id]);
    $competition = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$competition) {
        $_SESSION['error'] = "Competition not found.";
        header("Location: admin_competition.php");
        exit();
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
        $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
        $voting_end_date = filter_input(INPUT_POST, 'voting_end_date', FILTER_SANITIZE_STRING);
        $rules = filter_input(INPUT_POST, 'rules', FILTER_SANITIZE_STRING);
        $prize_description = filter_input(INPUT_POST, 'prize_description', FILTER_SANITIZE_STRING);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

        $errors = [];

        if (empty($title)) {
            $errors[] = "Title is required.";
        }
        
        if (empty($description)) {
            $errors[] = "Description is required.";
        }
        
        if (empty($start_date)) {
            $errors[] = "Start date is required.";
        }
        
        if (empty($end_date)) {
            $errors[] = "End date is required.";
        }
        
        if (empty($voting_end_date)) {
            $errors[] = "Voting end date is required.";
        }
        
        // Check if dates are in the correct order
        if (strtotime($start_date) >= strtotime($end_date)) {
            $errors[] = "Start date must be before end date.";
        }
        
        if (strtotime($end_date) >= strtotime($voting_end_date)) {
            $errors[] = "End date must be before voting end date.";
        }

        // Basic validation
        if (!empty($errors)) {
            $errors[] = "Please fill in all required fields.";
        } else {
            $update_query = "
                UPDATE competitions 
                SET title = :title, description = :description, start_date = :start_date, 
                    end_date = :end_date, voting_end_date = :voting_end_date, 
                    rules = :rules, prize_description = :prize_description, status = :status
                WHERE competition_id = :id
            ";
            $update_stmt = $competitionDB->prepare($update_query);
            $update_stmt->execute([
                'title' => $title,
                'description' => $description,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'voting_end_date' => $voting_end_date,
                'rules' => $rules,
                'prize_description' => $prize_description,
                'status' => $status,
                'id' => $competition_id
            ]);

            $_SESSION['success'] = "Competition updated successfully.";
            header("Location: admin_competition.php");
            exit();
        }
    }
} catch (PDOException $e) {
    $errors[] = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Competition - NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-label { font-weight: 500; }
    </style>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4">Edit Competition</h2>

        <!-- Error Message -->
        <?php if (!empty($errors) && is_array($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['errors']) && is_array($_SESSION['errors'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="mb-3">
            <a href="admin_competition.php" class="btn btn-secondary">Back to Manage Competitions</a>
        </div>

        <!-- Edit Form -->
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($competition['title']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($competition['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date" 
                               value="<?php echo date('Y-m-d\TH:i', strtotime($competition['start_date'])); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date" 
                               value="<?php echo date('Y-m-d\TH:i', strtotime($competition['end_date'])); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="voting_end_date" class="form-label">Voting End Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="voting_end_date" name="voting_end_date" 
                               value="<?php echo date('Y-m-d\TH:i', strtotime($competition['voting_end_date'])); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="rules" class="form-label">Rules</label>
                        <textarea class="form-control" id="rules" name="rules" rows="4"><?php echo htmlspecialchars($competition['rules'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="prize_description" class="form-label">Prize Description</label>
                        <textarea class="form-control" id="prize_description" name="prize_description" rows="4"><?php echo htmlspecialchars($competition['prize_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="upcoming" <?php echo $competition['status'] === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="active" <?php echo $competition['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="completed" <?php echo $competition['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>