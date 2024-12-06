CREATE TABLE IF NOT EXISTS `Drivers` {
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` TINYTEXT NOT NULL,
    `birthday` DATE NOT NULL,
    `code` CHAR(3) NOT NULL,
    `number` TINYINT(2) NOT NULL,
    `nationality` TINYTEXT NOT NULL
}