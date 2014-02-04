DROP TABLE IF EXISTS workout, workout_routine, tribe_leader, workout_location, tribe, user;

CREATE TABLE user (
	id SERIAL PRIMARY KEY,
	email VARCHAR(255) NOT NULL UNIQUE,
	facebook_token TEXT,
	facebook_id VARCHAR(255),
	token VARCHAR(255) NOT NULL,
	token_expiry TIMESTAMP NOT NULL,
	is_admin BOOLEAN NOT NULL DEFAULT FALSE
);

INSERT INTO user(email, token, token_expiry, is_admin) VALUES
	('admin@test.com', 'fakeToken', NOW(), TRUE),
	('bostontribeleader@test.com', 'fakeToken2', NOW(), FALSE),
	('normaluser@test.com', 'fakeToken3', NOW(), FALSE);

CREATE TABLE tribe (
	id SERIAL PRIMARY KEY,
	name VARCHAR(250) NOT NULL UNIQUE,
	latitude DECIMAL(10,7) NOT NULL,
	longitude DECIMAL(10,7) NOT NULL
);

INSERT INTO tribe(name, latitude, longitude) VALUES
	('Boston', 42.358431, -71.059773),
	('Denver', 39.737567, -104.984718),
	('Edmonton, AB', 53.544389, -113.490927),
	('San Diego', 32.715329, -117.157255),
	('Madison', 43.073052, -89.401230),
	('San Francisco', 37.774929, -122.419416),
	('Washington D.C.', 38.907231, -77.036464);
	
CREATE TABLE tribe_leader (
	id SERIAL PRIMARY KEY,
	tribe_id BIGINT UNSIGNED NOT NULL,
	user_id BIGINT UNSIGNED NOT NULL,
	FOREIGN KEY (tribe_id) REFERENCES tribe(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);

INSERT INTO tribe_leader(tribe_id, user_id) VALUES
	((SELECT id FROM tribe WHERE name='Boston'),(SELECT id FROM user WHERE email='bostontribeleader@test.com'));

CREATE TABLE workout_location (
	id SERIAL PRIMARY KEY,
	tribe_id BIGINT UNSIGNED NOT NULL,
	name VARCHAR(250) NOT NULL UNIQUE,
	latitude DECIMAL(10,7) NOT NULL,
	longitude DECIMAL(10,7) NOT NULL,
	FOREIGN KEY (tribe_id) REFERENCES tribe(id) ON DELETE CASCADE
);

INSERT INTO workout_location(tribe_id, name, latitude, longitude) VALUES
	((SELECT id FROM tribe WHERE name='Boston'), 'Harvard Stadium', 42.3673736, -71.1267865),
	((SELECT id FROM tribe WHERE name='Boston'), 'Clement G. Morgan Park (Cambridge)', 42.3649455, -71.0987097),
	((SELECT id FROM tribe WHERE name='Boston'), 'Summit Ave. (Corey Hill)', 42.3422762, -71.1320228),
	((SELECT id FROM tribe WHERE name='Boston'), 'McLaughlin Playground (Mission Hill)', 42.3281560, -71.1028877),
	((SELECT id FROM tribe WHERE name='Boston'), 'Riverside Press Park (Cambridge)', 42.3622094, -71.1153288),
	((SELECT id FROM tribe WHERE name='Boston'), 'Boston Navy Yard', 42.3742831, -71.053118),
	((SELECT id FROM tribe WHERE name='Washington D.C.'), 'Lincoln Memorial', 38.889321, -77.050166);

CREATE TABLE workout_routine (
	id SERIAL PRIMARY KEY,
	tribe_id BIGINT UNSIGNED NOT NULL,
	location_id BIGINT UNSIGNED,
	name VARCHAR(250) NOT NULL UNIQUE,
	description TEXT NOT NULL,
	FOREIGN KEY (tribe_id) REFERENCES tribe(id) ON DELETE CASCADE,
	FOREIGN KEY (location_id) REFERENCES workout_location(id) ON DELETE CASCADE
);

INSERT INTO workout_routine(tribe_id, location_id, name, description) VALUES
	((SELECT id FROM tribe WHERE name='Boston'), (SELECT id FROM workout_location WHERE name='Harvard Stadium'), 'Robot Man III', 'Three half-tours (3 x 19 sections)'),
	((SELECT id FROM tribe WHERE name='Boston'), (SELECT id FROM workout_location WHERE name='Harvard Stadium'), 'Full Tour for Time', 'Full tour (37 sections)'),
	((SELECT id FROM tribe WHERE name='Boston'), (SELECT id FROM workout_location WHERE name='Summit Ave. (Corey Hill)'), 'The Water Break', '5 x (Front hill + 10 burpees)'),
	((SELECT id FROM tribe WHERE name='Boston'), null, 'Destination Circuit #1', '5 x (Burpies/High Knees/Squats/Push Ups - 50 seconds each, 10 seconds rest between)');

CREATE TABLE workout (
	id SERIAL PRIMARY KEY,
	prescribed_routine_id BIGINT UNSIGNED NOT NULL,
	location_id BIGINT UNSIGNED,
	workout_date DATE NOT NULL,
	FOREIGN KEY (prescribed_routine_id) REFERENCES workout_routine(id),
	FOREIGN KEY (location_id) REFERENCES workout_location(id)
);

INSERT INTO workout(prescribed_routine_id, location_id, workout_date) VALUES
	((SELECT id FROM workout_routine WHERE name='Robot Man III'), null, '2014-01-06'),
	((SELECT id FROM workout_routine WHERE name='Destination Circuit #1'), (SELECT id FROM workout_location WHERE name='Clement G. Morgan Park (Cambridge)'), '2014-01-06');