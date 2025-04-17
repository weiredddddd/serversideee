<?php
ob_start(); // Prevent output issues
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Redirect if not logged in (BEFORE any output)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

require '../config/db.php';


$errors = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $cuisine_type = trim($_POST['cuisine_type']);
    $spice_level = isset($_POST['spice_level']) ? (int)$_POST['spice_level'] : 0;
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
    if (empty($cuisine_type)) {
        $errors[] = "Cuisine type is required.";
    }
    if (empty($_POST['ingredients'])) {
        $errors[] = "At least one ingredient is required.";
    } else {
        foreach ($_POST['ingredients'] as $index => $ingredient) {
            if (empty($ingredient['name']) || empty($ingredient['quantity'])) {
                $errors[] = "Ingredient name and quantity are required for ingredient " . ($index + 1) . ".";
            }
        }
    }
    if (empty($_POST['steps'])) {
        $errors[] = "At least one step is required.";
    } else {
        foreach ($_POST['steps'] as $index => $step_desc) {
            if (empty($step_desc)) {
                $errors[] = "Step description is required for step " . ($index + 1) . ".";
            }
        }
    }

    // Handle main recipe image upload
    $image_url = "";
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = "../uploads/recipe/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $image_url = uniqid() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $upload_dir . $image_url;

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error: " . $_FILES['image']['error'];
        } else {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = mime_content_type($_FILES['image']['tmp_name']);
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "Only JPG, PNG, and GIF files are allowed for the main image.";
            }

            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $errors[] = "Only JPG, PNG, and GIF files are allowed for the main image.";
            }

            $max_file_size = 10 * 1024 * 1024;
            if ($_FILES['image']['size'] > $max_file_size) {
                $errors[] = "File size exceeds the maximum allowed size of 10 MB.";
            }

            if (empty($errors)) {
                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $errors[] = "Failed to upload the main image. Check file permissions or path.";
                    error_log("Upload error: " . print_r($_FILES, true));
                }
            }
        }
    } else {
        $errors[] = "Main recipe image is required.";
    }

    // Insert recipe into database if no errors
    if (empty($errors)) {
        $stmt = $RecipeDB->prepare("INSERT INTO Recipes (title, description, category, cuisine_type, spice_level, image_url, user_id) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $category, $cuisine_type, $spice_level, $image_url, $user_id]);

        $recipe_id = $RecipeDB->lastInsertId();

        // Insert ingredients
        if (!empty($_POST['ingredients'])) {
            foreach ($_POST['ingredients'] as $ingredient) {
                if (!empty($ingredient['name']) && !empty($ingredient['quantity'])) {
                    try {
                        // Check if ingredient exists
                        $stmt = $RecipeDB->prepare("SELECT ingredient_id FROM Ingredients WHERE ingredient_name = ?");
                        $stmt->execute([$ingredient['name']]);
                        $existing = $stmt->fetch();

                        if ($existing) {
                            $ingredient_id = $existing['ingredient_id'];
                        } else {
                            // Insert new ingredient
                            $stmt = $RecipeDB->prepare("INSERT INTO Ingredients (ingredient_name) VALUES (?)");
                            $stmt->execute([$ingredient['name']]);
                            $ingredient_id = $RecipeDB->lastInsertId();
                        }

                        // Link to recipe
                        $stmt = $RecipeDB->prepare("INSERT INTO Recipe_Ingredient 
                                     (recipe_id, ingredient_id, quantity, unit) 
                                     VALUES (?, ?, ?, ?)");
                        $stmt->execute([
                            $recipe_id,
                            $ingredient_id,
                            $ingredient['quantity'],
                            $ingredient['unit'] ?? 'g' // Default to grams
                        ]);
                    } catch (PDOException $e) {
                        $errors[] = "Error processing ingredient: " . $ingredient['name'] . ". " . $e->getMessage();
                        error_log("Ingredient processing error: " . $e->getMessage());
                        continue;
                    }
                }
            }
        }

        // Insert steps with images
        $step_images = [];
        foreach ($_POST['steps'] as $index => $step_desc) {
            if (!empty($step_desc)) {
                $step_image_url = "";
                if (!empty($_FILES['step_images']['name'][$index])) {
                    $upload_dir = "../uploads/recipe/";
                    $step_image_url = uniqid() . "_" . basename($_FILES["step_images"]["name"][$index]);
                    $target_file = $upload_dir . $step_image_url;

                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $file_type = mime_content_type($_FILES['step_images']['tmp_name'][$index]);
                    if (!in_array($file_type, $allowed_types)) {
                        $errors[] = "Only JPG, PNG, and GIF files are allowed for step images.";
                    }
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    if (!in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $errors[] = "Only JPG, PNG, and GIF files are allowed for the step image.";
                    }

                    $max_file_size = 10 * 1024 * 1024;
                    if ($_FILES['image']['size'] > $max_file_size) {
                        $errors[] = "File size exceeds the maximum allowed size of 10 MB.";
                    }


                    if (empty($errors)) {
                        if (!move_uploaded_file($_FILES["step_images"]["tmp_name"][$index], $target_file)) {
                            $errors[] = "Failed to upload step image for step " . ($index + 1) . ".";
                        }
                    }
                }
                $step_images[] = $step_image_url;
                $step_stmt = $RecipeDB->prepare("INSERT INTO Steps (recipe_id, step_no, description, image_url) 
                                            VALUES (?, ?, ?, ?)");
                $step_stmt->execute([$recipe_id, $index + 1, $step_desc, $step_image_url]);
            }
        }

        if (!empty($_POST['nutrition'])) {
            $nutrition = $_POST['nutrition'];

            // Validate that all nutrition fields are filled and numeric
            if (
                empty($nutrition['calories']) || !is_numeric($nutrition['calories']) ||
                empty($nutrition['fat']) || !is_numeric($nutrition['fat']) ||
                empty($nutrition['carbs']) || !is_numeric($nutrition['carbs']) ||
                empty($nutrition['protein']) || !is_numeric($nutrition['protein'])
            ) {
                $errors[] = "All nutrition fields (calories, fat, carbs, protein) are required and must be numeric.";
            } else {
                $stmt = $RecipeDB->prepare("INSERT INTO Nutrition (recipe_id, calories, fat, carbs, protein) 
                                            VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $recipe_id,
                    $nutrition['calories'],
                    $nutrition['fat'],
                    $nutrition['carbs'],
                    $nutrition['protein']
                ]);
            }
        }
        if (empty($errors)) {
            $_SESSION['success_message'] = "Recipe posted successfully!";
            header("Location: ../recipes/view.php?id=" . $recipe_id);
            exit();
        }
    }
}
ob_end_flush(); // Flush output buffer to prevent header issues
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Add Recipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../recipes/css/recipe.css">
</head>

<body>
    <?php include_once '../includes/navigation.php'; ?> <!-- Include navigation bar -->
    <div class="container mt-4">
        <h1 class="text-center">Add a New Recipe</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error) echo "<li>$error</li>"; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Recipe Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control" required>
                        <option value="Appetizer" <?= (isset($_POST['category']) && $_POST['category'] === 'Appetizer') ? 'selected' : '' ?>>Appetizer</option>
                        <option value="Main Course" <?= (isset($_POST['category']) && $_POST['category'] === 'Main Course') ? 'selected' : '' ?>>Main Course</option>
                        <option value="Dessert" <?= (isset($_POST['category']) && $_POST['category'] === 'Dessert') ? 'selected' : '' ?>>Dessert</option>
                        <option value="Side Dish" <?= (isset($_POST['category']) && $_POST['category'] === 'Side Dish') ? 'selected' : '' ?>>Side Dish</option>
                        <option value="Beverage" <?= (isset($_POST['category']) && $_POST['category'] === 'Beverage') ? 'selected' : '' ?>>Beverage</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Cuisine Type</label>
                    <select name="cuisine_type" class="form-control" required>
                        <option value="Japanese" <?= (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] === 'Japanese') ? 'selected' : '' ?>>Japanese</option>
                        <option value="Western" <?= (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] === 'Western') ? 'selected' : '' ?>>Western</option>
                        <option value="Chinese" <?= (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] === 'Chinese') ? 'selected' : '' ?>>Chinese</option>
                        <option value="Indian" <?= (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] === 'Indian') ? 'selected' : '' ?>>Indian</option>
                        <option value="Italian" <?= (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] === 'Italian') ? 'selected' : '' ?>>Italian</option>
                        <option value="Mexican" <?= (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] === 'Mexican') ? 'selected' : '' ?>>Mexican</option>
                        <option value="Thai" <?= (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] === 'Thai') ? 'selected' : '' ?>>Thai</option>
                        <option value="Other" <?= (isset($_POST['cuisine_type']) && $_POST['cuisine_type'] === 'Other') ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Spice Level</label>
                    <div class="spice-level">
                        <div class="form-check spice-option">
                            <input class="form-check-input" type="radio" name="spice_level" id="spice0" value="0" <?= (!isset($_POST['spice_level']) || $_POST['spice_level'] == 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="spice0">None</label>
                        </div>
                        <div class="form-check spice-option">
                            <input class="form-check-input" type="radio" name="spice_level" id="spice1" value="1" <?= (isset($_POST['spice_level']) && $_POST['spice_level'] == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="spice1">Mild</label>
                        </div>
                        <div class="form-check spice-option">
                            <input class="form-check-input" type="radio" name="spice_level" id="spice2" value="2" <?= (isset($_POST['spice_level']) && $_POST['spice_level'] == 2) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="spice2">Medium</label>
                        </div>
                        <div class="form-check spice-option">
                            <input class="form-check-input" type="radio" name="spice_level" id="spice3" value="3" <?= (isset($_POST['spice_level']) && $_POST['spice_level'] == 3) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="spice3">Spicy</label>
                        </div>
                        <div class="form-check spice-option">
                            <input class="form-check-input" type="radio" name="spice_level" id="spice4" value="4" <?= (isset($_POST['spice_level']) && $_POST['spice_level'] == 4) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="spice4">Very Spicy</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Recipe Image</label>
                <?php if (!empty($image_url)): ?>
                    <div class="mb-2">
                        <img src="../uploads/recipe/<?= htmlspecialchars($image_url) ?>" alt="Recipe Image" class="img-thumbnail" style="max-width: 200px;">
                    </div>
                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($image_url) ?>">
                <?php endif; ?>
                <input type="file" name="image" class="form-control">
            </div>

            <h4>Ingredients</h4>
            <div id="ingredients-container">
                <?php if (!empty($_POST['ingredients'])): ?>
                    <?php foreach ($_POST['ingredients'] as $index => $ingredient): ?>
                        <div class="mb-3 ingredient-group row">
                            <div class="col-md-5">
                                <label class="form-label">Ingredient Name</label>
                                <input type="text" name="ingredients[<?= $index ?>][name]" class="form-control" value="<?= htmlspecialchars($ingredient['name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Quantity</label>
                                <input type="text" name="ingredients[<?= $index ?>][quantity]" class="form-control" value="<?= htmlspecialchars($ingredient['quantity'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Unit</label>
                                <select name="ingredients[<?= $index ?>][unit]" class="form-control">
                                    <option value="g" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'g') ? 'selected' : '' ?>>g</option>
                                    <option value="kg" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'kg') ? 'selected' : '' ?>>kg</option>
                                    <option value="ml" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'ml') ? 'selected' : '' ?>>ml</option>
                                    <option value="L" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'L') ? 'selected' : '' ?>>L</option>
                                    <option value="tsp" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'tsp') ? 'selected' : '' ?>>tsp</option>
                                    <option value="tbsp" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'tbsp') ? 'selected' : '' ?>>tbsp</option>
                                    <option value="cup" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'cup') ? 'selected' : '' ?>>cup</option>
                                    <option value="pinch" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'pinch') ? 'selected' : '' ?>>pinch</option>
                                    <option value="piece" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'piece') ? 'selected' : '' ?>>piece</option>
                                    <option value="oz" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'oz') ? 'selected' : '' ?>>oz</option>
                                    <option value="lb" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'lb') ? 'selected' : '' ?>>lb</option>
                                    <option value="clove" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'clove') ? 'selected' : '' ?>>clove</option>
                                    <option value="slice" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'slice') ? 'selected' : '' ?>>slice</option>
                                    <option value="can" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'can') ? 'selected' : '' ?>>can</option>
                                    <option value="bottle" <?= (isset($ingredient['unit']) && $ingredient['unit'] === 'bottle') ? 'selected' : '' ?>>bottle</option>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default Ingredient -->
                    <div class="mb-3 ingredient-group row">
                        <div class="col-md-5">
                            <label class="form-label">Ingredient Name</label>
                            <input type="text" name="ingredients[0][name]" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantity</label>
                            <input type="text" name="ingredients[0][quantity]" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unit</label>
                            <select name="ingredients[0][unit]" class="form-control">
                                <option value="g">g</option>
                                <option value="kg">kg</option>
                                <option value="ml">ml</option>
                                <option value="L">L</option>
                                <option value="tsp">tsp</option>
                                <option value="tbsp">tbsp</option>
                                <option value="cup">cup</option>
                                <option value="pinch">pinch</option>
                                <option value="piece">piece</option>
                                <option value="oz">oz</option>
                                <option value="lb">lb</option>
                                <option value="clove">clove</option>
                                <option value="slice">slice</option>
                                <option value="can">can</option>
                                <option value="bottle">bottle</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                        <span class="remove-btn" onclick="removeIngredient(this)">✕</span>
                    </div>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" id="add-ingredient" class="btn btn-secondary mb-3">Add Ingredient</button>

            <h4>Steps</h4>
            <div id="steps-container">
                <?php if (!empty($_POST['steps'])): ?>
                    <?php foreach ($_POST['steps'] as $index => $step_desc): ?>
                        <div class="mb-3 step-group row">
                            <div class="col-md-11">
                                <label class="form-label">Step <?= $index + 1 ?></label>
                                <textarea name="steps[]" class="form-control" rows="2" required><?= htmlspecialchars($step_desc) ?></textarea>
                                <?php if (!empty($step_images[$index])): ?>
                                    <div class="mb-2">
                                        <img src="../uploads/recipe/<?= htmlspecialchars($step_images[$index]) ?>" alt="Step Image" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                    <input type="hidden" name="existing_step_images[<?= $index ?>]" value="<?= htmlspecialchars($step_images[$index]) ?>">
                                <?php endif; ?>
                                <label class="form-label mt-2">Step <?= $index + 1 ?> Image</label>
                                <input type="file" name="step_images[]" class="form-control">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default Step -->
                    <div class="mb-3 step-group row">
                        <div class="col-md-11">
                            <label class="form-label">Step 1</label>
                            <textarea name="steps[]" class="form-control" rows="2" required></textarea>
                            <label class="form-label mt-2">Step 1 Image</label>
                            <input type="file" name="step_images[]" class="form-control">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                        <span class="remove-btn" onclick="removeStep(this)">✕</span>
                    </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="button" id="add-step" class="btn btn-secondary">Add Step</button>


            <h4>Nutrition Facts</h4>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Calories</label>
                    <input type="number" name="nutrition[calories]" class="form-control" value="<?= htmlspecialchars($_POST['nutrition']['calories'] ?? '') ?>" placeholder="e.g., 200">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Fat (g)</label>
                    <input type="number" step="0.1" name="nutrition[fat]" class="form-control" value="<?= htmlspecialchars($_POST['nutrition']['fat'] ?? '') ?>" placeholder="e.g., 10.5">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Carbs (g)</label>
                    <input type="number" step="0.1" name="nutrition[carbs]" class="form-control" value="<?= htmlspecialchars($_POST['nutrition']['carbs'] ?? '') ?>" placeholder="e.g., 30.2">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Protein (g)</label>
                    <input type="number" step="0.1" name="nutrition[protein]" class="form-control" value="<?= htmlspecialchars($_POST['nutrition']['protein'] ?? '') ?>" placeholder="e.g., 15.8">
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Add Recipe</button>
            </div>
        </form>
    </div>

    <script>
        // Ingredients functionality
        let ingredientCount = document.querySelectorAll("#ingredients-container .ingredient-group").length - 1;

        document.getElementById("add-ingredient").addEventListener("click", function() {
            ingredientCount++;
            let ingredientDiv = document.createElement("div");
            ingredientDiv.classList.add("mb-3", "ingredient-group", "row");
            ingredientDiv.innerHTML = `
                <div class="col-md-5">
                    <label class="form-label">Ingredient Name</label>
                    <input type="text" name="ingredients[${ingredientCount}][name]" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantity</label>
                    <input type="text" name="ingredients[${ingredientCount}][quantity]" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Unit</label>
                    <select name="ingredients[${ingredientCount}][unit]" class="form-control">
                       <option value="g">g</option>
    <option value="kg">kg</option>
    <option value="ml">ml</option>
    <option value="L">L</option>
    <option value="tsp">tsp</option>
    <option value="tbsp">tbsp</option>
    <option value="cup">cup</option>
    <option value="pinch">pinch</option>
    <option value="piece">piece</option>
    <option value="oz">oz</option>
    <option value="lb">lb</option>
    <option value="clove">clove</option>
    <option value="slice">slice</option>
    <option value="can">can</option>
    <option value="bottle">bottle</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                        <span class="remove-btn" onclick="removeIngredient(this)">✕</span>
                    </div>
            `;
            document.getElementById("ingredients-container").appendChild(ingredientDiv);
        });

        function removeIngredient(element) {
    const container = document.getElementById("ingredients-container");
    const groups = container.querySelectorAll('.ingredient-group');
    if (groups.length > 1) {
        element.closest('.ingredient-group').remove();
    } else {
        alert("You must have at least one ingredient. Add a new one first if you want to replace this.");
    }
}

        // Steps functionality
        let stepCount = document.querySelectorAll("#steps-container .step-group").length;
        document.getElementById("add-step").addEventListener("click", function() {
            stepCount++;
            let stepDiv = document.createElement("div");
            stepDiv.classList.add("mb-3", "step-group", "row");
            stepDiv.innerHTML = `
        <div class="col-md-11">
            <label class="form-label">Step ${stepCount}</label>
            <textarea name="steps[]" class="form-control" rows="2" required></textarea>
            <label class="form-label mt-2">Step ${stepCount} Image</label>
            <input type="file" name="step_images[]" class="form-control">
        </div>
       <div class="col-md-1 d-flex align-items-end">
                        <span class="remove-btn" onclick="removeStep(this)">✕</span>
                    </div>
    `;
            document.getElementById("steps-container").appendChild(stepDiv);
        });

        // Function to remove a step
        function removeStep(element) {
    const container = document.getElementById("steps-container");
    const groups = container.querySelectorAll('.step-group');
    if (groups.length > 1) {
        element.closest('.step-group').remove();
        // Renumber remaining steps
        const remainingSteps = container.querySelectorAll('.step-group');
        remainingSteps.forEach((group, index) => {
            group.querySelector('label').textContent = `Step ${index + 1}`;
        });
        stepCount = remainingSteps.length;
    } else {
        alert("You must have at least one step. Add a new one first if you want to replace this.");
    }
}
    </script>
    <?php include_once '../includes/footer.php'; ?>
</body>

</html>
