const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const shortDayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
let currentStartDate;
let currentMainDate;
let mealData = {};
let animationsEnabled = true;

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

    // Animate the week range text
    const weekRangeElement = document.getElementById('current-week-range');
    if (animationsEnabled) {
        weekRangeElement.style.opacity = '0';
        setTimeout(() => {
            weekRangeElement.textContent = `${formatDate(currentStartDate)} - ${formatDate(endDate)}`;
            weekRangeElement.style.opacity = '1';
        }, 300);
    } else {
        weekRangeElement.textContent = `${formatDate(currentStartDate)} - ${formatDate(endDate)}`;
    }

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
    
    // Create a temporary container for the new content
    const tempContainer = document.createElement('div');
    tempContainer.className = 'row g-2';
    
    for (let i = 0; i < 7; i++) {
        const date = new Date(currentStartDate);
        date.setDate(currentStartDate.getDate() + i);
        const dateStr = date.toISOString().split('T')[0];
        const defaultSlots = { Breakfast: [], Lunch: [], Dinner: [] };
        const dayMeals = { ...defaultSlots, ...(mealData[dateStr] || {}) };
        const isActive = dateStr === currentMainDate.toISOString().split('T')[0];
        const isToday = dateStr === new Date().toISOString().split('T')[0];

        const dayCol = document.createElement('div');
        dayCol.className = 'col-4';
        dayCol.innerHTML = `
            <div class="compact-day p-2 ${isActive ? 'active' : ''} ${isToday ? 'today' : ''}" data-date="${dateStr}">
                <h6>${shortDayNames[date.getDay()]} ${date.getDate()} ${monthNames[date.getMonth()]}</h6>
                ${['Breakfast', 'Lunch', 'Dinner'].map(slot => {
                    const meals = dayMeals[slot];
                    const firstMeal = meals[0];
                    let icon = '';
                    if (slot === 'Breakfast') icon = '<i class="bi bi-cup-hot me-1"></i>';
                    if (slot === 'Lunch') icon = '<i class="bi bi-egg-fried me-1"></i>';
                    if (slot === 'Dinner') icon = '<i class="bi bi-moon me-1"></i>';
                    
                    return `
                        <div class="compact-meal-slot">
                            ${icon}
                            ${firstMeal ? 
                                `${firstMeal.recipe_title || firstMeal.custom_meal_name}${meals.length > 1 ? ` <span class="badge rounded-pill">${meals.length}</span>` : ''}` 
                                : `<span class="text-muted">Add ${slot}</span>`}
                        </div>
                    `;
                }).join('')}
            </div>
        `;
        tempContainer.appendChild(dayCol);
    }
    
    // Apply animation when replacing content
    if (animationsEnabled) {
        grid.style.opacity = '0';
        setTimeout(() => {
            grid.innerHTML = tempContainer.innerHTML;
            grid.style.opacity = '1';
            
            // Add event listeners to the new elements
            addCompactDayEventListeners();
        }, 300);
    } else {
        grid.innerHTML = tempContainer.innerHTML;
        addCompactDayEventListeners();
    }
}

function addCompactDayEventListeners() {
    document.querySelectorAll('.compact-day').forEach(day => {
        day.addEventListener('click', function() {
            const previousActive = document.querySelector('.compact-day.active');
            if (previousActive) {
                previousActive.classList.remove('active');
            }
            
            this.classList.add('active');
            currentMainDate = new Date(this.dataset.date);
            
            // Animate the transition
            const mainDisplay = document.getElementById('main-day-display');
            if (animationsEnabled) {
                mainDisplay.style.opacity = '0';
                mainDisplay.style.transform = 'translateY(10px)';
                
                setTimeout(() => {
                    renderMainDay(mealData);
                    mainDisplay.style.opacity = '1';
                    mainDisplay.style.transform = 'translateY(0)';
                }, 300);
            } else {
                renderMainDay(mealData);
            }
        });
    });
}

function renderMainDay(mealData) {
    const mainDisplay = document.getElementById('main-day-display');
    const dateStr = currentMainDate.toISOString().split('T')[0];
    const defaultSlots = { Breakfast: [], Lunch: [], Dinner: [] };
    const dayMeals = { ...defaultSlots, ...(mealData[dateStr] || {}) };
    const isToday = dateStr === new Date().toISOString().split('T')[0];

    // Create meal day content
    let mealDayContent = `
        <div class="meal-day p-4">
            <h3>${dayNames[currentMainDate.getDay()]}, ${formatDate(currentMainDate)} ${isToday ? '<span class="badge bg-primary">Today</span>' : ''}</h3>
    `;

    // Add meal slots
    ['Breakfast', 'Lunch', 'Dinner'].forEach((slot, index) => {
        let icon = '';
        let bgColor = '';
        
        if (slot === 'Breakfast') {
            icon = '<i class="bi bi-cup-hot me-2"></i>';
            bgColor = 'rgba(249, 115, 22, 0.1)';
        } else if (slot === 'Lunch') {
            icon = '<i class="bi bi-egg-fried me-2"></i>';
            bgColor = 'rgba(16, 185, 129, 0.1)';
        } else if (slot === 'Dinner') {
            icon = '<i class="bi bi-moon me-2"></i>';
            bgColor = 'rgba(79, 70, 229, 0.1)';
        }
        
        mealDayContent += `
            <div class="meal-slot mb-4" data-date="${dateStr}" data-slot="${slot}" style="border-left: 4px solid ${bgColor.replace('0.1', '1')}; background-color: ${bgColor}">
                <h5>${icon}${slot}</h5>
                <div class="meal-list">
        `;
        
        // Add meals or empty state
        if (dayMeals[slot].length === 0) {
            mealDayContent += `
                <div class="empty-slot-message">
                    <i class="bi bi-plus-circle me-2"></i>No meals planned for ${slot.toLowerCase()}
                </div>
            `;
        } else {
            dayMeals[slot].forEach((meal, mealIndex) => {
                const delay = 0.1 * mealIndex;
                mealDayContent += `
                    <div class="meal-item" style="animation-delay: ${delay}s">
                        ${meal.image_url ? `<img src="/ServerSide/serversideee/uploads/${meal.image_url}" alt="${meal.recipe_title}" class="img-fluid mb-2">` : ''}
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${meal.recipe_title || meal.custom_meal_name}</strong>
                                ${meal.custom_meal_description ? `<div class="text-muted small">${meal.custom_meal_description}</div>` : ''}
                            </div>
                            <div class="meal-actions">
                                <button class="btn btn-icon btn-edit me-1" onclick="editMeal(${meal.meal_plan_id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-icon btn-delete" onclick="deleteMeal(${meal.meal_plan_id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        
        mealDayContent += `
                </div>
                <button class="btn btn-add-meal mt-2" onclick="addMeal('${dateStr}', '${slot}')">
                    <i class="bi bi-plus-circle me-1"></i>Add Meal
                </button>
            </div>
        `;
    });
    
    mealDayContent += `</div>`;
    
    // Apply animation when replacing content
    if (animationsEnabled) {
        mainDisplay.innerHTML = mealDayContent;
        mainDisplay.style.opacity = '1';
        mainDisplay.style.transform = 'translateY(0)';
    } else {
        mainDisplay.innerHTML = mealDayContent;
    }
}

function addMeal(date, slot) {
    document.getElementById('mealDate').value = date;
    document.getElementById('mealSlot').value = slot;
    document.getElementById('mealPlanId').value = '';
    document.getElementById('recipeSelect').value = '';
    document.getElementById('customMeal').value = '';
    document.getElementById('mealNotes').value = '';
    document.getElementById('btnDelete').style.display = 'none';
    
    const modalTitle = document.querySelector('#mealModal .modal-title');
    modalTitle.innerHTML = `<i class="bi bi-calendar-plus me-2"></i>Add ${slot} for ${dayNames[new Date(date).getDay()]}, ${formatDate(new Date(date))}`;
    
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
            
            const modalTitle = document.querySelector('#mealModal .modal-title');
            modalTitle.innerHTML = `<i class="bi bi-pencil-square me-2"></i>Edit ${meal.time_slot} for ${dayNames[new Date(meal.date).getDay()]}, ${formatDate(new Date(meal.date))}`;
            
            const modal = new bootstrap.Modal(document.getElementById('mealModal'));
            modal.show();
        });
}

function saveMeal() {
    // Add a small animation to the save button
    const saveButton = document.getElementById('btnSave');
    saveButton.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...';
    saveButton.disabled = true;
    
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
            // Reset button state
            saveButton.innerHTML = '<i class="bi bi-check-circle me-1"></i>Save Meal';
            saveButton.disabled = false;
            
            // Close modal with animation
            const modalElement = document.getElementById('mealModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            
            // Add success feedback
            const modalBody = modalElement.querySelector('.modal-body');
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success mt-3';
            successAlert.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Meal saved successfully!';
            modalBody.appendChild(successAlert);
            
            setTimeout(() => {
                successAlert.remove();
                modal.hide();
                initializeCalendar(currentStartDate, currentMainDate);
            }, 1000);
        } else {
            alert('Error saving meal');
            saveButton.innerHTML = '<i class="bi bi-check-circle me-1"></i>Save Meal';
            saveButton.disabled = false;
        }
    });
}

function deleteMeal(mealId) {
    // Use a more stylish confirmation dialog
    if (confirm('Are you sure you want to delete this meal?')) {
        const affectedElement = mealId ? 
            document.querySelector(`.meal-item:has(button[onclick="editMeal(${mealId})"])`) : 
            null;
        
        if (affectedElement && animationsEnabled) {
            // Add delete animation
            affectedElement.style.opacity = '0.5';
            affectedElement.style.transform = 'translateX(10px)';
        }
        
        fetch('delete_meal_plan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ meal_id: mealId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (affectedElement && animationsEnabled) {
                    affectedElement.style.height = `${affectedElement.offsetHeight}px`;
                    affectedElement.style.marginTop = '0';
                    affectedElement.style.marginBottom = '0';
                    affectedElement.style.padding = '0';
                    affectedElement.style.overflow = 'hidden';
                    
                    setTimeout(() => {
                        affectedElement.style.height = '0';
                        setTimeout(() => {
                            initializeCalendar(currentStartDate, currentMainDate);
                        }, 300);
                    }, 100);
                } else {
                    initializeCalendar(currentStartDate, currentMainDate);
                }
                
                // Close modal if open
                const modal = bootstrap.Modal.getInstance(document.getElementById('mealModal'));
                if (modal) modal.hide();
            } else {
                alert('Error deleting meal');
                if (affectedElement) {
                    affectedElement.style.opacity = '1';
                    affectedElement.style.transform = 'translateX(0)';
                }
            }
        });
    }
}

function navigateWeek(days) {
    // Animate the navigation buttons
    const button = days < 0 ? document.getElementById('prev-week') : document.getElementById('next-week');
    button.classList.add('animate-pulse');
    
    setTimeout(() => {
        button.classList.remove('animate-pulse');
        currentStartDate.setDate(currentStartDate.getDate() + days);
        
        // Slide animation for week change
        const mainDisplay = document.getElementById('main-day-display');
        const compactView = document.getElementById('compact-weekly-view');
        
        if (animationsEnabled) {
            const direction = days < 0 ? '100px' : '-100px';
            
            mainDisplay.style.opacity = '0';
            mainDisplay.style.transform = `translateX(${direction})`;
            compactView.style.opacity = '0';
            
            setTimeout(() => {
                initializeCalendar(currentStartDate);
                
                setTimeout(() => {
                    mainDisplay.style.opacity = '1';
                    mainDisplay.style.transform = 'translateX(0)';
                    compactView.style.opacity = '1';
                }, 50);
            }, 300);
        } else {
            initializeCalendar(currentStartDate);
        }
    }, 300);
}

function jumpToDate() {
    const targetDate = new Date(document.getElementById('targetDate').value);
    
    // Add animation for date jump
    const mainDisplay = document.getElementById('main-day-display');
    const compactView = document.getElementById('compact-weekly-view');
    
    if (animationsEnabled) {
        mainDisplay.style.opacity = '0';
        mainDisplay.style.transform = 'scale(0.95)';
        compactView.style.opacity = '0';
        
        setTimeout(() => {
            initializeCalendar(targetDate, targetDate);
            
            setTimeout(() => {
                mainDisplay.style.opacity = '1';
                mainDisplay.style.transform = 'scale(1)';
                compactView.style.opacity = '1';
            }, 50);
        }, 300);
    } else {
        initializeCalendar(targetDate, targetDate);
    }
    
    bootstrap.Modal.getInstance(document.getElementById('datePickerModal')).hide();
}

function formatDate(date) {
    return `${date.getDate()} ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
}

// Event listeners
document.getElementById('prev-week').addEventListener('click', () => navigateWeek(-7));
document.getElementById('next-week').addEventListener('click', () => navigateWeek(7));
document.getElementById('btnJumpToDate').addEventListener('click', jumpToDate);
document.getElementById('btnSave').addEventListener('click', saveMeal);
document.getElementById('btnDelete').addEventListener('click', () => deleteMeal(document.getElementById('mealPlanId').value));

// Initialize calendar
document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar(new Date());
});