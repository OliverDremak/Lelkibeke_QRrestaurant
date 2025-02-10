CREATE DATABASE LelkibekeQR
	CHARACTER SET utf8
	COLLATE utf8_hungarian_ci;

CREATE TABLE IF NOT EXISTS `users` (
	`id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`email` varchar(255) NOT NULL,
	`password` varchar(255) NOT NULL,
	`name` varchar(255) NOT NULL,
	`role` enum NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `tables` (
	`id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`table_number` varchar(255) NOT NULL,
	`qr_code_url` text NOT NULL,
	`is_avalable` boolean NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `menu_items` (
	`id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`category_id` varchar(255) NOT NULL,
	`name` varchar(255) NOT NULL,
	`description` text NOT NULL,
	`price` decimal(10,0) NOT NULL,
	`image_url` text NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `orders` (
	`id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`user_id` int NOT NULL,
	`table_id` int NOT NULL,
	`status` text NOT NULL,
	`total_price` text NOT NULL,
	`created_at` timestamp NOT NULL,
	`updated_at` timestamp NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `order_items` (
	`id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`order_id` int NOT NULL,
	`menu_item_id` int NOT NULL,
	`quantity` int NOT NULL,
	`notes` text NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `category` (
	`id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`name` text NOT NULL,
	PRIMARY KEY (`id`)
);




ALTER TABLE `orders` ADD CONSTRAINT `orders_fk1` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`);

ALTER TABLE `orders` ADD CONSTRAINT `orders_fk2` FOREIGN KEY (`table_id`) REFERENCES `tables`(`id`);
ALTER TABLE `order_items` ADD CONSTRAINT `order_items_fk1` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`);

ALTER TABLE `order_items` ADD CONSTRAINT `order_items_fk2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`);
ALTER TABLE `category` ADD CONSTRAINT `category_fk0` FOREIGN KEY (`id`) REFERENCES `menu_items`(`category_id`);

DELIMITER //
CREATE PROCEDURE GetUsers()
BEGIN
    SELECT * FROM users;
END //
DELIMITER ;
