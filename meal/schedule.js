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
    const tempContainer = document.createElement('div');
    tempContainer.className = 'row g-2';
    
    const nutrientThresholds = {
        calories: { min: 2000, max: 2500 },
        carbs: { min: 225, max: 300 },
        protein: { min: 60, max: 150, high: 120 },
        fat: { min: 44, max: 78 }
    };
    
    for (let i = 0; i < 7; i++) {
        const date = new Date(currentStartDate);
        date.setDate(currentStartDate.getDate() + i);
        const dateStr = date.toISOString().split('T')[0];
        const defaultSlots = { Breakfast: [], Lunch: [], Dinner: [] };
        const dayMeals = { ...defaultSlots, ...(mealData[dateStr] || {}) };
        const isActive = dateStr === currentMainDate.toISOString().split('T')[0];
        const isToday = dateStr === new Date().toISOString().split('T')[0];
        
        // Calculate daily totals for all nutrients
        const dailyTotals = {
            calories: 0,
            fat: 0,
            carbs: 0,
            protein: 0
        };
        
        const allMeals = [...dayMeals.Breakfast, ...dayMeals.Lunch, ...dayMeals.Dinner];
        
        allMeals.forEach(meal => {
            const isRecipe = !!meal.recipe_id;
            const nutrition = isRecipe ? {
                calories: parseInt(meal.calories || 0),
                fat: parseFloat(meal.fat || 0),
                carbs: parseFloat(meal.carbs || 0),
                protein: parseFloat(meal.protein || 0)
            } : {
                calories: parseInt(meal.custom_calories || 0),
                fat: parseFloat(meal.custom_fat || 0),
                carbs: parseFloat(meal.custom_carbs || 0),
                protein: parseFloat(meal.custom_protein || 0)
            };
            
            dailyTotals.calories += nutrition.calories;
            dailyTotals.fat += nutrition.fat;
            dailyTotals.carbs += nutrition.carbs;
            dailyTotals.protein += nutrition.protein;
        });
        
        // Determine nutrition status class based on meter logic
        let statusClass = '';
        if (allMeals.length > 0) {
            const anyExcessive = 
                dailyTotals.calories > nutrientThresholds.calories.max ||
                dailyTotals.carbs > nutrientThresholds.carbs.max ||
                dailyTotals.protein > nutrientThresholds.protein.max ||
                dailyTotals.fat > nutrientThresholds.fat.max;
            
            const allSufficient = 
                dailyTotals.calories >= nutrientThresholds.calories.min &&
                dailyTotals.carbs >= nutrientThresholds.carbs.min &&
                dailyTotals.protein >= nutrientThresholds.protein.min &&
                dailyTotals.fat >= nutrientThresholds.fat.min;
            
            const highProtein = dailyTotals.protein >= nutrientThresholds.protein.high;
            
            if (anyExcessive) {
                statusClass = 'nutrition-cheat'; // Purple
            } else if (allSufficient && highProtein) {
                statusClass = 'nutrition-muscle'; // Yellow
            } else if (allSufficient) {
                statusClass = 'nutrition-balanced'; // Green
            } else {
                statusClass = 'nutrition-insufficient'; // Red
            }
        }
        
        const dayCol = document.createElement('div');
        dayCol.className = 'col-4';
        dayCol.innerHTML = `
            <div class="compact-day p-2 ${isActive ? 'active' : ''} ${isToday ? 'today' : ''} ${statusClass}" data-date="${dateStr}">
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
    
    if (animationsEnabled) {
        grid.style.opacity = '0';
        setTimeout(() => {
            grid.innerHTML = tempContainer.innerHTML;
            grid.style.opacity = '1';
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
  
    // Calculate daily totals
    let dailyTotals = {
      calories: 0,
      fat: 0,
      carbs: 0,
      protein: 0
    };
  
    ['Breakfast', 'Lunch', 'Dinner'].forEach(slot => {
      const slotMeals = dayMeals[slot] || [];
      slotMeals.forEach(meal => {
        const isRecipe = !!meal.recipe_id;
        const nutrition = isRecipe ? {
          calories: parseInt(meal.calories || 0),
          fat: parseFloat(meal.fat || 0),
          carbs: parseFloat(meal.carbs || 0),
          protein: parseFloat(meal.protein || 0)
        } : {
          calories: parseInt(meal.custom_calories || 0),
          fat: parseFloat(meal.custom_fat || 0),
          carbs: parseFloat(meal.custom_carbs || 0),
          protein: parseFloat(meal.custom_protein || 0)
        };
        
        dailyTotals.calories += nutrition.calories;
        dailyTotals.fat += nutrition.fat;
        dailyTotals.carbs += nutrition.carbs;
        dailyTotals.protein += nutrition.protein;
      });
    });
  
    // Round values to 1 decimal place
    dailyTotals.fat = Math.round(dailyTotals.fat * 10) / 10;
    dailyTotals.carbs = Math.round(dailyTotals.carbs * 10) / 10;
    dailyTotals.protein = Math.round(dailyTotals.protein * 10) / 10;
  
    // Start building the HTML content
    let mealDayContent = `
      <div class="meal-day p-4">
        <h3>${dayNames[currentMainDate.getDay()]}, ${formatDate(currentMainDate)} ${isToday ? '<span class="badge bg-primary">Today</span>' : ''}</h3>
        <div id="nutrition-meter-container" class="mb-4"></div>
    `;
  
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
  
      const totalNutrition = dayMeals[slot].reduce(
        (acc, meal) => {
          const isRecipe = !!meal.recipe_id;
          const nutrition = isRecipe
            ? {
                calories: parseInt(meal.calories || 0),
                fat: parseFloat(meal.fat || 0),
                carbs: parseFloat(meal.carbs || 0),
                protein: parseFloat(meal.protein || 0),
              }
            : {
                calories: parseInt(meal.custom_calories || 0),
                fat: parseFloat(meal.custom_fat || 0),
                carbs: parseFloat(meal.custom_carbs || 0),
                protein: parseFloat(meal.custom_protein || 0),
              };
          acc.calories += nutrition.calories;
          acc.fat += nutrition.fat;
          acc.carbs += nutrition.carbs;
          acc.protein += nutrition.protein;
          return acc;
        },
        { calories: 0, fat: 0, carbs: 0, protein: 0 }
      );
  
      totalNutrition.fat = Math.round(totalNutrition.fat * 10) / 10;
      totalNutrition.carbs = Math.round(totalNutrition.carbs * 10) / 10;
      totalNutrition.protein = Math.round(totalNutrition.protein * 10) / 10;
  
      mealDayContent += `
        <div class="meal-slot mb-4" data-date="${dateStr}" data-slot="${slot}" style="border-left: 4px solid ${bgColor.replace(
        '0.1',
        '1'
      )}; background-color: ${bgColor}">
          <h5>${icon}${slot}</h5>
          <div class="meal-list">
      `;
  
      if (dayMeals[slot].length === 0) {
        mealDayContent += `
          <div class="empty-slot-message">
            <i class="bi bi-plus-circle me-2"></i>No meals planned for ${slot.toLowerCase()}
          </div>
        `;
      } else {
        dayMeals[slot].forEach((meal, mealIndex) => {
          const delay = 0.1 * mealIndex;
          const isRecipe = !!meal.recipe_id;
          const nutrition = isRecipe
            ? {
                calories: meal.calories || 0,
                fat: meal.fat || 0,
                carbs: meal.carbs || 0,
                protein: meal.protein || 0,
              }
            : {
                calories: meal.custom_calories || 0,
                fat: meal.custom_fat || 0,
                carbs: meal.custom_carbs || 0,
                protein: meal.custom_protein || 0,
              };
          mealDayContent += `
            <div class="meal-item" style="animation-delay: ${delay}s">
              ${
                meal.image_url
                  ? `<img src="../uploads/recipe/${meal.image_url}" alt="${meal.recipe_title}" class="img-fluid mb-2">`
                  : ''
              }
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <strong>${meal.recipe_title || meal.custom_meal_name}</strong>
                  <div class="nutrition-info">
                    <span>Calories: ${nutrition.calories}</span>
                    <span>Fat: ${nutrition.fat}g</span>
                    <span>Carbs: ${nutrition.carbs}g</span>
                    <span>Protein: ${nutrition.protein}g</span>
                  </div>
                  ${
                    meal.custom_meal_description
                      ? `<div class="text-muted small">${meal.custom_meal_description}</div>`
                      : ''
                  }
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
          <div class="slot-totals nutrition-info">
            <span>Total: </span>
            <span>Calories: ${totalNutrition.calories}</span>
            <span>Fat: ${totalNutrition.fat}g</span>
            <span>Carbs: ${totalNutrition.carbs}g</span>
            <span>Protein: ${totalNutrition.protein}g</span>
          </div>
          <button class="btn btn-add-meal mt-2" onclick="addMeal('${dateStr}', '${slot}')">
            <i class="bi bi-plus-circle me-1"></i>Add Meal
          </button>
        </div>
      `;
    });
  
    mealDayContent += `</div>`;
  
    mainDisplay.innerHTML = mealDayContent;
    
    // Update the nutrition meter with the daily totals
    if (window.updateNutritionMeter) {
      window.updateNutritionMeter(dailyTotals);
    }
  
    if (animationsEnabled) {
      mainDisplay.style.opacity = '1';
      mainDisplay.style.transform = 'translateY(0)';
    }
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
            if (meal.custom_meal_name) {
                document.getElementById('customNutritionFields').style.display = 'block';
                document.getElementById('customCalories').value = meal.custom_calories || '';
                document.getElementById('customFat').value = meal.custom_fat || '';
                document.getElementById('customCarbs').value = meal.custom_carbs || '';
                document.getElementById('customProtein').value = meal.custom_protein || '';
            } else {
                document.getElementById('customNutritionFields').style.display = 'none';
            }
            document.getElementById('btnDelete').style.display = 'block';
            
            const modalTitle = document.querySelector('#mealModal .modal-title');
            modalTitle.innerHTML = `<i class="bi bi-pencil-square me-2"></i>Edit ${meal.time_slot} for ${dayNames[new Date(meal.date).getDay()]}, ${formatDate(new Date(meal.date))}`;
            
            const modal = new bootstrap.Modal(document.getElementById('mealModal'));
            modal.show();
        });
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

function saveMeal() {
    const saveButton = document.getElementById('btnSave');
    saveButton.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...';
    saveButton.disabled = true;
    
    const formData = {
        meal_plan_id: document.getElementById('mealPlanId').value,
        date: document.getElementById('mealDate').value,
        time_slot: document.getElementById('mealSlot').value,
        recipe_id: document.getElementById('recipeSelect').value,
        custom_meal_name: document.getElementById('customMeal').value,
        custom_meal_description: document.getElementById('mealNotes').value,
        custom_calories: document.getElementById('customCalories').value || 0,
        custom_fat: document.getElementById('customFat').value || 0,
        custom_carbs: document.getElementById('customCarbs').value || 0,
        custom_protein: document.getElementById('customProtein').value || 0
    };

    fetch('save_meal_plan.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            saveButton.innerHTML = '<i class="bi bi-check-circle me-1"></i>Save Meal';
            saveButton.disabled = false;
            
            const modalElement = document.getElementById('mealModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
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
    if (confirm('Are you sure you want to delete this meal?')) {
        const affectedElement = mealId ? 
            document.querySelector(`.meal-item:has(button[onclick="editMeal(${mealId})"])`) : 
            null;
        
        if (affectedElement && animationsEnabled) {
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
    const button = days < 0 ? document.getElementById('prev-week') : document.getElementById('next-week');
    button.classList.add('animate-pulse');
    
    setTimeout(() => {
        button.classList.remove('animate-pulse');
        currentStartDate.setDate(currentStartDate.getDate() + days);
        
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
document.getElementById('btnJumpToDate').addEventListener('click', jumpToDate);
function jumpToDate() {
    const targetDate = new Date(document.getElementById('targetDate').value);
    
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

document.getElementById('customMeal').addEventListener('input', function() {
    const nutritionFields = document.getElementById('customNutritionFields');
    if (this.value.trim() !== '') {
        nutritionFields.style.display = 'block';
    } else {
        nutritionFields.style.display = 'none';
    }
});

document.getElementById('prev-week').addEventListener('click', () => navigateWeek(-7));
document.getElementById('next-week').addEventListener('click', () => navigateWeek(7));
document.getElementById('btnJumpToDate').addEventListener('click', jumpToDate);
document.getElementById('btnSave').addEventListener('click', saveMeal);
document.getElementById('btnDelete').addEventListener('click', () => deleteMeal(document.getElementById('mealPlanId').value));

document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar(new Date());
});