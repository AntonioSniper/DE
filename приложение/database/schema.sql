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
