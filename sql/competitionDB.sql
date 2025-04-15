-- Create Database
DROP DATABASE IF EXISTS competitionDB;
CREATE DATABASE competitionDB;
USE competitionDB;

-- Create Tables
-- Competitions Table
CREATE TABLE competitions (
    competition_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    voting_end_date DATETIME NOT NULL,
    rules TEXT,
    prize_description TEXT,
    status ENUM('upcoming', 'active', 'completed') NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES usersDB.users(user_id)
);

-- Competition entries table
CREATE TABLE competition_entries (
    entry_id INT PRIMARY KEY AUTO_INCREMENT,
    competition_id INT NOT NULL,
    recipe_id INT NOT NULL,
    user_id INT NOT NULL,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (competition_id) REFERENCES competitions(competition_id),
    FOREIGN KEY (recipe_id) REFERENCES RecipeDB.Recipes(recipe_id),
    FOREIGN KEY (user_id) REFERENCES usersDB.users(user_id),
    UNIQUE KEY (competition_id, recipe_id) -- Prevent duplicate submissions
);

-- Votes table
CREATE TABLE votes (
    vote_id INT PRIMARY KEY AUTO_INCREMENT,
    entry_id INT NOT NULL,
    user_id INT NOT NULL,
    vote_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entry_id) REFERENCES competition_entries(entry_id),
    FOREIGN KEY (user_id) REFERENCES usersDB.users(user_id),
    UNIQUE KEY (entry_id, user_id) -- Prevent duplicate votes
);

-- Insert data
INSERT INTO competitions (
    title, description, start_date, end_date, voting_end_date, rules, prize_description, status, created_by
)
VALUES 
(
    'Free-for-all Cooking Contest',
    'Show off your best dish ever!',
    '2025-04-01 00:00:00',
    '2025-04-15 23:59:59',
    '2025-04-18 23:59:59',
    'Only one recipe per user.',
    '1st Prize: $100 gift card',
    'active',
    8
),
(
    'Extreme Cooking Contest',
    'Demonstrate your most extreme food to be savoured!',
    '2024-07-01 00:00:00',
    '2024-07-15 23:59:59',
    '2024-07-18 23:59:59',
    'Let your imaginary run wild.',
    '1st Prize: $50 worth of cooking utensils',
    'completed',
    9
),
(
    'Vegetarian Delight',
    'Submit your best vegetarian meals.',
    '2025-05-01 00:00:00',
    '2025-05-07 23:59:59',
    '2025-05-09 23:59:59',
    'No meat or seafood allowed.',
    'Winner gets featured on the homepage!',
    'upcoming',
    10
);

INSERT INTO competition_entries (
    competition_id, recipe_id, user_id, notes
)
VALUES 
(1, 1, 1, 'Great presentation.'),
(1, 2, 2, 'Tasty and healthy.'),
(1, 3, 3, 'Crunchy cookies for snacks.'),
(2, 1, 1, 'Extreme!'),
(2, 2, 2, 'Cool & Tasty.'),
(2, 3, 3, 'Nom Nom Nom!');

INSERT INTO votes (
    entry_id, user_id
)
VALUES 
(1, 2), -- Bob votes for Alice
(2, 3), -- Charlie votes for Bob
(2, 1), -- Alice votes for Bob
(4, 2), -- Bob votes for Alice
(4, 3), -- Charlie votes for Alice
(4, 4), -- Weiyu votes for Alice
(5, 5), -- Weihong votes for Bob
(5, 6), -- Desmond votes for Bob
(6, 7); -- Yisheng votes for Charlie
