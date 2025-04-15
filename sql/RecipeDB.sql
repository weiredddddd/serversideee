DROP DATABASE IF EXISTS RecipeDB;
CREATE DATABASE RecipeDB;
USE RecipeDB;

CREATE TABLE `Ingredients` (
  `ingredient_id` int(11) NOT NULL,
  `ingredient_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Ingredients`
--

INSERT INTO `Ingredients` (`ingredient_id`, `ingredient_name`) VALUES
(18, 'All-purpose flour'),
(2, 'Bacon'),
(19, 'Baking Soda'),
(35, 'Basil'),
(26, 'Black Pepper'),
(27, 'Boneless Chicken Breasts'),
(13, 'Butter'),
(28, 'Cake Mix'),
(5, 'Chicken'),
(25, 'Chicken Broth'),
(24, 'Chicken gravy'),
(23, 'Chicken Soup'),
(22, 'Chocolate chips'),
(12, 'Cocoa Powder'),
(7, 'Coconut Milk'),
(6, 'Curry Powder'),
(15, 'Dark Brown Sugar'),
(16, 'Egg'),
(3, 'Eggs'),
(10, 'Flour'),
(9, 'Garlic'),
(33, 'Ground Beef'),
(38, 'Heavy Cream'),
(31, 'Ice Cream'),
(21, 'Milk'),
(34, 'Mozzarella Cheese'),
(30, 'Oil'),
(8, 'Onion'),
(36, 'Oregano'),
(4, 'Parmesan Cheese'),
(39, 'Pasta Sheets'),
(37, 'Red Chili Flakes'),
(40, 'Ricotta Cheese'),
(20, 'Salt'),
(1, 'Spaghetti'),
(41, 'Spinach'),
(11, 'Sugar'),
(32, 'Tomato Paste'),
(14, 'Unsalted butter'),
(17, 'Vanilla Extract'),
(29, 'Water');

-- --------------------------------------------------------

--
-- Table structure for table `Nutrition`
--

CREATE TABLE `Nutrition` (
  `nutrition_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `calories` int(11) DEFAULT NULL,
  `fat` float DEFAULT NULL,
  `carbs` float DEFAULT NULL,
  `protein` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Nutrition`
--

INSERT INTO `Nutrition` (`nutrition_id`, `recipe_id`, `calories`, `fat`, `carbs`, `protein`) VALUES
(1, 1, 570, 22.5, 65, 24),
(2, 2, 450, 18, 20, 38),
(3, 3, 320, 14, 45, 4),
(4, 4, 180, 6, 9, 21),
(5, 5, 250, 11, 30, 4),
(6, 6, 650, 35, 45, 40),
(7, 7, 200, 10, 25, 5),
(8, 8, 500, 30, 35, 25),
(9, 9, 400, 25, 5, 35),
(10, 10, 300, 15, 40, 10);

-- --------------------------------------------------------

--
-- Table structure for table `Recipes`
--

CREATE TABLE `Recipes` (
  `recipe_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `cuisine_type` varchar(50) DEFAULT NULL,
  `spice_level` tinyint(4) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `view_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Recipes`
--

INSERT INTO `Recipes` (`recipe_id`, `user_id`, `title`, `description`, `category`, `cuisine_type`, `spice_level`, `image_url`, `created_at`, `updated_at`, `created_by`, `updated_by`, `view_count`) VALUES
(1, 1, 'Spaghetti Carbonara', 'A classic Italian pasta dish with creamy sauce.', 'Appetizer', 'Japanese', 0, 'carbonara.jpg', '2025-04-11 16:21:48', '2025-04-14 18:46:21', 1, 1, 3),
(2, 2, 'Chicken Curry', 'Spicy and rich curry with tender chicken pieces.', 'Main Course', 'Indian', 2, '67f9507a991da_Chicken_Curry.jpg', '2025-04-11 16:21:48', '2025-04-14 17:26:51', 2, 2, 1),
(3, 3, 'Double Chocolate Chip Cookies Recipe', 'The best recipe for double chocolate chip cookies must obviously include extra doses of chocolate. These soft-baked thick and chunky cookies are as indulgent as they look: rich and fudge-like with chewy centers, slightly crisp edges, and oodles of melty chocolate chips in each glorious bite.\r\n\r\nIt’s the chocolate dough recipe I’ve been making for years and even included it in my cookbook stuffed with peanut butter cups. There’s no reason to stray from this basic chocolate dough!', 'Dessert', 'Other', 0, '67f9c43bc7a35_double-chocolate-chip-cookies-recipe-2.jpg', '2025-04-11 16:21:48', '2025-04-12 01:39:07', 3, 3, 0),
(4, 5, 'Slow Cooker Chicken and Gravy', 'This slow cooker chicken and gravy could not be easier. You\'ll want to serve this over hot rice or mashed potatoes for the ultimate comfort food dish.', 'Main Course', 'Japanese', 0, '67fca4ad5ec10_slowcooker-gravy.jpg', '2025-04-14 14:01:17', '2025-04-14 14:01:17', NULL, NULL, 0),
(5, 5, 'Ice Cream Cake', 'This ice cream cake is easy to make with any flavor of ice cream or cake mix you prefer. Frost with your favorite frosting, fudge topping, crushed Oreos, or anything!', 'Dessert', 'Western', 0, '67fca650d94b6_ice-cream.jpg', '2025-04-14 14:08:16', '2025-04-14 14:08:16', NULL, NULL, 0),
(6, 1, 'Classic Lasagna', 'Layers of pasta with rich meat sauce and creamy cheese.', 'Main Course', 'Italian', 1, '67fce40cd2b06_lagsanapic.jpg', '2025-04-14 17:00:00', '2025-04-14 18:31:40', 1, 1, 1),
(7, 2, 'Creamy Tomato Basil Soup', 'A smooth and flavorful tomato soup with fresh basil.', 'Appetizer', 'Japanese', 0, '67fce4e7c25c3_tomato-basil-soup.jpg', '2025-04-14 17:05:00', '2025-04-14 18:35:19', 2, 2, 0),
(8, 3, 'Spicy Beef Tacos', 'Ground beef tacos with a spicy kick.', 'Main Course', 'Mexican', 3, '67fce53e12464_Spicy-Beef-Tacos.jpg', '2025-04-14 17:10:00', '2025-04-14 18:36:46', 3, 3, 0),
(9, 4, 'Garlic Butter Shrimp', 'Juicy shrimp sautéed in garlic butter sauce.', 'Main Course', 'Japanese', 1, '67fce4663806a_garlic-butter-shrimp.jpg', '2025-04-14 17:15:00', '2025-04-14 18:33:10', 4, 4, 0),
(10, 5, 'Vegetable Stir Fry', 'A mix of fresh vegetables stir-fried in a savory sauce.', 'Main Course', 'Japanese', 2, '67fce3424df7f_vegetable-stirfry.jpg', '2025-04-14 17:20:00', '2025-04-14 18:28:18', 5, 5, 0);

-- --------------------------------------------------------

--
-- Table structure for table `Recipe_Ingredient`
--

CREATE TABLE `Recipe_Ingredient` (
  `recipe_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` varchar(50) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(3, 22, '225', 'g'),
(4, 20, '1/2', 'tsp'),
(4, 23, '1', 'tbsp'),
(4, 24, '2', 'piece'),
(4, 25, '1/2', 'cup'),
(4, 26, '1/2', 'tsp'),
(4, 27, '3 to 4', 'piece'),
(5, 3, '3', 'piece'),
(5, 28, '1', 'piece'),
(5, 29, '1', 'cup'),
(5, 30, '1', 'tsp'),
(5, 31, '1', 'cup'),
(6, 32, '200', 'g'),
(6, 33, '500', 'g'),
(6, 34, '250', 'g'),
(6, 35, '1', 'tbsp'),
(6, 39, '9', 'g'),
(6, 40, '200', 'g'),
(7, 32, '300', 'g'),
(7, 35, '2', 'tbsp'),
(7, 38, '100', 'ml'),
(8, 20, '1', 'tsp'),
(8, 33, '400', 'g'),
(8, 37, '1', 'tsp'),
(9, 9, '3', 'g'),
(9, 13, '50', 'g'),
(9, 20, '1', 'tsp'),
(10, 6, '2', 'tsp'),
(10, 8, '1', 'piece'),
(10, 41, '200', 'g');

-- --------------------------------------------------------

--
-- Table structure for table `Steps`
--

CREATE TABLE `Steps` (
  `step_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `step_no` int(11) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(19, 3, 10, 'Cover leftover cookies tightly and store at room temperature for up to 1 week', ''),
(20, 4, 1, 'In a 6-quart slow cooker, whisk together canned soup, gravy mix. chicken broth, salt, and pepper. Add chicken. Cover and cook on Low for 4 hours.', ''),
(21, 4, 2, 'Before serving, carefully shred chicken using a hand mixer.', ''),
(22, 5, 1, 'Preheat the oven to 350 degrees F (175 degrees C). Grease a 9x13-inch baking dish.', ''),
(23, 5, 2, 'Combine chocolate cake mix, water, eggs, and oil in a large bowl. Beat with an electric mixer on medium speed until smooth, about 2 minutes. Pour batter into the prepared baking dish.', '67fca650dcfc1_ice-cream-step-2.jpg'),
(24, 5, 3, 'Bake in the preheated oven until a toothpick inserted into the center comes out clean, 26 to 30 minutes. Allow to cool completely on a wired rack.', '67fca650dd557_ice-cream-step-3.jpg'),
(25, 5, 4, 'Meanwhile, remove the carton paper from ice cream. Set ice cream block on its long side. Use a piece of string or dental floss to cut ice cream in half lengthwise, then place the 2 pieces side-by-side on a sheet of waxed paper.', '67fca650dd704_ice-cream-step-4.jpg'),
(26, 5, 5, 'Remove cooled cake from the baking dish and place over the ice cream slabs. Trim cake and ice cream, so the edges are flush.', ''),
(27, 5, 6, 'Place a board or serving platter over cake. Holding onto the waxed paper and board, flip ice cream cake over. Remove the waxed paper and smooth the seam between the ice cream slabs.', '67fca650dd8f4_ice-cream-step-6jpg.jpg'),
(41, 10, 1, 'Stir-fry onions and spinach with curry powder.', ''),
(42, 10, 2, 'Add other vegetables and cook until tender.', ''),
(43, 6, 1, 'Preheat oven to 375°F (190°C).', ''),
(44, 6, 2, 'Cook ground beef until browned. Add tomato paste and simmer.', ''),
(45, 6, 3, 'Layer pasta sheets, meat sauce, ricotta, and mozzarella in a baking dish.', ''),
(46, 6, 4, 'Bake for 45 minutes until cheese is bubbly.', ''),
(47, 9, 1, 'Sauté garlic in butter, add shrimp and cook until pink.', ''),
(48, 9, 2, 'Season with salt and pepper, garnish with parsley.', ''),
(49, 7, 1, 'Sauté garlic in butter, add tomato paste and cook.', ''),
(50, 7, 2, 'Add heavy cream and fresh basil, simmer for 15 minutes.', ''),
(51, 7, 3, 'Blend the mixture until smooth and serve hot.', ''),
(52, 8, 1, 'Cook ground beef with chili flakes and seasoning.', ''),
(53, 8, 2, 'Assemble beef in taco shells with desired toppings.', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Ingredients`
--
ALTER TABLE `Ingredients`
  ADD PRIMARY KEY (`ingredient_id`),
  ADD UNIQUE KEY `ingredient_name` (`ingredient_name`);

--
-- Indexes for table `Nutrition`
--
ALTER TABLE `Nutrition`
  ADD PRIMARY KEY (`nutrition_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `Recipes`
--
ALTER TABLE `Recipes`
  ADD PRIMARY KEY (`recipe_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `Recipe_Ingredient`
--
ALTER TABLE `Recipe_Ingredient`
  ADD PRIMARY KEY (`recipe_id`,`ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `Steps`
--
ALTER TABLE `Steps`
  ADD PRIMARY KEY (`step_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Ingredients`
--
ALTER TABLE `Ingredients`
  MODIFY `ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `Nutrition`
--
ALTER TABLE `Nutrition`
  MODIFY `nutrition_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `Recipes`
--
ALTER TABLE `Recipes`
  MODIFY `recipe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `Steps`
--
ALTER TABLE `Steps`
  MODIFY `step_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Nutrition`
--
ALTER TABLE `Nutrition`
  ADD CONSTRAINT `nutrition_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `Recipes` (`recipe_id`) ON DELETE CASCADE;

--
-- Constraints for table `Recipes`
--
ALTER TABLE `Recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usersDB`.`Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `usersDB`.`Users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `recipes_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `usersDB`.`Users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `Recipe_Ingredient`
--
ALTER TABLE `Recipe_Ingredient`
  ADD CONSTRAINT `recipe_ingredient_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `Recipes` (`recipe_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_ingredient_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `Ingredients` (`ingredient_id`) ON DELETE CASCADE;

--
-- Constraints for table `Steps`
--
ALTER TABLE `Steps`
  ADD CONSTRAINT `steps_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `Recipes` (`recipe_id`) ON DELETE CASCADE;
COMMIT;

