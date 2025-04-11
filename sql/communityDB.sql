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

-- Insert some sample data
INSERT INTO discussion_posts (user_id, title, content, category) VALUES
(1, 'Best Knife Sharpening Techniques', 'Here are my tips for keeping your knives razor sharp...', 'Cooking Tips'),
(2, 'How to prevent pasta from sticking', 'Add a bit of olive oil to the water and stir occasionally...', 'Cooking Tips'),
(3, 'Your favorite cooking gadgets?', 'What are some kitchen gadgets you can\'t live without?', 'General Discussion');

-- Sample data for post_comments
INSERT INTO post_comments (post_id, user_id, comment_text) VALUES
(1, 2, 'Great tips! I would also recommend using a honing rod regularly.'),
(1, 3, 'What do you think about electric knife sharpeners?'),
(2, 1, 'I also rinse my pasta with cold water if I\'m not using it immediately.');

-- Sample data for recipe_comments
INSERT INTO recipe_comments (recipe_id, user_id, comment_text) VALUES
(1, 2, 'Delicious recipe! I added some extra garlic and it turned out great.'),
(2, 1, 'Thanks for sharing! I might try it with a vegetarian twist.'),
(3, 3, 'Loved it! The instructions were super clear.');

-- Sample data for recipe_ratings
INSERT INTO recipe_ratings (recipe_id, user_id, rating_value) VALUES
(1, 2, 5),
(2, 1, 4),
(3, 3, 5);

-- Sample data for post_likes
INSERT INTO post_likes (post_id, user_id) VALUES
(1, 3),
(2, 1),
(3, 2);

-- Sample data for user_followers
INSERT INTO user_followers (follower_id, following_id) VALUES
(2, 1), -- User 2 follows User 1
(3, 1), -- User 3 follows User 1
(1, 3); -- User 1 follows User 3