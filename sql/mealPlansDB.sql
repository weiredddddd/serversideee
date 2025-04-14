-- Create Database and Table
DROP DATABASE IF EXISTS MealPlansDB;
CREATE DATABASE MealPlansDB;
USE MealPlansDB;

CREATE TABLE MealPlans (
    meal_plan_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    time_slot ENUM('Breakfast', 'Lunch', 'Dinner', 'Snack') NOT NULL,
    recipe_id INT NULL,
    custom_meal_name VARCHAR(255) NULL,
    custom_meal_description TEXT NULL,
    custom_calories INT DEFAULT 0,
    custom_fat FLOAT DEFAULT 0,
    custom_carbs FLOAT DEFAULT 0,
    custom_protein FLOAT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES usersDB.users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES RecipeDB.Recipes(recipe_id) ON DELETE SET NULL
);

-- Insert dummy data
INSERT INTO MealPlans(user_id,date,time_slot,recipe_id,custom_meal_name,custom_meal_description,custom_calories,custom_fat,custom_carbs,custom_protein)
VALUES
(1,'2025-04-14','Lunch',2,NULL,'Grilled chicken salad with avocado',0,0,0,0),
(1,'2025-04-14','Dinner',NULL,'Homemade Pasta','Pasta with a rich tomato-basil sauce',80,80,80,0),
(1,'2025-04-15','Snack',NULL,'Fruit Bowl','Seasonal mixed fruits for a light snack',0,0,0,0),
(2,'2025-04-15','Breakfast',3,NULL,'Veggie omelette with toast',0,0,0,0),
(2,'2025-04-15','Lunch',NULL,'Quinoa Salad','A refreshing quinoa salad with cucumbers and tomatoes',0,0,0,0),
(7,'2025-04-13','Breakfast',1,NULL,'s',0,0,0,0),
(7,'2025-04-16','Lunch',2,NULL,'s',0,0,0,0),
(7,'2025-04-18','Lunch',3,NULL,NULL,0,0,0,0),
(7,'2025-04-17','Dinner',NULL,'bello',NULL,0,0,0,0),
(7,'2025-04-15','Breakfast',1,NULL,NULL,0,0,0,0),
(1,'2025-04-14','Breakfast',3,NULL,NULL,0,0,0,0),
(1,'2025-04-14','Breakfast',1,NULL,NULL,0,0,0,0),
(1,'0000-00-00','3',NULL,NULL,NULL,0,0,0,0),
(1,'0000-00-00','1',NULL,NULL,NULL,0,0,0,0),
(1,'2025-04-16','Breakfast',1,NULL,'with some soup',0,0,0,0),
(1,'2025-05-04','Breakfast',1,NULL,NULL,0,0,0,0),
(1,'2025-05-04','Breakfast',NULL,'bello',NULL,0,0,0,0),
(1,'2025-05-04','Breakfast',2,NULL,NULL,0,0,0,0),
(1,'2025-05-04','Breakfast',2,NULL,NULL,0,0,0,0),
(1,'2025-04-13','Breakfast',2,NULL,NULL,0,0,0,0),
(1,'2025-04-13','Breakfast',2,NULL,NULL,0,0,0,0),
(1,'2025-04-14','Dinner',2,'bello',NULL,200,0,0,0),
(1,'2025-04-14','Breakfast',1,NULL,NULL,0,0,0,0),
(1,'2025-04-15','Breakfast',NULL,'Boombastic protein',NULL,2000,50,300,60),
(1,'2025-04-17','Breakfast',NULL,'Boombastic protein',NULL,2000,44,300,120);

