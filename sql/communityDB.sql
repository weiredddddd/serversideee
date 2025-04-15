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
'scramble_eggs.jpg', 21, 5),

(2, 'Need Help with Sourdough Rising', 
'My sourdough starter is bubbly, but my dough isn’t rising much. I’m using bread flour and proofing overnight. Should I be adding more water or kneading longer?', 
'Recipe Questions', 
NULL, 12, 4),

(3, 'Best Budget-Friendly Chef’s Knife?', 
'I’m looking for a good quality chef’s knife under $50. I’ve seen some on Amazon but not sure what’s reliable. Any favorites you swear by?', 
'Kitchen Equipment', 
'chefs_knife.jpg', 14, 4),

(4, 'What’s the Difference Between Broiling and Roasting?', 
'I’ve seen recipes calling for both but I’m not totally sure how they differ. Is it just temperature or something more?', 
'General Discussion', 
NULL, 4, 2),

(5, 'Tips for Cooking Steak to Medium Rare', 
'I always struggle with getting the steak just right. I use a thermometer now, but sometimes it still ends up medium or worse. What’s your method?', 
'Cooking Tips', 
'steak.jpg', 42, 6),

(6, 'How to Prevent Cakes from Sinking in the Middle?', 
'Every time I bake a sponge cake, it puffs up in the oven and sinks while cooling. I follow the recipe exactly. Could it be oven temp or overmixing?', 
'Recipe Questions', 
NULL, 26, 1),

(7, 'Underrated Kitchen Tools You Love?', 
'I recently started using a bench scraper and I’m amazed at how useful it is beyond baking. What simple tools have changed your kitchen game?', 
'Kitchen Equipment', 
'bench_scraper.jpg', 15, 0);

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
(1, 3, 'Took me only 20 minutes — perfect for a quick dinner.'),
(2, 2, 'I tried it with tofu instead of chicken — turned out great.'),
(3, 4, 'Added white chocolate chips and it was amazing!'),
(4, 1, 'Smelled amazing while cooking. Will make this again.'),
(4, 6, 'Could use a bit more seasoning, but overall very comforting.'),
(5, 2, 'Made it for my sister’s birthday — huge hit!'),
(5, 3, 'I layered brownies between ice cream layers. Game changer.'),
(6, 1, 'The lasagna held up great even the next day. Perfect leftovers.'),
(6, 7, 'I used ricotta and cream cheese together. Cheesy heaven!'),
(7, 5, 'Tried it with roasted garlic — next level flavor.'),
(8, 3, 'Spicy and satisfying! I added avocado on top.'),
(8, 6, 'Used ground turkey instead of beef and it was still juicy.'),
(9, 4, 'Garlic butter is a win every time. Served with rice.'),
(10, 5, 'Tried it with hoisin sauce and it gave a sweet kick.'),
(2, 4, 'I love how the spices blended — didn’t even need salt.'),
(4, 3, 'I added carrots and peas to make it more filling.'),
(6, 2, 'Used gluten-free noodles and it still layered nicely.'),
(5, 6, 'I topped it with crushed Oreos and it was a hit!'),
(3, 5, 'Fudgy, rich, and chocolatey — everything I wanted.'),
(8, 1, 'Wrapped the tacos in lettuce leaves for a low-carb version.'),
(10, 6, 'Used oyster sauce in the mix and it was a bomb!');


-- Sample data for recipe_ratings
INSERT INTO recipe_ratings (recipe_id, user_id, rating_value) VALUES
(1, 1, 5),
(1, 3, 4),
(2, 2, 5),
(2, 4, 4),
(3, 5, 5),
(3, 6, 3),
(4, 6, 3),
(4, 7, 4),
(4, 1, 3),
(5, 2, 5),
(5, 3, 4),
(5, 6, 4),
(6, 4, 5),
(6, 5, 4),
(7, 6, 3),
(7, 7, 4),
(8, 1, 5),
(8, 2, 2),
(9, 3, 4),
(9, 4, 5),
(10, 5, 4),
(10, 6, 5),
(1, 7, 3),
(2, 1, 4),
(3, 2, 5),
(4, 3, 3),
(5, 4, 4),
(6, 7, 5),
(7, 5, 3),
(8, 3, 4),
(9, 2, 5),
(10, 1, 4),
(3, 4, 4),  
(6, 1, 5),  
(6, 2, 4),  
(8, 6, 4);


