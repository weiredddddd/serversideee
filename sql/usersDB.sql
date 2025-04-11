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
('Alice Johnson', 'alice@example.com', 'password123', NULL, NULL, 0),
('Bob Smith', 'bob@example.com', 'securepass', NULL, NULL, 0),
('Charlie Lee', 'charlie@example.com', 'charliepass', NULL, NULL, 0),
('weired', 'weihong116@1utar.my', '$2y$10$t8l6TLubfd0wBrmupRL5E.YCGhgXTebl/uMFcAOTnxF6IuKN/0VNW', NULL, NULL, 0),
('hehe', 'hehe@mail.com', '$2y$10$imoGyBNh42LTJjiEXjZKb.ZwwrL9Tc1MWbx/XfN/FpqGwJsEP1pr.', NULL, NULL, 0),
('weihong___', 'weihong116@gmail.com', '$2y$10$WiBsYbFrWwXt38sJPqdsU.6nfZQXqdUKF/kZCwu2vjHFeKzPg7QsO', NULL, NULL, 0);