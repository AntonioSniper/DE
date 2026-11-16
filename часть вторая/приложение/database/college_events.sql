CREATE DATABASE IF NOT EXISTS college_events CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE college_events;
CREATE TABLE `roles` (
 `role_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `role_name` VARCHAR(50) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE `event_types` (
 `type_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `type_name` VARCHAR(50) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE `events` (
 `event_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `event_name` VARCHAR(255) NOT NULL,
 `type_id` INT UNSIGNED NOT NULL,
 FOREIGN KEY (`type_id`) REFERENCES `event_types` (`type_id`)
) ENGINE=InnoDB;
CREATE TABLE `statuses` (
 `status_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `status_name` VARCHAR(50) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE `users` (
 `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `role_id` INT UNSIGNED NOT NULL DEFAULT 2,
 `login` VARCHAR(255) NOT NULL UNIQUE,
 `password_hash` VARCHAR(255) NOT NULL,
 `full_name` VARCHAR(255) NOT NULL,
 `phone` VARCHAR(16) NOT NULL,
 `email` VARCHAR(254) NOT NULL,
 FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
) ENGINE=InnoDB;
CREATE TABLE `applications` (
 `application_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `user_id` INT UNSIGNED NOT NULL,
 `event_id` INT UNSIGNED NOT NULL,
 `event_date` DATE NOT NULL,
 `contact_phone` VARCHAR(16) NOT NULL,
 `contact_email` VARCHAR(254) NOT NULL,
 `status_id` INT UNSIGNED NOT NULL DEFAULT 1,
 FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
 FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
 FOREIGN KEY (`status_id`) REFERENCES `statuses` (`status_id`)
) ENGINE=InnoDB;
CREATE TABLE `application_members` (
 `member_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `application_id` INT UNSIGNED NOT NULL,
 `full_name` VARCHAR(255) NOT NULL,
 FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`)
) ENGINE=InnoDB;
CREATE TABLE `reviews` (
 `review_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `application_id` INT UNSIGNED NOT NULL UNIQUE,
 `review_text` TEXT NOT NULL,
 FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`)
) ENGINE=InnoDB;

USE college_events;
INSERT INTO `roles` (`role_id`,`role_name`) VALUES
(1,'Педагог-организатор'),
(2,'Пользователь');
INSERT INTO `event_types` (`type_id`,`type_name`) VALUES
(1,'Олимпиада'),
(2,'Конкурс'),
(3,'Экскурсия'),
(4,'Мастер-класс'),
(5,'Конференция');
INSERT INTO `events` (`event_id`,`event_name`,`type_id`) VALUES
(1,'Олимпиада «IT-Планета»',1),
(2,'Конкурс «Лучший по профессии»',2),
(3,'Экскурсия в музей',3),
(4,'Мастер-класс по веб-дизайну',4),
(5,'Конференция «Студенческая наука»',5);
INSERT INTO `statuses` (`status_id`,`status_name`) VALUES
(1,'Новая'),
(2,'Записан'),
(3,'Участие подтверждено');
INSERT INTO `users` (`user_id`,`role_id`,`login`,`password_hash`,`full_name`,`phone`,`email`) VALUES
(1,1,'Admin','$2y$10$Q2spvZxSRRfzks70ZuWsEuD5CY8f9yVVG2criTxjSxYGKYeE1M4TO','Педагог организатор','8(800)555-35-35','admin@example.com');
