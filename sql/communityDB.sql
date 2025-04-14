-- Create Database
DROP DATABASE IF EXISTS communityDB;
CREATE DATABASE communityDB;
USE communityDB;

-- Create some Tables
-- Table for discussion posts
CREATE TABLE IF NOT EXISTS discussion_posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    category VARCHAR(100) NOT NULL,
    post_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    view_count INT DEFAULT 0,
    like_count INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES usersDB.users(user_id) ON DELETE CASCADE
);

-- Table for comments on discussion posts
CREATE TABLE IF NOT EXISTS post_comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    comment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES discussion_posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES usersDB.users(user_id) ON DELETE CASCADE
);

-- Table for recipe comments (references RecipeDB)
CREATE TABLE IF NOT EXISTS recipe_comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    comment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipe_id) REFERENCES RecipeDB.Recipes(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES usersDB.users(user_id) ON DELETE CASCADE
);

-- Table for recipe ratings (references RecipeDB)
CREATE TABLE IF NOT EXISTS recipe_ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    user_id INT NOT NULL,
    rating_value INT NOT NULL CHECK (rating_value BETWEEN 1 AND 5),
    rating_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_recipe_rating (user_id, recipe_id),
    FOREIGN KEY (recipe_id) REFERENCES RecipeDB.Recipes(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES usersDB.users(user_id) ON DELETE CASCADE
);

-- Sample data for discussion_posts
-- Insert into discussion_posts
INSERT INTO discussion_posts (user_id, title, content, category, image_url, view_count, like_count) VALUES
(1, 'Secrets to Fluffier Scrambled Eggs', 
'I’ve been experimenting with different ways to make my scrambled eggs extra fluffy, and I’ve picked up a few tricks that really make a difference:\n\n1. Whisk thoroughly before cooking: The more air you whip into the eggs, the lighter they turn out. I whisk until the yolks and whites are completely combined and a bit frothy.\n\n2. Cook on low heat: High heat makes eggs rubbery. Low and slow is the way to go. I stir gently and constantly with a spatula to get soft curds.\n\n3. Add a splash of dairy: A little milk or cream makes a big difference in texture. I’ve also tried sour cream, which adds richness.\n\nSome people also swear by adding baking powder or seltzer water — has anyone tried those?\n\nI''d love to hear your own tricks for the perfect scrambled eggs!', 
'Cooking Tips', 
'scramble_eggs.jpg', 50, 8),

(2, 'Need Help with Sourdough Rising', 
'My sourdough starter is bubbly, but my dough isn’t rising much. I’m using bread flour and proofing overnight. Should I be adding more water or kneading longer?', 
'Recipe Questions', 
NULL, 12, 3),

(3, 'Best Budget-Friendly Chef’s Knife?', 
'I’m looking for a good quality chef’s knife under $50. I’ve seen some on Amazon but not sure what’s reliable. Any favorites you swear by?', 
'Kitchen Equipment', 
'chefs_knife.jpg', 14, 5),

(4, 'What’s the Difference Between Broiling and Roasting?', 
'I’ve seen recipes calling for both but I’m not totally sure how they differ. Is it just temperature or something more?', 
'General Discussion', 
NULL, 4, 2),

(5, 'Tips for Cooking Steak to Medium Rare', 
'I always struggle with getting the steak just right. I use a thermometer now, but sometimes it still ends up medium or worse. What’s your method?', 
'Cooking Tips', 
'steak.jpg', 42, 12),

(6, 'How to Prevent Cakes from Sinking in the Middle?', 
'Every time I bake a sponge cake, it puffs up in the oven and sinks while cooling. I follow the recipe exactly. Could it be oven temp or overmixing?', 
'Recipe Questions', 
NULL, 26, 4),

(7, 'Underrated Kitchen Tools You Love?', 
'I recently started using a bench scraper and I’m amazed at how useful it is beyond baking. What simple tools have changed your kitchen game?', 
'Kitchen Equipment', 
'bench_scraper.jpg', 15, 7);

-- Insert into post_comments table
INSERT INTO post_comments (post_id, user_id, comment_text) VALUES
(1, 2, 'Totally agree about low heat! I also take mine off the stove a little early so they stay creamy.'),
(1, 1, 'I use cream cheese instead of milk — gives it a rich texture and subtle tang.'),
(1, 4, 'Never tried sour cream, but I’m intrigued. Might give it a go this weekend!'),
(1, 5, 'I’ve heard Gordon Ramsay adds butter at the end. Tried it and it’s a game-changer!'),
(1, 6, 'Has anyone tried using non-dairy milk like oat or almond? Wondering if it affects the fluffiness.'),
(1, 2, 'A pinch of salt while whisking helps break down the proteins for a smoother texture.'),
(2, 1, 'Try doing a windowpane test after kneading. If the dough stretches thin without tearing, gluten is developed well. Also, make sure your proofing temperature is warm enough—around 75-78°F works great for sourdough.'),
(2, 7, 'Make sure you’re doing stretch and folds during bulk fermentation.'),
(2, 3, 'How long are you fermenting it? Sometimes an extra hour can really help. Also, try folding gently during bulk.'),
(3, 3, 'The Victorinox Fibrox is great for its price!'),
(3, 4, 'Check out Mercer Culinary — great balance and super affordable.'),
(3, 5, 'I use a Cuisinart chef’s knife I got in a set. It holds up surprisingly well for the price.'),
(4, 1, 'Broiling uses direct heat from the top, roasting is more even.'),
(5, 2, 'Let the steak rest after cooking to retain juices.'),
(5, 3, 'Reverse sear method works wonders. Start low in the oven, then sear at the end.'),
(5, 5, 'Make sure the steak is at room temperature before cooking. Helps cook more evenly.'),
(5, 6, 'Use a meat thermometer and aim to pull it out at 130°F for medium rare.'),
(5, 4, 'Baste with butter, garlic, and thyme during the last minute of searing — it adds flavor and color.'),
(6, 1, 'Lower the oven temp slightly and avoid opening the door too soon.'),
(7, 3, 'My garlic press is a surprisingly underrated tool.'),
(7, 2, 'Silicone spatulas are my favorite — heatproof and great for scraping every bit out.');

-- Sample data for recipe_comments
INSERT INTO recipe_comments (recipe_id, user_id, comment_text) VALUES
(1, 3, 'Swapped out butter for olive oil and it still turned out amazing.'),
(2, 2, 'I made a vegan version — just as creamy and flavorful!'),
(3, 1, 'Can confirm: adding cinnamon made a big difference.'),
(2, 1, 'Next time I might try baking it instead of frying.'),
(1, 2, 'Thanks! My kids loved it, and it was easy to follow.');

-- Sample data for recipe_ratings
INSERT INTO recipe_ratings (recipe_id, user_id, rating_value) VALUES
(1, 3, 5),
(2, 2, 4),
(3, 1, 5),
(1, 1, 4),
(3, 2, 5);
