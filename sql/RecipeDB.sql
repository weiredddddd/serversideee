-- Drop and recreate database
DROP DATABASE IF EXISTS RecipeDB;
CREATE DATABASE RecipeDB;
USE RecipeDB;

-- Ingredients Table
CREATE TABLE Ingredients (
  ingredient_id INT(11) NOT NULL AUTO_INCREMENT,
  ingredient_name VARCHAR(255) NOT NULL UNIQUE,
  PRIMARY KEY (ingredient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Recipes Table
CREATE TABLE Recipes (
  recipe_id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  category VARCHAR(100) DEFAULT NULL,
  cuisine_type VARCHAR(50) DEFAULT NULL,
  spice_level TINYINT(4) DEFAULT 0,
  image_url VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by INT(11) DEFAULT NULL,
  updated_by INT(11) DEFAULT NULL,
  PRIMARY KEY (recipe_id),
  FOREIGN KEY (user_id) REFERENCES usersDB.Users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES usersDB.Users(user_id) ON DELETE SET NULL,
  FOREIGN KEY (updated_by) REFERENCES usersDB.Users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Recipe_Ingredient Table
CREATE TABLE Recipe_Ingredient (
  recipe_id INT(11) NOT NULL,
  ingredient_id INT(11) NOT NULL,
  quantity VARCHAR(50) DEFAULT NULL,
  unit VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (recipe_id, ingredient_id),
  FOREIGN KEY (recipe_id) REFERENCES Recipes(recipe_id) ON DELETE CASCADE,
  FOREIGN KEY (ingredient_id) REFERENCES Ingredients(ingredient_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Steps Table
CREATE TABLE Steps (
  step_id INT(11) NOT NULL AUTO_INCREMENT,
  recipe_id INT(11) NOT NULL,
  step_no INT(11) NOT NULL,
  description TEXT NOT NULL,
  image_url VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (step_id),
  FOREIGN KEY (recipe_id) REFERENCES Recipes(recipe_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert Ingredients
INSERT INTO Ingredients (ingredient_id, ingredient_name) VALUES
(1, 'Spaghetti'),
(2, 'Bacon'),
(3, 'Eggs'),
(4, 'Parmesan Cheese'),
(5, 'Chicken'),
(6, 'Curry Powder'),
(7, 'Coconut Milk'),
(8, 'Onion'),
(9, 'Garlic'),
(10, 'Flour'),
(11, 'Sugar'),
(12, 'Cocoa Powder'),
(13, 'Butter');

-- Insert Recipes
INSERT INTO Recipes (recipe_id, user_id, title, description, category, cuisine_type, spice_level, image_url, created_by, updated_by) VALUES
(1, 1, 'Spaghetti Carbonara', 'A classic Italian pasta dish with creamy sauce.', 'Pasta', NULL, 0, 'carbonara.jpg', 1, 1),
(2, 2, 'Chicken Curry', 'Spicy and rich curry with tender chicken pieces.', 'Curry', NULL, 0, 'chicken_curry.jpg', 2, 2),
(3, 3, 'Chocolate Cake', 'A rich and moist chocolate cake.', 'Dessert', NULL, 0, 'chocolate_cake.jpg', 3, 3);

-- Insert Recipe_Ingredients
INSERT INTO Recipe_Ingredient (recipe_id, ingredient_id, quantity, unit) VALUES
(1, 1, '200', 'grams'),
(1, 2, '100', 'grams'),
(1, 3, '2', 'pieces'),
(1, 4, '50', 'grams'),
(2, 5, '500', 'grams'),
(2, 6, '2', 'tablespoons'),
(2, 7, '200', 'ml'),
(2, 8, '1', 'piece'),
(2, 9, '2', 'cloves'),
(3, 10, '200', 'grams'),
(3, 11, '150', 'grams'),
(3, 12, '50', 'grams'),
(3, 13, '100', 'grams');

-- Insert Steps
INSERT INTO Steps (step_id, recipe_id, step_no, description, image_url) VALUES
(1, 1, 1, 'Boil spaghetti until al dente.', NULL),
(2, 1, 2, 'Fry bacon until crispy.', NULL),
(3, 1, 3, 'Mix eggs and cheese, combine with pasta and bacon.', NULL);
