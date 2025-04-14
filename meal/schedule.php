<?php
session_start();
require '../config/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Planner - NoiceFoodie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="schedule.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/react@17/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@17/umd/react-dom.development.js"></script>
    <script src="components/nutrition-meter.js"></script>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    <div class="container py-4">
        <div class="page-header px-4">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-calendar-check me-2"></i>Meal Planner</h2>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#datePickerModal" style="position: relative; z-index: 3;">
                    <i class="bi bi-calendar-range me-1"></i> Choose Date
                </button>
            </div>
        </div>

        <!-- Week Navigation -->
        <div class="week-navigation">
            <button class="btn btn-outline-primary" id="prev-week">
                <i class="bi bi-chevron-left me-1"></i> Previous Week
            </button>
            <h4 id="current-week-range" class="text-center"></h4>
            <button class="btn btn-outline-primary" id="next-week">
                Next Week <i class="bi bi-chevron-right ms-1"></i>
            </button>
        </div>

        <div class="row">
            <!-- Main Day Display -->
            <div class="col-md-8" id="main-day-display">
                <!-- Dynamically inserted content -->
            </div>

            <!-- Compact Weekly View -->
            <div class="col-md-4">
                <div class="row g-2" id="compact-weekly-view">
                    <!-- Dynamically inserted compact days -->
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Meal Modal -->
    <div class="modal fade" id="mealModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Plan Your Meal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="mealForm">
                        <input type="hidden" id="mealDate" name="date">
                        <input type="hidden" id="mealSlot" name="time_slot">
                        <input type="hidden" id="mealPlanId" name="meal_plan_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold"><i class="bi bi-book me-1"></i>Select Recipe</label>
                                    <select class="form-select" id="recipeSelect" name="recipe_id">
                                        <option value="">Choose a recipe...</option>
                                        <?php
                                        $recipes = $RecipeDB->query("SELECT recipe_id, title FROM Recipes");
                                        while ($recipe = $recipes->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value='{$recipe['recipe_id']}'>{$recipe['title']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="text-center mb-3 position-relative">
                                    <div class="position-absolute w-100" style="top: 50%; height: 1px; background-color: #e5e7eb;"></div>
                                    <span class="position-relative bg-white px-3 text-muted">OR</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold"><i class="bi bi-pencil me-1"></i>Custom Meal</label>
                                    <input type="text" class="form-control" id="customMeal" name="custom_meal_name" placeholder="Enter custom meal name">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3" id="customNutritionFields" style="display: none;">
                            <label class="form-label fw-bold"><i class="bi bi-bar-chart me-1"></i>Nutrition Information</label>
                            <div class="row">
                                <div class="col">
                                    <input type="number" class="form-control" id="customCalories" name="custom_calories" placeholder="Calories" min="0">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" id="customFat" name="custom_fat" placeholder="Fat (g)" min="0" step="0.1">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" id="customCarbs" name="custom_carbs" placeholder="Carbs (g)" min="0" step="0.1">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" id="customProtein" name="custom_protein" placeholder="Protein (g)" min="0" step="0.1">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="bi bi-card-text me-1"></i>Additional Notes</label>
                            <textarea class="form-control" id="mealNotes" name="custom_meal_description" rows="3" placeholder="Add preparation notes..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btnDelete" style="display: none;">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Close
                    </button>
                    <button type="button" class="btn btn-primary" id="btnSave">
                        <i class="bi bi-check-circle me-1"></i>Save Meal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Picker Modal -->
    <div class="modal fade" id="datePickerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-calendar-date me-2"></i>Jump to Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="date" class="form-control" id="targetDate" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btnJumpToDate">
                        <i class="bi bi-arrow-right-circle me-1"></i>Go to Date
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="schedule.js"></script>
</body>
</html>