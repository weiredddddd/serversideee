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
  view_count INT DEFAULT 0,
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

-- Add Nutrition Table
CREATE TABLE Nutrition (
  nutrition_id INT(11) NOT NULL AUTO_INCREMENT,
  recipe_id INT(11) NOT NULL,
  calories INT DEFAULT NULL,
  fat FLOAT DEFAULT NULL,
  carbs FLOAT DEFAULT NULL,
  protein FLOAT DEFAULT NULL,
  PRIMARY KEY (nutrition_id),
  FOREIGN KEY (recipe_id) REFERENCES Recipes(recipe_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `Ingredients` (`ingredient_id`, `ingredient_name`) VALUES
(18, 'All-purpose flour'),
(2, 'Bacon'),
(19, 'Baking Soda'),
(13, 'Butter'),
(5, 'Chicken'),
(22, 'Chocolate chips'),
(12, 'Cocoa Powder'),
(7, 'Coconut Milk'),
(6, 'Curry Powder'),
(15, 'Dark Brown Sugar'),
(16, 'Egg'),
(3, 'Eggs'),
(10, 'Flour'),
(9, 'Garlic'),
(21, 'Milk'),
(8, 'Onion'),
(4, 'Parmesan Cheese'),
(20, 'Salt'),
(1, 'Spaghetti'),
(11, 'Sugar'),
(14, 'Unsalted butter'),
(17, 'Vanilla Extract');

--
-- Dumping data for table `Recipes`
--

INSERT INTO `Recipes` (`recipe_id`, `user_id`, `title`, `description`, `category`, `cuisine_type`, `spice_level`, `image_url`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 1, 'Spaghetti Carbonara', 'A classic Italian pasta dish with creamy sauce.', 'Appetizer', 'Japanese', 0, 'carbonara.jpg', '2025-04-11 16:21:48', '2025-04-11 17:14:55', 1, 1),
(2, 2, 'Chicken Curry', 'Spicy and rich curry with tender chicken pieces.', 'Main Course', 'Indian', 2, '67f9507a991da_Chicken_Curry.jpg', '2025-04-11 16:21:48', '2025-04-11 17:25:14', 2, 2),
(3, 3, 'Double Chocolate Chip Cookies Recipe', 'The best recipe for double chocolate chip cookies must obviously include extra doses of chocolate. These soft-baked thick and chunky cookies are as indulgent as they look: rich and fudge-like with chewy centers, slightly crisp edges, and oodles of melty chocolate chips in each glorious bite.\r\n\r\nIt’s the chocolate dough recipe I’ve been making for years and even included it in my cookbook stuffed with peanut butter cups. There’s no reason to stray from this basic chocolate dough!', 'Dessert', 'Other', 0, '67f9c43bc7a35_double-chocolate-chip-cookies-recipe-2.jpg', '2025-04-11 16:21:48', '2025-04-12 01:39:07', 3, 3);

--
-- Dumping data for table `Recipe_Ingredient`
--

INSERT INTO `Recipe_Ingredient` (`recipe_id`, `ingredient_id`, `quantity`, `unit`) VALUES
(1, 1, '200', 'g'),
(1, 2, '100', 'g'),
(1, 3, '2', 'g'),
(1, 4, '50', 'g'),
(2, 5, '500', 'g'),
(2, 6, '2', 'g'),
(2, 7, '200', 'ml'),
(2, 8, '1', 'piece'),
(2, 9, '2', 'g'),
(3, 11, '100', 'g'),
(3, 12, '55', 'g'),
(3, 14, '1/2', 'cup'),
(3, 15, '100', 'g'),
(3, 16, '1', 'piece'),
(3, 17, '1', 'tsp'),
(3, 18, '125', 'g'),
(3, 19, '1', 'tsp'),
(3, 20, '1/8', 'tsp'),
(3, 21, '15', 'ml'),
(3, 22, '225', 'g');

--
-- Dumping data for table `Steps`
--

INSERT INTO `Steps` (`step_id`, `recipe_id`, `step_no`, `description`, `image_url`) VALUES
(4, 1, 1, 'Boil spaghetti until al dente.', ''),
(5, 1, 2, 'Fry bacon until crispy.', ''),
(6, 1, 3, 'Mix eggs and cheese, combine with pasta and bacon.', ''),
(7, 2, 2, 'Heat olive oil in a skillet over medium heat. Sauté onion until lightly browned.', '67f9507a9a02d_46822-Indian-Chicken-Curry-step-1-103232-e2ee06726c3e4555aa7a5ae3d96cc47d.jpg'),
(8, 2, 3, 'Stir in garlic, curry powder, cinnamon, paprika, bay leaf, ginger, sugar, and salt. Continue stirring for 2 minutes.', '67f9507a9a134_46822-Indian-Chicken-Curry-step-2-103239-5d895662cfeb4540ba6ae2b2ede841e6.jpg'),
(9, 2, 4, 'Add chicken pieces, tomato paste, yogurt, and coconut milk. Bring to a boil, reduce heat, and simmer for 20 to 25 minutes.', '67f9507a9a21b_46822-Indian-Chicken-Curry-step-3-103245-f78c0ef54ca84642b4341a30da1e4967.jpg'),
(10, 2, 5, 'Remove bay leaf, and stir in lemon juice and cayenne pepper. Simmer 5 more minutes.', '67f9507a9a356_46822-Indian-Chicken-Curry-step-4-103267-749f3ad7a23b46cd90c35af9d343995a.jpg'),
(11, 3, 2, 'This cookie dough requires at least 3 hours of chilling, but I prefer to chill the dough overnight. The colder the dough, the thicker the cookies.', ''),
(12, 3, 3, 'In a large bowl using a hand-held or stand mixer fitted with a paddle attachment, beat the butter, granulated sugar, and brown sugar together on medium high speed until fluffy and light in color, about 2-3 minutes. Add the egg and vanilla extract, and then beat on high speed until combined. Scrape down the sides and bottom of the bowl as needed.', '67f9c43bc83c9_Ingredient.jpg'),
(13, 3, 4, 'In a separate bowl, whisk the flour, cocoa powder, baking soda and salt together until combined. With the mixer running on low speed, slowly pour into the wet ingredients. Beat on low until combined. The cookie dough will be quite thick. Switch to high speed and beat in the milk, then the chocolate chips. The cookie dough will be sticky and tacky. Cover dough tightly and chill in the refrigerator for at least 3 hours and up to 3 days. Chilling is mandatory for this sticky cookie dough.\r\n', '67f9c43bc8473_double-chocolate-cookie-dough.jpg'),
(14, 3, 5, 'Remove cookie dough from the refrigerator and allow to sit at room temperature for 10 minutes. If the cookie dough chilled longer than 3 hours, let it sit at room temperature for about 20 minutes. This makes the chilled cookie dough easier to scoop and roll.', ''),
(15, 3, 6, 'Preheat oven to 350°F (177°C). Line large baking sheets with parchment paper or silicone baking mats. (Always recommended for cookies.) Set aside.', ''),
(16, 3, 7, 'Scoop and roll dough, a heaping 1.5 Tablespoons (about 35-40g; I like to use this medium cookie scoop) in size, into balls. To ensure a thicker cookie, make the balls taller than they are wide (almost like a cylinder or column). Arrange 2-3 inches apart on the baking sheets. The cookie dough is certainly sticky, so wipe your hands clean after every few balls of dough you shape.', '67f9c43bc862d_double-chocolate-cookie-dough-balls.jpg'),
(17, 3, 8, 'Bake the cookies for 11-12 minutes or until the edges appear set and the centers still look soft. Tip: If they aren’t really spreading by minute 9, remove them from the oven and lightly bang the baking sheet on the counter 2-3x. This helps initiate that spread. Return to the oven to continue baking.', '67f9c43bc86da_soft-double-chocolate-chip-cookies-on-baking-sheet.jpg'),
(18, 3, 9, 'Cool cookies for 5 minutes on the baking sheet. During this time, I like to press a few more chocolate chips into the tops of the warm cookies. (This is optional and only for looks.) Transfer to cooling rack to cool completely. The cookies will slightly deflate as they cool. ', ''),
(19, 3, 10, 'Cover leftover cookies tightly and store at room temperature for up to 1 week', '');

INSERT INTO `Nutrition` (`nutrition_id`, `recipe_id`, `calories`, `fat`, `carbs`, `protein`) VALUES
(1, 1, 570, 22.5, 65.0, 24.0),  -- Spaghetti Carbonara
(2, 2, 450, 18.0, 20.0, 38.0),  -- Chicken Curry
(3, 3, 320, 14.0, 45.0, 4.0);   -- Double Chocolate Chip Cookies

COMMIT;


