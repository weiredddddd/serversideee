<?php
session_start();
require '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

// Check if recipe ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage.php");
    exit();
}

$recipe_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch the recipe to edit
$stmt = $RecipeDB->prepare("SELECT * FROM Recipes WHERE recipe_id = ? AND user_id = ?");
$stmt->execute([$recipe_id, $user_id]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

// If recipe doesn't exist or doesn't belong to the user, redirect
if (!$recipe) {
    header("Location: manage.php");
    exit();
}

// Fetch existing ingredients
$ingredient_stmt = $RecipeDB->prepare("SELECT i.ingredient_id, i.ingredient_name, ri.quantity, ri.unit 
                                FROM Recipe_Ingredient ri
                                JOIN Ingredients i ON ri.ingredient_id = i.ingredient_id
                                WHERE ri.recipe_id = ?");
$ingredient_stmt->execute([$recipe_id]);
$existing_ingredients = $ingredient_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing steps
$step_stmt = $RecipeDB->prepare("SELECT * FROM Steps WHERE recipe_id = ? ORDER BY step_no");
$step_stmt->execute([$recipe_id]);
$existing_steps = $step_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing nutrition data (add this with other fetches at the top)
$nutrition_stmt = $RecipeDB->prepare("SELECT * FROM Nutrition WHERE recipe_id = ?");
$nutrition_stmt->execute([$recipe_id]);
$existing_nutrition = $nutrition_stmt->fetch(PDO::FETCH_ASSOC);
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $cuisine_type = trim($_POST['cuisine_type']);
    $spice_level = isset($_POST['spice_level']) ? (int)$_POST['spice_level'] : 0;

    // Validate inputs
    if (empty($title)) {
        $errors[] = "Title is required.";
    }
    if (empty($description)) {
        $errors[] = "Description is required.";
    }
    if (empty($category)) {
        $errors[] = "Category is required.";
    }

    // Handle image upload
    $image_url = $recipe['image_url'];
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = '../uploads/recipe/';
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $image_name;

        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF files are allowed.";
        } elseif ($_FILES['image']['size'] > 10 * 1024 * 1024) {
            $errors[] = "File size exceeds 10MB limit.";
        }

        if (empty($errors)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = $image_name;
                // Optionally delete old image
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    // Handle steps
    $steps = [];
    if (!empty($_POST['steps'])) {
        foreach ($_POST['steps'] as $index => $step_desc) {
            if (!empty($step_desc)) {
                $steps[$index] = [
                    'description' => $step_desc,
                    'image_url' => ''
                ];
            }
        }
    }

    // Handle step images
    if (!empty($_FILES['step_images']['name'])) {
        foreach ($_FILES['step_images']['name'] as $index => $name) {
            if (!empty($name) && isset($steps[$index])) {
                $upload_dir = '../uploads/recipe/';
                $step_image_name = uniqid() . '_' . basename($name);
                $target_file = $upload_dir . $step_image_name;

                if (move_uploaded_file($_FILES['step_images']['tmp_name'][$index], $target_file)) {
                    $steps[$index]['image_url'] = $step_image_name;
                }
            }
        }
    }

    // Handle ingredients
    $ingredients = [];
    if (!empty($_POST['ingredients'])) {
        foreach ($_POST['ingredients'] as $ing) {
            if (!empty($ing['name']) && !empty($ing['quantity'])) {
                $ingredients[] = [
                    'name' => $ing['name'],
                    'quantity' => $ing['quantity'],
                    'unit' => $ing['unit'] ?? 'g'
                ];
            }
        }
    }

    // Update nutrition facts
    if (!empty($_POST['nutrition'])) {
        $nutrition = $_POST['nutrition'];
        // Validate nutrition values
        foreach (['calories', 'fat', 'carbs', 'protein'] as $field) {
            if (isset($nutrition[$field]) && !is_numeric($nutrition[$field])) {
                $errors[] = "Nutrition $field must be a number";
            }
        }
        if ($existing_nutrition) {
            // Update existing nutrition data
            $stmt = $RecipeDB->prepare("UPDATE Nutrition SET 
                                    calories = ?, fat = ?, carbs = ?, protein = ? 
                                    WHERE recipe_id = ?");
            $stmt->execute([
                $nutrition['calories'] ?? null,
                $nutrition['fat'] ?? null,
                $nutrition['carbs'] ?? null,
                $nutrition['protein'] ?? null,
                $recipe_id
            ]);
        } else {
            // Insert new nutrition data if it doesn't exist
            $stmt = $RecipeDB->prepare("INSERT INTO Nutrition (recipe_id, calories, fat, carbs, protein) 
                                    VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $recipe_id,
                $nutrition['calories'] ?? null,
                $nutrition['fat'] ?? null,
                $nutrition['carbs'] ?? null,
                $nutrition['protein'] ?? null
            ]);
        }
    }
    // If no errors, update everything
    if (empty($errors)) {
        try {
            $RecipeDB->beginTransaction();

            // Update recipe
            $stmt = $RecipeDB->prepare("UPDATE Recipes SET 
                                  title = ?, description = ?, category = ?, 
                                  cuisine_type = ?, spice_level = ?, image_url = ? 
                                  WHERE recipe_id = ? AND user_id = ?");
            $stmt->execute([
                $title,
                $description,
                $category,
                $cuisine_type,
                $spice_level,
                $image_url,
                $recipe_id,
                $user_id
            ]);

            // Delete existing ingredients and steps
            $RecipeDB->prepare("DELETE FROM Recipe_Ingredient WHERE recipe_id = ?")->execute([$recipe_id]);
            $RecipeDB->prepare("DELETE FROM Steps WHERE recipe_id = ?")->execute([$recipe_id]);

            // Insert ingredients
            foreach ($ingredients as $ingredient) {
                // Check if ingredient exists
                $stmt = $RecipeDB->prepare("SELECT ingredient_id FROM Ingredients WHERE ingredient_name = ?");
                $stmt->execute([$ingredient['name']]);
                $existing = $stmt->fetch();

                $ingredient_id = $existing ? $existing['ingredient_id'] : null;

                if (!$existing) {
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
                    $ingredient['unit']
                ]);
            }

            // Insert steps
            foreach ($steps as $step_no => $step) {
                $stmt = $RecipeDB->prepare("INSERT INTO Steps 
                                      (recipe_id, step_no, description, image_url) 
                                      VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $recipe_id,
                    $step_no + 1, // steps start at 1
                    $step['description'],
                    $step['image_url']
                ]);
            }

            $RecipeDB->commit();

            $_SESSION['success_message'] = "Recipe updated successfully!";
            header("Location: manage.php");
            exit();
        } catch (Exception $e) {
            $RecipeDB->rollBack();
            $errors[] = "Error updating recipe: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../recipes/css/recipe.css">
</head>

<body>
    <?php include_once '../includes/navigation.php'; ?> <!-- Include navigation bar -->

    <div class="container mt-5">
        <h2>Edit Recipe</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <!-- Basic Info -->
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($recipe['title']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($recipe['description']) ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control" required>
                        <option value="Appetizer" <?= $recipe['category'] === 'Appetizer' ? 'selected' : '' ?>>Appetizer</option>
                        <option value="Main Course" <?= $recipe['category'] === 'Main Course' ? 'selected' : '' ?>>Main Course</option>
                        <option value="Dessert" <?= $recipe['category'] === 'Dessert' ? 'selected' : '' ?>>Dessert</option>
                        <option value="Side Dish" <?= $recipe['category'] === 'Side Dish' ? 'selected' : '' ?>>Side Dish</option>
                        <option value="Beverage" <?= $recipe['category'] === 'Beverage' ? 'selected' : '' ?>>Beverage</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Cuisine Type</label>
                    <select name="cuisine_type" class="form-control" required>
                        <option value="Japanese" <?= $recipe['cuisine_type'] === 'Japanese' ? 'selected' : '' ?>>Japanese</option>
                        <option value="Western" <?= $recipe['cuisine_type'] === 'Western' ? 'selected' : '' ?>>Western</option>
                        <option value="Chinese" <?= $recipe['cuisine_type'] === 'Chinese' ? 'selected' : '' ?>>Chinese</option>
                        <option value="Indian" <?= $recipe['cuisine_type'] === 'Indian' ? 'selected' : '' ?>>Indian</option>
                        <option value="Italian" <?= $recipe['cuisine_type'] === 'Italian' ? 'selected' : '' ?>>Italian</option>
                        <option value="Mexican" <?= $recipe['cuisine_type'] === 'Mexican' ? 'selected' : '' ?>>Mexican</option>
                        <option value="Thai" <?= $recipe['cuisine_type'] === 'Thai' ? 'selected' : '' ?>>Thai</option>
                        <option value="Other" <?= $recipe['cuisine_type'] === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Spice Level</label>
                    <div class="spice-level">
                        <?php $current_spice = $recipe['spice_level'] ?? 0; ?>
                        <?php foreach ([0 => 'None', 1 => 'Mild', 2 => 'Medium', 3 => 'Spicy', 4 => 'Very Spicy'] as $level => $label): ?>
                            <div class="form-check spice-option">
                                <input class="form-check-input" type="radio" name="spice_level" id="spice<?= $level ?>"
                                    value="<?= $level ?>" <?= $current_spice == $level ? 'checked' : '' ?>>
                                <label class="form-check-label" for="spice<?= $level ?>"><?= $label ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Image -->
            <div class="mb-3">
                <label class="form-label">Recipe Image</label>
                <input type="file" name="image" class="form-control">
                <?php if (!empty($recipe['image_url'])): ?>
                    <p class="mt-2">Current Image: <a href="../uploads/recipe/<?= htmlspecialchars($recipe['image_url']) ?>" target="_blank"><?= htmlspecialchars($recipe['image_url']) ?></a></p>
                    <img src="../uploads/recipe/<?= htmlspecialchars($recipe['image_url']) ?>" class="img-thumbnail" style="max-height: 150px;">
                <?php endif; ?>
            </div>

            <!-- Ingredients -->
            <h4>Ingredients</h4>
            <div id="ingredients-container">
                <?php foreach ($existing_ingredients as $index => $ing): ?>
                    <div class="ingredient-group row" data-index="<?= $index ?>">
                        <div class="col-md-5">
                            <label class="form-label">Ingredient Name</label>
                            <input type="text" name="ingredients[<?= $index ?>][name]" class="form-control"
                                value="<?= htmlspecialchars($ing['ingredient_name']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantity</label>
                            <input type="text" name="ingredients[<?= $index ?>][quantity]" class="form-control"
                                value="<?= htmlspecialchars($ing['quantity']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unit</label>
                            <select name="ingredients[<?= $index ?>][unit]" class="form-control">
                                <?php foreach (['g', 'kg', 'ml', 'L', 'tsp', 'tbsp', 'cup', 'pinch', 'piece','oz','lb','clove','slice','can','bottle'] as $unit): ?>
                                    <option value="<?= $unit ?>" <?= $ing['unit'] === $unit ? 'selected' : '' ?>>
                                        <?= $unit ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <span class="remove-btn" onclick="removeIngredient(this)">✕</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="add-ingredient" class="btn btn-secondary mb-4">Add Ingredient</button>

            <!-- Steps -->
            <h4>Steps</h4>
            <div id="steps-container">
                <?php foreach ($existing_steps as $index => $step): ?>
                    <div class="step-group mb-3" data-index="<?= $index ?>">
                        <div class="row">
                            <div class="col-md-11">
                                <label class="form-label">Step <?= $index + 1 ?></label>
                                <textarea name="steps[<?= $index ?>]" class="form-control" rows="2" required><?= htmlspecialchars($step['description']) ?></textarea>
                                <label class="form-label mt-2">Step Image</label>
                                <input type="file" name="step_images[<?= $index ?>]" class="form-control mb-2">
                                <?php if (!empty($step['image_url'])): ?>
                                    <p>Current Image: <a href="../uploads/recipe/?= htmlspecialchars($step['image_url']) ?>" target="_blank"><?= htmlspecialchars($step['image_url']) ?></a></p>
                                    <img src="../uploads/recipe/<?= htmlspecialchars($step['image_url']) ?>" class="img-thumbnail" style="max-height: 100px;">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <span class="remove-btn" onclick="removeStep(this)">✕</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="add-step" class="btn btn-secondary mb-4">Add Step</button>
            <h4>Nutrition Facts</h4>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Calories</label>
                    <input type="number" name="nutrition[calories]" class="form-control"
                        value="<?= htmlspecialchars($existing_nutrition['calories'] ?? '') ?>"
                        placeholder="e.g., 200">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Fat (g)</label>
                    <input type="number" step="0.1" name="nutrition[fat]" class="form-control"
                        value="<?= htmlspecialchars($existing_nutrition['fat'] ?? '') ?>"
                        placeholder="e.g., 10.5">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Carbs (g)</label>
                    <input type="number" step="0.1" name="nutrition[carbs]" class="form-control"
                        value="<?= htmlspecialchars($existing_nutrition['carbs'] ?? '') ?>"
                        placeholder="e.g., 30.2">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Protein (g)</label>
                    <input type="number" step="0.1" name="nutrition[protein]" class="form-control"
                        value="<?= htmlspecialchars($existing_nutrition['protein'] ?? '') ?>"
                        placeholder="e.g., 15.8">
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Update Recipe</button>
                <a href="manage.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        
    // Ingredients functionality
    let ingredientCount = <?= count($existing_ingredients) ?>;
    document.getElementById("add-ingredient").addEventListener("click", function() {
        ingredientCount++;
        const ingredientDiv = document.createElement("div");
        ingredientDiv.classList.add("ingredient-group", "row", "mb-3");
        ingredientDiv.setAttribute('data-index', ingredientCount);
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

    // Steps functionality
    let stepCount = <?= count($existing_steps) ?>;
    document.getElementById("add-step").addEventListener("click", function() {
        stepCount++;
        const stepDiv = document.createElement("div");
        stepDiv.classList.add("step-group", "mb-3");
        stepDiv.setAttribute('data-index', stepCount);
        stepDiv.innerHTML = `
            <div class="row">
                <div class="col-md-11">
                    <label class="form-label">Step ${stepCount}</label>
                    <textarea name="steps[${stepCount}]" class="form-control" rows="2" required></textarea>
                    <label class="form-label mt-2">Step Image</label>
                    <input type="file" name="step_images[${stepCount}]" class="form-control mb-2">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <span class="remove-btn" onclick="removeStep(this)">✕</span>
                </div>
            </div>
        `;
        document.getElementById("steps-container").appendChild(stepDiv);
    });

    // Function to remove an ingredient
    function removeIngredient(button) {
        const ingredientGroup = button.closest('.ingredient-group');
        if (ingredientGroup) {
            ingredientGroup.remove();
        }
    }

    // Function to remove a step
    function removeStep(button) {
        const stepGroup = button.closest('.step-group');
        if (stepGroup) {
            stepGroup.remove();
        }
    }
</script>
   


    <?php include_once '../includes/footer.php'; ?>
</body>

</html>
