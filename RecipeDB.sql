-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 31, 2025 at 05:52 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `RecipeDB`
--

-- --------------------------------------------------------

--
-- Table structure for table `Ingredients`
--

CREATE TABLE `Ingredients` (
  `ingredient_id` int(11) NOT NULL,
  `ingredient_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Ingredients`
--

INSERT INTO `Ingredients` (`ingredient_id`, `ingredient_name`) VALUES
(2, 'Bacon'),
(13, 'Butter'),
(5, 'Chicken'),
(12, 'Cocoa Powder'),
(7, 'Coconut Milk'),
(6, 'Curry Powder'),
(3, 'Eggs'),
(10, 'Flour'),
(9, 'Garlic'),
(8, 'Onion'),
(4, 'Parmesan Cheese'),
(1, 'Spaghetti'),
(11, 'Sugar');

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
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Recipes`
--

INSERT INTO `Recipes` (`recipe_id`, `user_id`, `title`, `description`, `category`, `cuisine_type`, `spice_level`, `image_url`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 1, 'Spaghetti Carbonara', 'A classic Italian pasta dish with creamy sauce.', 'Pasta', NULL, 0, 'carbonara.jpg', '2025-03-15 08:25:03', '2025-03-15 08:25:03', 1, 1),
(2, 2, 'Chicken Curry', 'Spicy and rich curry with tender chicken pieces.', 'Curry', NULL, 0, 'chicken_curry.jpg', '2025-03-15 08:25:03', '2025-03-15 08:25:03', 2, 2),
(3, 3, 'Chocolate Cake', 'A rich and moist chocolate cake.', 'Dessert', NULL, 0, 'chocolate_cake.jpg', '2025-03-15 08:25:03', '2025-03-15 08:25:03', 3, 3);

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

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `avatar` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`user_id`, `username`, `email`, `password`, `reset_token`, `reset_token_expiry`, `avatar`) VALUES
(1, 'Alice Johnson', 'alice@example.com', 'password123', NULL, NULL, 0),
(2, 'Bob Smith', 'bob@example.com', 'securepass', NULL, NULL, 0),
(3, 'Charlie Lee', 'charlie@example.com', 'charliepass', NULL, NULL, 0),
(4, 'weired', 'weihong116@1utar.my', '$2y$10$t8l6TLubfd0wBrmupRL5E.YCGhgXTebl/uMFcAOTnxF6IuKN/0VNW', NULL, NULL, 0),
(5, 'hehe', 'hehe@mail.com', '$2y$10$imoGyBNh42LTJjiEXjZKb.ZwwrL9Tc1MWbx/XfN/FpqGwJsEP1pr.', NULL, NULL, 0),
(6, 'weihong___', 'weihong116@gmail.com', '$2y$10$WiBsYbFrWwXt38sJPqdsU.6nfZQXqdUKF/kZCwu2vjHFeKzPg7QsO', NULL, NULL, 0);

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
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Ingredients`
--
ALTER TABLE `Ingredients`
  MODIFY `ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `Recipes`
--
ALTER TABLE `Recipes`
  MODIFY `recipe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Steps`
--
ALTER TABLE `Steps`
  MODIFY `step_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Recipes`
--
ALTER TABLE `Recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `Users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `recipes_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `Users` (`user_id`) ON DELETE SET NULL;

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
