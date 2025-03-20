<?php
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors to the browser
ini_set('display_startup_errors', 1); // Display startup errors
session_start(); // Start session to check if user is logged in

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php"); // Adjust path to login.php
    exit();
}

include '../config/db.php'; // Include database connection (adjust path as needed)
$errors = []; // Array to store errors

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $user_id = $_SESSION['user_id'];

    // Validate required fields
    if (empty($title)) {
        $errors[] = "Recipe title is required.";
    }
    if (empty($description)) {
        $errors[] = "Recipe description is required.";
    }
    if (empty($category)) {
        $errors[] = "Recipe category is required.";
    }

    // Handle main recipe image upload
    $image_url = "";
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = "../uploads/"; // Adjust path to uploads folder
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Create the directory if it doesn't exist
        }

        // Generate a unique filename
        $image_url = uniqid() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $upload_dir . $image_url;

        // Debugging: Check file upload errors
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error: " . $_FILES['image']['error'];
        } else {
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = mime_content_type($_FILES['image']['tmp_name']);
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "Only JPG, PNG, and GIF files are allowed for the main image.";
            }

            // Validate file extension
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $errors[] = "Only JPG, PNG, and GIF files are allowed for the main image.";
            }

            // Validate file size
            $max_file_size = 10 * 1024 * 1024; // 10 MB
            if ($_FILES['image']['size'] > $max_file_size) {
                $errors[] = "File size exceeds the maximum allowed size of 10 MB.";
            }

            // Move uploaded file if no errors
            if (empty($errors)) {
                echo "Target file path: " . $target_file; // Debugging output
                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $errors[] = "Failed to upload the main image. Check file permissions or path.";
                }
            }
        }
    } else {
        $errors[] = "Main recipe image is required.";
    }

    // Insert recipe into database if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO Recipes (title, description, category, image_url, user_id) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $category, $image_url, $user_id]);

        $recipe_id = $pdo->lastInsertId();

        // Insert steps with images
        foreach ($_POST['steps'] as $index => $step_desc) {
            if (!empty($step_desc)) {
                $step_image_url = "";
                if (!empty($_FILES['step_images']['name'][$index])) {
                    $upload_dir = "../uploads/"; // Adjust path to uploads folder
                    $step_image_url = uniqid() . "_" . basename($_FILES["step_images"]["name"][$index]);
                    $target_file = $upload_dir . $step_image_url;

                    // Validate file type for step images
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $file_type = mime_content_type($_FILES['step_images']['tmp_name'][$index]);
                    if (!in_array($file_type, $allowed_types)) {
                        $errors[] = "Only JPG, PNG, and GIF files are allowed for step images.";
                    }

                    // Move uploaded file if no errors
                    if (empty($errors)) {
                        if (!move_uploaded_file($_FILES["step_images"]["tmp_name"][$index], $target_file)) {
                            $errors[] = "Failed to upload step image for step " . ($index + 1) . ".";
                        }
                    }
                }

                // Insert step into database
                $step_stmt = $pdo->prepare("INSERT INTO Steps (recipe_id, step_no, description, image_url) 
                                            VALUES (?, ?, ?, ?)");
                $step_stmt->execute([$recipe_id, $index + 1, $step_desc, $step_image_url]);
            }
        }

        // Redirect to the new recipe page if no errors
        if (empty($errors)) {
            header("Location: view.php?id=" . $recipe_id); // Redirect to view.php in the same folder
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Recipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css"> <!-- Adjust path to CSS file -->
</head>
<body>
    <!-- Profile Dropdown Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">NoiceFoodie</a>
            <div class="ms-auto">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= htmlspecialchars($_SESSION['username']) ?> <!-- Display username -->
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li><a class="dropdown-item" href="../users/profile.php">My Profile</a></li>
                        <li><a class="dropdown-item" href="../recipes/manage.php">Manage Recipes</a></li>
                        <li><a class="dropdown-item" href="../users/logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="text-center">Add a New Recipe</h1>

        <!-- Display Errors -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error) echo "<li>$error</li>"; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Recipe Submission Form -->
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Recipe Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-control" required>
                    <option value="Appetizer">Appetizer</option>
                    <option value="Main Course">Main Course</option>
                    <option value="Dessert">Dessert</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Recipe Image</label>
                <input type="file" name="image" class="form-control" required>
            </div>

            <!-- Steps -->
            <h4>Steps</h4>
            <div id="steps-container">
                <div class="mb-3 step-group">
                    <label class="form-label">Step 1</label>
                    <textarea name="steps[]" class="form-control" rows="2" required></textarea>
                    <label class="form-label">Step 1 Image</label>
                    <input type="file" name="step_images[]" class="form-control">
                </div>
            </div>
            <button type="button" id="add-step" class="btn btn-secondary">Add Step</button>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Add Recipe</button>
            </div>
        </form>
    </div>

    <script>
        // JavaScript to dynamically add steps
        let stepCount = 1;
        document.getElementById("add-step").addEventListener("click", function() {
            stepCount++;
            let stepDiv = document.createElement("div");
            stepDiv.classList.add("mb-3", "step-group");
            stepDiv.innerHTML = `
                <label class="form-label">Step ${stepCount}</label>
                <textarea name="steps[]" class="form-control" rows="2" required></textarea>
                <label class="form-label">Step ${stepCount} Image</label>
                <input type="file" name="step_images[]" class="form-control">
            `;
            document.getElementById("steps-container").appendChild(stepDiv);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>