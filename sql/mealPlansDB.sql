-- Create Database
DROP DATABASE IF EXISTS MealPlansDB;
CREATE DATABASE MealPlansDB;
USE MealPlansDB;

-- Create MealPlans Table
CREATE TABLE MealPlans (
    meal_plan_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    time_slot ENUM('Breakfast', 'Lunch', 'Dinner', 'Snack') NOT NULL,
    recipe_id INT NULL,
    custom_meal_name VARCHAR(255) NULL,
    custom_meal_description TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES usersDB.users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES RecipeDB.Recipes(recipe_id) ON DELETE SET NULL
);

INSERT INTO MealPlans (user_id, date, time_slot, recipe_id, custom_meal_name, custom_meal_description)
VALUES
  (1, '2025-04-14', 'Breakfast', 1, NULL, 'Fluffy pancakes with maple syrup'),
  (1, '2025-04-14', 'Lunch', 2, NULL, 'Grilled chicken salad with avocado'),
  (1, '2025-04-14', 'Dinner', NULL, 'Homemade Pasta', 'Pasta with a rich tomato-basil sauce'),
  (1, '2025-04-15', 'Snack', NULL, 'Fruit Bowl', 'Seasonal mixed fruits for a light snack'),
  (2, '2025-04-15', 'Breakfast', 3, NULL, 'Veggie omelette with toast'),
  (2, '2025-04-15', 'Lunch', NULL, 'Quinoa Salad', 'A refreshing quinoa salad with cucumbers and tomatoes');