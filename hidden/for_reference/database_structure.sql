DROP TABLE IF EXISTS user;

CREATE TABLE user (
	id SERIAL PRIMARY KEY,
	username VARCHAR(255) NOT NULL UNIQUE,
	password_hash VARCHAR(100) NOT NULL,
	yubikey_prefix VARCHAR(100) NOT NULL
);

INSERT INTO user(username, password_hash, yubikey_prefix) VALUES
	('alex', '$2y$10$Sg4UjcxgmRhT6zxpfkEun.Oj34sGZSVqaREkHiRoo7NjEasm3401e', 'cccccccuudjl'),
	('kathryn', '$2y$10$fjLVTIK2nyT/iKA.BnyJ5.DKByYA6zjJ9aCfGpv8ut5q3GcAz6hlm', 'cccccccncnee');