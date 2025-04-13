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

-- Table for likes on discussion posts
CREATE TABLE IF NOT EXISTS post_likes (
    like_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    like_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_post_like (user_id, post_id),
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

-- Table for user followers
CREATE TABLE IF NOT EXISTS user_followers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    follow_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_follow_relationship (follower_id, following_id),
    FOREIGN KEY (follower_id) REFERENCES usersDB.users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES usersDB.users(user_id) ON DELETE CASCADE
);

-- Sample data for discussion_posts
-- Insert into discussion_posts table
INSERT INTO discussion_posts (user_id, title, content, category, image_url) VALUES
(1, 'How to Store Fresh Herbs Properly?', 
'I used to waste so many herbs until I started storing them in jars with a bit of water in the fridge. Parsley and cilantro last over a week this way! Anyone else have tricks for basil or rosemary?', 
'Cooking Tips', 
'fresh_herbs.jpg'),

(2, 'Need Help with Sourdough Rising', 
'My sourdough starter is bubbly, but my dough isn’t rising much. I’m using bread flour and proofing overnight. Should I be adding more water or kneading longer?', 
'Recipe Questions', 
NULL),

(3, 'Best Budget-Friendly Chef’s Knife?', 
'I’m looking for a good quality chef’s knife under $50. I’ve seen some on Amazon but not sure what’s reliable. Any favorites you swear by?', 
'Kitchen Equipment', 
'chefs_knife.jpg'),

(1, 'What’s the Difference Between Broiling and Roasting?', 
'I’ve seen recipes calling for both but I’m not totally sure how they differ. Is it just temperature or something more?', 
'General Discussion', 
NULL),

(2, 'Tips for Cooking Steak to Medium Rare', 
'I always struggle with getting the steak just right. I use a thermometer now, but sometimes it still ends up medium or worse. What’s your method?', 
'Cooking Tips', 
'steak.jpg'),

(3, 'How to Prevent Cakes from Sinking in the Middle?', 
'Every time I bake a sponge cake, it puffs up in the oven and sinks while cooling. I follow the recipe exactly. Could it be oven temp or overmixing?', 
'Recipe Questions', 
NULL),

(1, 'Underrated Kitchen Tools You Love?', 
'I recently started using a bench scraper and I’m amazed at how useful it is beyond baking. What simple tools have changed your kitchen game?', 
'Kitchen Equipment', 
'bench_scraper.jpg');

-- Insert into post_comments table
INSERT INTO post_comments (post_id, user_id, comment_text) VALUES
(1, 2, 'Try wrapping basil in paper towels. It helps a lot.'),
(2, 1, 'Make sure you’re doing stretch and folds during bulk fermentation.'),
(3, 3, 'The Victorinox Fibrox is great for its price!'),
(4, 1, 'Broiling uses direct heat from the top, roasting is more even.'),
(5, 2, 'Let the steak rest after cooking to retain juices.'),
(6, 1, 'Lower the oven temp slightly and avoid opening the door too soon.'),
(7, 3, 'My garlic press is a surprisingly underrated tool.');

-- Insert into post_likes table
INSERT INTO post_likes (post_id, user_id) VALUES
(1, 3),  
(2, 2),  
(3, 1),  
(4, 3),  
(5, 2),  
(6, 1),  
(7, 3); 

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

-- Sample data for user_followers
INSERT INTO user_followers (follower_id, following_id) VALUES
(2, 1), -- User 2 follows User 1
(3, 1), -- User 3 follows User 1
(1, 3); -- User 1 follows User 3