-- Create Database
DROP DATABASE IF EXISTS usersDB;
CREATE DATABASE usersDB;
USE usersDB;

-- Create users table
CREATE TABLE users (
  user_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username varchar(50) NOT NULL,
  email varchar(100) NOT NULL,
  password varchar(255) NOT NULL,
  reset_token varchar(255) DEFAULT NULL,
  reset_token_expiry datetime DEFAULT NULL,
  avatar int(11) DEFAULT 0,
  UNIQUE KEY email (email)
);

-- Insert data for users
INSERT INTO users (username, email, password, reset_token, reset_token_expiry, avatar) VALUES
  ('Alice Johnson', 'alice@example.com', '$2y$10$Fo00tBXLFOQAP2R1ORIN9OTK0t/HJlkBRPBNooQlCZgxskNARHcAe', NULL, NULL, 0), 
  ('Bob Smith', 'bob@example.com', '$2y$10$JkR2sJXtUnBEqQeoFLU5fO6Zoqusc4mhMUd7az1qgedRLL11ppQMy', NULL, NULL, 0), 
  ('Charlie Lee', 'charlie@example.com', '$2y$10$WfGLBqJcyOe6rIlwA15HJ.ejyzX0cUvujpsyoKLUuEs41ITiK5npW', NULL, NULL, 0), 
  ('weired', 'weihong116@1utar.my', '$2y$10$MaaVcmTHpz4/46apRxLCo.kwiRvaFK5QGOaCbScfG7zB8AAmFxrXO', NULL, NULL, 0),
  ('hehe', 'hehe@mail.com', '$2y$10$IPR2k06Gg2b4xOEQiygKve1kEdfA4LWyh/CIKH/ZEME9lDkZ7kpdu', NULL, NULL, 0),
  ('weihong___', 'weihong116@gmail.com', '$2y$10$JhXEX0bYluBB4U4q1q1qKOHG43Y8fEJUzfFaaA59L8YpKw4c/PY7e', NULL, NULL, 0);

-- Passwords (for reference only):
-- Alice Johnson  -> password123
-- Bob Smith      -> securepass
-- Charlie Lee    -> charliepass
-- weired         -> weiredpass
-- hehe           -> hehepass
-- weihong___     -> weihongpass