<?php
session_start();
include '../includes/navigation.php';
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .meal-day { background: #f8f9fa; border-radius: 8px; }
        .meal-slot { border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; }
        .meal-item { background: #fff; border-radius: 4px; padding: 10px; margin-bottom: 10px; }
        .compact-day { cursor: pointer; font-size: 0.8rem; background: #f8f9fa; }
        .compact-meal-slot { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding: 2px 5px; }
        .compact-day.active { background: #e7f1ff; border: 1px solid #86b7fe; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Meal Planner</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#datePickerModal">
                <i class="bi bi-calendar-range"></i> Choose Date
            </button>
        </div>

        <!-- Week Navigation -->
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between">
                <button class="btn btn-outline-primary" id="prev-week">« Previous Week</button>
                <h4 id="current-week-range" class="text-center"></h4>
                <button class="btn btn-outline-primary" id="next-week">Next Week »</button>
            </div>
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
                    <h5 class="modal-title">Plan Your Meal</h5>
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
                                    <label class="form-label">Select Recipe</label>
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
                                <div class="text-muted text-center mb-3">- OR -</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Custom Meal</label>
                                    <input type="text" class="form-control" id="customMeal" name="custom_meal_name" placeholder="Enter custom meal name">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="mealNotes" name="custom_meal_description" rows="3" placeholder="Add preparation notes..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btnDelete" style="display: none;">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnSave">Save Meal</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Picker Modal -->
    <div class="modal fade" id="datePickerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Jump to Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="date" class="form-control" id="targetDate" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btnJumpToDate">Go to Date</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    let currentStartDate;
    let currentMainDate;
    let mealData = {};

    function initializeCalendar(startDate, mainDate = null) {
        currentStartDate = new Date(startDate);
        const endDate = new Date(currentStartDate);
        endDate.setDate(currentStartDate.getDate() + 6);

        if (mainDate) {
            currentMainDate = new Date(mainDate);
        } else {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (today >= currentStartDate && today <= endDate) {
                currentMainDate = new Date(today);
            } else {
                currentMainDate = new Date(currentStartDate);
            }
        }

        document.getElementById('current-week-range').textContent = `${formatDate(currentStartDate)} - ${formatDate(endDate)}`;

        fetch(`fetch_meal_plans.php?start=${currentStartDate.toISOString().split('T')[0]}&end=${endDate.toISOString().split('T')[0]}`)
            .then(response => response.json())
            .then(meals => {
                mealData = processMeals(meals);
                renderCompactView(mealData);
                renderMainDay(mealData);
            });
    }

    function processMeals(meals) {
        const mealData = {};
        meals.forEach(meal => {
            if (!mealData[meal.date]) {
                mealData[meal.date] = {};
            }
            if (!mealData[meal.date][meal.time_slot]) {
                mealData[meal.date][meal.time_slot] = [];
            }
            mealData[meal.date][meal.time_slot].push(meal);
        });
        return mealData;
    }

    function renderCompactView(mealData) {
        const grid = document.getElementById('compact-weekly-view');
        grid.innerHTML = '';

        for (let i = 0; i < 7; i++) {
            const date = new Date(currentStartDate);
            date.setDate(currentStartDate.getDate() + i);
            const dateStr = date.toISOString().split('T')[0];
            const defaultSlots = { Breakfast: [], Lunch: [], Dinner: [] };
            const dayMeals = { ...defaultSlots, ...(mealData[dateStr] || {}) };
            const isActive = dateStr === currentMainDate.toISOString().split('T')[0];

            grid.innerHTML += `
                <div class="col-4">
                    <div class="compact-day p-2 ${isActive ? 'active' : ''}" data-date="${dateStr}">
                        <h6>${date.getDate()} ${monthNames[date.getMonth()]}</h6>
                        ${['Breakfast', 'Lunch', 'Dinner'].map(slot => {
                            const meals = dayMeals[slot];
                            const firstMeal = meals[0];
                            return `
                                <div class="compact-meal-slot">
                                    ${firstMeal ? 
                                        `${firstMeal.recipe_title || firstMeal.custom_meal_name}${meals.length > 1 ? ` <span class="badge bg-secondary">+${meals.length - 1}</span>` : ''}` 
                                        : `<span class="text-muted">Add ${slot}</span>`}
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
        }

        document.querySelectorAll('.compact-day').forEach(day => {
            day.addEventListener('click', function() {
                currentMainDate = new Date(this.dataset.date);
                renderMainDay(mealData);
                document.querySelectorAll('.compact-day').forEach(d => d.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    function renderMainDay(mealData) {
        const mainDisplay = document.getElementById('main-day-display');
        const dateStr = currentMainDate.toISOString().split('T')[0];
        const defaultSlots = { Breakfast: [], Lunch: [], Dinner: [] };
        const dayMeals = { ...defaultSlots, ...(mealData[dateStr] || {}) };

        mainDisplay.innerHTML = `
            <div class="meal-day p-4">
                <h3>${formatDate(currentMainDate)}</h3>
                ${['Breakfast', 'Lunch', 'Dinner'].map(slot => `
                    <div class="meal-slot mb-4" data-date="${dateStr}" data-slot="${slot}">
                        <h5>${slot}</h5>
                        <div class="meal-list">
                            ${dayMeals[slot].map(meal => `
                                <div class="meal-item">
                                    ${meal.image_url ? `<img src="/ServerSide/serversideee/uploads/${meal.image_url}" alt="${meal.recipe_title}" class="img-fluid mb-2" style="max-height: 100px;">` : ''}
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>${meal.recipe_title || meal.custom_meal_name}</strong>
                                            ${meal.custom_meal_description ? `<div class="text-muted small">${meal.custom_meal_description}</div>` : ''}
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary me-1" onclick="editMeal(${meal.meal_plan_id})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteMeal(${meal.meal_plan_id})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        <button class="btn btn-sm btn-primary mt-2" onclick="addMeal('${dateStr}', '${slot}')">Add Meal</button>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function addMeal(date, slot) {
        document.getElementById('mealDate').value = date;
        document.getElementById('mealSlot').value = slot;
        document.getElementById('mealPlanId').value = '';
        document.getElementById('recipeSelect').value = '';
        document.getElementById('customMeal').value = '';
        document.getElementById('mealNotes').value = '';
        document.getElementById('btnDelete').style.display = 'none';
        const modal = new bootstrap.Modal(document.getElementById('mealModal'));
        modal.show();
    }

    function editMeal(mealId) {
        fetch(`fetch_meal_plans.php?meal_id=${mealId}`)
            .then(response => response.json())
            .then(meal => {
                document.getElementById('mealDate').value = meal.date;
                document.getElementById('mealSlot').value = meal.time_slot;
                document.getElementById('mealPlanId').value = meal.meal_plan_id;
                document.getElementById('recipeSelect').value = meal.recipe_id || '';
                document.getElementById('customMeal').value = meal.custom_meal_name || '';
                document.getElementById('mealNotes').value = meal.custom_meal_description || '';
                document.getElementById('btnDelete').style.display = 'block';
                const modal = new bootstrap.Modal(document.getElementById('mealModal'));
                modal.show();
            });
    }

    function saveMeal() {
        const formData = {
            meal_plan_id: document.getElementById('mealPlanId').value,
            date: document.getElementById('mealDate').value,
            time_slot: document.getElementById('mealSlot').value,
            recipe_id: document.getElementById('recipeSelect').value,
            custom_meal_name: document.getElementById('customMeal').value,
            custom_meal_description: document.getElementById('mealNotes').value
        };

        fetch('save_meal_plan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                initializeCalendar(currentStartDate, currentMainDate);
                bootstrap.Modal.getInstance(document.getElementById('mealModal')).hide();
            } else {
                alert('Error saving meal');
            }
        });
    }

    function deleteMeal(mealId) {
        if (confirm('Are you sure you want to delete this meal?')) {
            fetch('delete_meal_plan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ meal_id: mealId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    initializeCalendar(currentStartDate, currentMainDate);
                } else {
                    alert('Error deleting meal');
                }
            });
        }
    }

    function navigateWeek(days) {
        currentStartDate.setDate(currentStartDate.getDate() + days);
        initializeCalendar(currentStartDate);
    }

    function jumpToDate() {
        const targetDate = new Date(document.getElementById('targetDate').value);
        initializeCalendar(targetDate, targetDate);
        bootstrap.Modal.getInstance(document.getElementById('datePickerModal')).hide();
    }

    function formatDate(date) {
        return `${date.getDate()} ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
    }

    document.getElementById('prev-week').addEventListener('click', () => navigateWeek(-7));
    document.getElementById('next-week').addEventListener('click', () => navigateWeek(7));
    document.getElementById('btnJumpToDate').addEventListener('click', jumpToDate);
    document.getElementById('btnSave').addEventListener('click', saveMeal);
    document.getElementById('btnDelete').addEventListener('click', () => deleteMeal(document.getElementById('mealPlanId').value));

    initializeCalendar(new Date());
    </script>
</body>
</html>