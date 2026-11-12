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
