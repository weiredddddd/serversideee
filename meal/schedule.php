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
        .meal-slot { 
            min-height: 120px; border: 2px dashed #dee2e6; 
            cursor: pointer; transition: all 0.2s;
        }
        .meal-slot:hover { background: #e9ecef; border-color: #adb5bd; }
        .meal-item { background: white; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .calendar-day.active { background: #e7f1ff; border-color: #86b7fe; }
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
                <button class="btn btn-outline-primary" id="prev-week">&laquo; Previous Week</button>
                <h4 id="current-week-range" class="text-center"></h4>
                <button class="btn btn-outline-primary" id="next-week">Next Week &raquo;</button>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="row g-3" id="calendar-grid">
            <!-- Days will be dynamically inserted here -->
        </div>
    </div>

    <!-- Add Meal Modal -->
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
                                    <input type="text" class="form-control" id="customMeal" name="custom_meal_name" 
                                           placeholder="Enter custom meal name">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="mealNotes" name="custom_meal_description" 
                                      rows="3" placeholder="Add preparation notes..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btnDelete">Delete</button>
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
                    <div class="mb-3">
                        <input type="date" class="form-control" id="targetDate" 
                               min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btnJumpToDate">Go to Date</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                       'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    let currentStartDate;
    
    function initializeCalendar(startDate) {
        currentStartDate = new Date(startDate);
        
        // Set week range display
        const endDate = new Date(currentStartDate);
        endDate.setDate(currentStartDate.getDate() + 6);
        document.getElementById('current-week-range').textContent = 
            `${formatDate(currentStartDate)} - ${formatDate(endDate)}`;
        
        // Fetch meals for the week
        fetch(`fetch_meal_plans.php?start=${currentStartDate.toISOString().split('T')[0]}&end=${endDate.toISOString().split('T')[0]}`)
            .then(response => response.json())
            .then(meals => {
                renderCalendarGrid(meals);
            });
    }

    function renderCalendarGrid(meals) {
        const grid = document.getElementById('calendar-grid');
        grid.innerHTML = '';
        
        for(let i = 0; i < 7; i++) {
            const date = new Date(currentStartDate);
            date.setDate(currentStartDate.getDate() + i);
            
            const dayMeals = meals.filter(m => m.date === date.toISOString().split('T')[0]);
            
            grid.innerHTML += `
                <div class="col">
                    <div class="meal-day p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">${date.getDate()} ${monthNames[date.getMonth()]}</h5>
                            <small class="text-muted">${weekDays[date.getDay()]}</small>
                        </div>
                        ${['Breakfast', 'Lunch', 'Dinner'].map(slot => {
                            const meal = dayMeals.find(m => m.time_slot === slot);
                            return `
                                <div class="meal-slot p-2 mb-2" 
                                     data-date="${date.toISOString().split('T')[0]}" 
                                     data-slot="${slot}"
                                     data-meal-id="${meal?.meal_plan_id || ''}">
                                    ${meal ? renderMealItem(meal) : `
                                        <div class="text-center text-muted py-2">
                                            <i class="bi bi-plus-lg"></i> Add ${slot}
                                        </div>
                                    `}
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
        }
            
            // Attach click handlers
            document.querySelectorAll('.meal-slot').forEach(slot => {
                slot.addEventListener('click', function() {
                    const date = this.dataset.date;
                    const timeSlot = this.dataset.slot;
                    const mealId = this.dataset.mealId;
                    
                    document.getElementById('mealDate').value = date;
                    document.getElementById('mealSlot').value = timeSlot;
                    document.getElementById('mealPlanId').value = mealId || '';
                    
                    if(mealId) {
                        fetch(`fetch_meal_plans.php?meal_id=${mealId}`)
                            .then(r => r.json())
                            .then(meal => {
                                document.getElementById('recipeSelect').value = meal.recipe_id || '';
                                document.getElementById('customMeal').value = meal.custom_meal_name || '';
                                document.getElementById('mealNotes').value = meal.custom_meal_description || '';
                            });
                    }
                    
                    document.getElementById('btnDelete').style.display = mealId ? 'block' : 'none';
                    new bootstrap.Modal(document.getElementById('mealModal')).show();
                });
            });
        }

        function renderMealItem(meal) {
            return `
                <div class="meal-item p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${meal.recipe_title || meal.custom_meal_name}</strong>
                            ${meal.custom_meal_description ? `
                                <div class="text-muted small mt-1">${meal.custom_meal_description}</div>
                            ` : ''}
                        </div>
                        <button class="btn btn-sm btn-outline-danger" 
                                onclick="event.stopPropagation(); deleteMeal(${meal.meal_plan_id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        }

        // Event Listeners
        document.getElementById('btnSave').addEventListener('click', saveMeal);
        document.getElementById('btnDelete').addEventListener('click', deleteMeal);
        document.getElementById('prev-week').addEventListener('click', () => navigateWeek(-7));
        document.getElementById('next-week').addEventListener('click', () => navigateWeek(7));
        document.getElementById('btnJumpToDate').addEventListener('click', jumpToDate);

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
                if(data.success) {
                    initializeCalendar(currentStartDate);
                    bootstrap.Modal.getInstance(document.getElementById('mealModal')).hide();
                }
            });
        }

        function deleteMeal(mealId) {
            if(!confirm('Are you sure?')) return;
            
            fetch('delete_meal_plan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ meal_id: mealId })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) initializeCalendar(currentStartDate);
            });
        }

        function navigateWeek(days) {
            currentStartDate.setDate(currentStartDate.getDate() + days);
            initializeCalendar(currentStartDate);
        }

        function jumpToDate() {
            const targetDate = new Date(document.getElementById('targetDate').value);
            initializeCalendar(targetDate);
            bootstrap.Modal.getInstance(document.getElementById('datePickerModal')).hide();
        }

        function formatDate(date) {
            return `${date.getDate()} ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
        }

        // Initialize with current week
        initializeCalendar(new Date());
    </script>
</body>
</html>