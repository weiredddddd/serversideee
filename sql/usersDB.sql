-- Create Database
DROP DATABASE IF EXISTS usersDB;
CREATE DATABASE usersDB;
USE usersDB;

-- Create users table 
CREATE TABLE users (
  user_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username varchar(50) NOT NULL,
  nickname varchar(100) DEFAULT NULL,
  email varchar(100) NOT NULL,
  password varchar(255) NOT NULL,
  registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reset_token varchar(255) DEFAULT NULL,
  reset_token_expiry datetime DEFAULT NULL,
  avatar int(11) DEFAULT 0,
  is_admin TINYINT(1) DEFAULT 0,
  UNIQUE KEY email (email)
);

-- Insert data for users 
INSERT INTO users (username, nickname, email, password, reset_token, reset_token_expiry, avatar, registration_date, is_admin) VALUES
  ('alicej', 'Alice Johnson', 'alice@example.com', '$2y$10$Fo00tBXLFOQAP2R1ORIN9OTK0t/HJlkBRPBNooQlCZgxskNARHcAe', NULL, NULL, 1, '2023-01-01 00:00:00', 0),
  ('bobsmith', 'Bob S.', 'bob@example.com', '$2y$10$JkR2sJXtUnBEqQeoFLU5fO6Zoqusc4mhMUd7az1qgedRLL11ppQMy', NULL, NULL, 0, '2024-06-03 00:00:00', 0),
  ('charlielee', 'Charlie', 'charlie@example.com', '$2y$10$2K4rP/kgjMG7SLWSlhLpGeXM8lyI.iPyzUmBXlClK1H86l7q15ZT2', NULL, NULL, 4, '2023-05-18 00:00:00', 0),
  ('ngweiyu123', 'Wei Yu', 'weiyung0091@1utar.my', '$2y$10$yskdkgLLThcVqrF.WvajAeX0WwGMKDh4FqLZN7wZRmQo1BdQRLjy6', NULL, NULL, 2, '2022-03-18 00:00:00', 0),
  ('weihong116', 'Wei Hong', 'weihong116@1utar.my', '$2y$10$8FMGs9egxOCoOe3SVeIXYO2mRB/ZGY45xzSHD4l/3ZAnEWKWQ1YDi', NULL, NULL, 3, '2025-02-11 00:00:00', 0),
  ('desmondutar', 'Desmond', 'desmond@1utar.my', '$2y$10$CSOOBwRXQD89icZ3c3JltO5xOkJxkHKVdOA6Lq4MNmRgPKB7fH9l6', NULL, NULL, 5, '2022-03-18 00:00:00', 0),
  ('yishengutar', 'Yi Sheng', 'yisheng@1utar.my', '$2y$10$osC0NA/jtWQ05NJtvvNakeqI8.E8WbdgeVelRrY52U64sB85Tn78y', NULL, NULL, 5, '2024-12-31 00:00:00', 0);

-- Passwords (for reference only):
-- Alice Johnson  -> password123
-- Bob Smith      -> securepass
-- Charlie Lee    -> charliepass
-- Wei Yu         -> weiyupass
-- Wei Hong       -> weihongpass
-- Desmond        -> desmondpass
-- Yi Sheng       -> yishengpass

-- Insert admin users (password: admin123)
INSERT INTO users (username, nickname, email, password, reset_token, reset_token_expiry, avatar, registration_date, is_admin) VALUES
  ('adminjane', 'Admin Jane', 'adminjane@example.com', '$2a$12$36IMr7adfDbtprnF.E2gq.QYU7vbSyQs7xJN9y9jh3ry7j/6ei9YG', NULL, NULL, 1, '2024-03-01 00:00:00', 1),
  ('adminmark', 'Admin Mark', 'adminmark@example.com', '$2a$12$36IMr7adfDbtprnF.E2gq.QYU7vbSyQs7xJN9y9jh3ry7j/6ei9YG', NULL, NULL, 2, '2023-01-02 00:00:00', 1),
  ('superadmin', 'Super Admin', 'superadmin@example.com', '$2a$12$36IMr7adfDbtprnF.E2gq.QYU7vbSyQs7xJN9y9jh3ry7j/6ei9YG', NULL, NULL, 3, '2024-12-21 00:00:00', 1);

