<?php
// Initialize session and include required files
require_once '../config/session_config.php';
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to create competitions.";
    header("Location: ../users/login.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate inputs
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $voting_end_date = trim($_POST['voting_end_date']);
    $rules = trim($_POST['rules']);
    $prize_description = trim($_POST['prize_description']);
    $user_id = $_SESSION['user_id'];
    
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
    
    // If no errors, insert into database
    if (empty($errors)) {
        // Determine initial status
        $current_date = date('Y-m-d H:i:s');
        if (strtotime($start_date) > strtotime($current_date)) {
            $status = 'upcoming';
        } elseif (strtotime($end_date) > strtotime($current_date)) {
            $status = 'active';
        } elseif (strtotime($voting_end_date) > strtotime($current_date)) {
            $status = 'voting';
        } else {
            $status = 'completed';
        }
        
        try {
            $sql = "INSERT INTO competitions 
                    (title, description, start_date, end_date, voting_end_date, rules, prize_description, status, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $competitionDB->prepare($sql);
            
            $stmt->execute([
                $title, 
                $description, 
                $start_date, 
                $end_date, 
                $voting_end_date, 
                $rules, 
                $prize_description, 
                $status, 
                $user_id
            ]);
        
            $_SESSION['success'] = "Competition created successfully!";
            header("Location: competitions.php");
            exit();
        
        } catch (PDOException $e) {
            $errors[] = "Error creating competition: " . $e->getMessage();
        }
        
    }
}
//Set page title
$pageTitle = "Create Competition";
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
    <div class="container mt-4">
        <h1>Create New Competition</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group mb-3">
                <label for="title">Competition Title:</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= isset($title) ? htmlspecialchars($title) : '' ?>" required>
            </div>
            
            <div class="form-group mb-3">
                <label for="description">Description:</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?= isset($description) ? htmlspecialchars($description) : '' ?></textarea>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="start_date">Start Date:</label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="<?= isset($start_date) ? htmlspecialchars($start_date) : '' ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="end_date">Submission End Date:</label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="<?= isset($end_date) ? htmlspecialchars($end_date) : '' ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="voting_end_date">Voting End Date:</label>
                        <input type="datetime-local" class="form-control" id="voting_end_date" name="voting_end_date" value="<?= isset($voting_end_date) ? htmlspecialchars($voting_end_date) : '' ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group mb-3">
                <label for="rules">Competition Rules:</label>
                <textarea class="form-control" id="rules" name="rules" rows="4"><?= isset($rules) ? htmlspecialchars($rules) : '' ?></textarea>
            </div>
            
            <div class="form-group mb-3">
                <label for="prize_description">Prize Description:</label>
                <textarea class="form-control" id="prize_description" name="prize_description" rows="2"><?= isset($prize_description) ? htmlspecialchars($prize_description) : '' ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Create Competition</button>
            <a href="competitions.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <?php include_once '../includes/footer.php'; ?>
</body>
</html>