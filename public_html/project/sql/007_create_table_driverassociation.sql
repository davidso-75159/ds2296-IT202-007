CREATE TABLE IF NOT EXISTS `DriverAssociation` (
    `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` int NOT NULL,
    `driver_id` int NOT NULL,
    `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`driver_id`) REFERENCES Drivers (`id`),
    FOREIGN KEY (`user_id`) REFERENCES Users (`id`),
    unique key (`driver_id`, `user_id`)
)