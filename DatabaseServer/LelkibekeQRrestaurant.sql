CREATE DATABASE LelkibekeQR
	CHARACTER SET utf8
	COLLATE utf8_hungarian_ci;

CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(255),
    `remember_token` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `personal_access_tokens` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `tokenable_type` varchar(255) NOT NULL,
    `tokenable_id` bigint unsigned NOT NULL,
    `name` varchar(255) NOT NULL,
    `token` varchar(64) NOT NULL UNIQUE,
    `abilities` text DEFAULT NULL,
    `last_used_at` timestamp NULL DEFAULT NULL,
    `expires_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL
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
	`category_id` int NOT NULL,
	`name` varchar(255) NOT NULL,
	`description` text NOT NULL,
	`price` decimal(10,0) NOT NULL,
	`image_url` text NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `orders` (
	`id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`user_id` BIGINT UNSIGNED NOT NULL,
	`table_id` int NOT NULL,
	`status` enum('cooking', 'done') NOT NULL,
	`total_price` text NOT NULL,
	`created_at` timestamp NOT NULL,
-- 	`updated_at` timestamp NOT NULL,
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
ALTER TABLE `menu_items` ADD CONSTRAINT `menu_items_fk1` FOREIGN KEY (`category_id`) REFERENCES `category`(`id`);


ALTER TABLE `personal_access_tokens` MODIFY `tokenable_id` BIGINT UNSIGNED NOT NULL;
ALTER TABLE `personal_access_tokens` ADD CONSTRAINT `personal_access_tokens_user_id_fk` FOREIGN KEY (`tokenable_id`) REFERENCES `users`(`id`) ON DELETE CASCADE;

-- Insert into users
INSERT INTO users (email, password, name, role) VALUES
('admin@example.com', 'hashedpassword1', 'Admin User', 'admin'),
('user1@example.com', 'hashedpassword2', 'John Doe', 'user'),
('user2@example.com', 'hashedpassword3', 'Jane Smith', 'user'),
('waiter1@example.com', 'hashedpassword4', 'Michael Scott', 'waiter');

-- Insert into tables
INSERT INTO tables (table_number, qr_code_url, is_avalable) VALUES
('T1', 'https://example.com/qr1', true),
('T2', 'https://example.com/qr2', false),
('T3', 'https://example.com/qr3', true);

-- Insert into category
INSERT INTO category (name) VALUES
('Pizza'),
('Burger'),
('Pasta'),
('Drinks');

-- Insert into menu_items
INSERT INTO menu_items (category_id, name, description, price, image_url) VALUES
(1, 'Margherita Pizza', 'Classic Italian pizza with mozzarella and tomato sauce', 2500, 'https://example.com/margherita.jpg'),
(1, 'Pepperoni Pizza', 'Spicy pepperoni with mozzarella cheese', 2800, 'https://example.com/pepperoni.jpg'),
(2, 'Cheeseburger', 'Juicy beef patty with cheese and lettuce', 2000, 'https://example.com/cheeseburger.jpg'),
(2, 'Chicken Burger', 'Crispy chicken patty with mayo and lettuce', 2200, 'https://example.com/chickenburger.jpg'),
(3, 'Spaghetti Bolognese', 'Classic pasta with meat sauce', 2300, 'https://example.com/spaghetti.jpg'),
(4, 'Coke', 'Refreshing Coca-Cola', 600, 'https://example.com/coke.jpg');

-- Insert into orders
INSERT INTO orders (user_id, table_id, status, total_price, created_at) VALUES
(2, 1, 'cooking', '4500', NOW()),
(3, 2, 'done', '2800', NOW()),
(2, 3, 'cooking', '2300', NOW());

-- Insert into order_items
INSERT INTO order_items (order_id, menu_item_id, quantity, notes) VALUES
(1, 1, 1, 'Extra cheese'),
(1, 3, 1, 'No onions'),
(3, 5, 1, 'Gluten-free pasta');


DELIMITER //
CREATE PROCEDURE GetUsers()
BEGIN
    SELECT * FROM users;
END //

DELIMITER //
CREATE PROCEDURE GetMenu()
BEGIN
  SELECT 
    menu_items.id, 
    category.name AS category_name, 
    menu_items.name, 
    menu_items.description, 
    menu_items.price, 
    menu_items.image_url
  FROM menu_items
  JOIN category ON menu_items.category_id = category.id;
END //



DELIMITER //
CREATE PROCEDURE GetActiveOrders()
BEGIN
    SELECT 
        orders.table_id, 
        GROUP_CONCAT(menu_items.name ORDER BY menu_items.name SEPARATOR ', ') AS menu_items
    FROM orders
    JOIN order_items ON orders.id = order_items.order_id
    JOIN menu_items ON order_items.menu_item_id = menu_items.id
    WHERE orders.status = 'cooking'
    GROUP BY orders.table_id;
END //


DELIMITER //
CREATE PROCEDURE GetAllOrderedItems()
BEGIN
    SELECT GROUP_CONCAT(menu_items.name SEPARATOR ', ')
    FROM orders
    JOIN order_items ON orders.id = order_items.order_id
    JOIN menu_items ON order_items.menu_item_id = menu_items.id
    WHERE orders.status = 'cooking'
    ORDER BY menu_items.name;
END //


DELIMITER //
CREATE PROCEDURE CreateNewMenuItem(IN p_category_id INT, IN p_name VARCHAR(255), IN p_description TEXT, IN p_price DECIMAL(10,0), IN p_image_url TEXT)
BEGIN
    INSERT INTO menu_items (category_id, name, description, price, image_url)
    VALUES (p_category_id, p_name, p_description, p_price, p_image_url);
END //

DELIMITER //
CREATE PROCEDURE ModifyMenuItemById(IN p_menu_item_id INT, IN p_category_id INT, IN p_name VARCHAR(255), IN p_description TEXT, IN p_price DECIMAL(10,0), IN p_image_url TEXT)
BEGIN
    UPDATE menu_items
    SET category_id = p_category_id,
        name = p_name,
        description = p_description,
        price = p_price,
        image_url = p_image_url
    WHERE id = p_menu_item_id;
END //

DELIMITER //
CREATE PROCEDURE DeleteMenuItemById(IN p_menu_item_id INT)
BEGIN
    DELETE FROM menu_items WHERE id = p_menu_item_id;
END //

DELIMITER //
CREATE PROCEDURE CreateNewTable(IN p_table_number VARCHAR(255), IN p_qr_code_url TEXT, IN p_is_available BOOLEAN)
BEGIN
    INSERT INTO `tables` (table_number, qr_code_url, is_avalable)
    VALUES (p_table_number, p_qr_code_url, p_is_available);
END //

DELIMITER //
CREATE PROCEDURE ModifyTableById(IN p_table_id INT, IN p_table_number VARCHAR(255), IN p_qr_code_url TEXT, IN p_is_available BOOLEAN)
BEGIN
    UPDATE `tables`
    SET table_number = p_table_number,
        qr_code_url = p_qr_code_url,
        is_avalable = p_is_available
    WHERE id = p_table_id;
END //

DELIMITER //
CREATE PROCEDURE DeleteTableById(
    IN p_table_id INT
)
BEGIN
    DELETE FROM `tables` WHERE id = p_table_id;
END //


-- CALL CreateNewMenuItem(1, 'Geronimo Pizza', 'Geronimo Italian pizza with tomato sauce and mozzarella', 2500, 'https://example.com/geronimo.jpg');
-- CALL ModifyMenuItemById(7, 1, 'Jojo Pizza', 'Jojo pizza with pepperoni and extra cheese', 2800, 'https://example.com/jojo.jpg');
-- CALL DeleteMenuItemById(7);
-- CALL CreateNewTable('T50', 'https://example.com/qr50', true);
-- CALL ModifyTableById(4, 'T355', 'https://example.com/qr355', false);
-- CALL DeleteTableById(4);
DELIMITER //
CREATE PROCEDURE GetOrdersForTableById(IN p_table_id INT)
BEGIN
    SELECT 
        GROUP_CONCAT(menu_items.name ORDER BY menu_items.name SEPARATOR ', ') AS menu_items
    FROM orders
    JOIN order_items ON orders.id = order_items.order_id
    JOIN menu_items ON order_items.menu_item_id = menu_items.id
    WHERE orders.status = 'cooking'
      AND orders.table_id = p_table_id
    GROUP BY orders.table_id;
END //

DELIMITER //
CREATE PROCEDURE SetTableOccupancyStatus(IN p_table_id INT, IN p_occupied BOOLEAN)
BEGIN
    UPDATE `tables`
    SET is_avalable = p_occupied
    WHERE id = p_table_id;
END //

DELIMITER //

CREATE PROCEDURE sendOrder(
    IN p_user_id INT,
    IN p_table_id INT,
    IN p_total_price DECIMAL(10,2),
    IN p_items JSON
)
BEGIN
    DECLARE v_order_id INT;
    INSERT INTO orders (user_id, table_id, status, total_price, created_at)
    VALUES (p_user_id, p_table_id, 'cooking', p_total_price, NOW());
    SET v_order_id = LAST_INSERT_ID();
    SET @json = p_items;
    SET @i = 0;
    SET @length = JSON_LENGTH(@json);

    WHILE @i < @length DO
        INSERT INTO order_items (order_id, menu_item_id, quantity, notes)
        VALUES (
            v_order_id,
            JSON_UNQUOTE(JSON_EXTRACT(@json, CONCAT('$[', @i, '].menu_item_id'))),
            JSON_UNQUOTE(JSON_EXTRACT(@json, CONCAT('$[', @i, '].quantity'))),
            JSON_UNQUOTE(JSON_EXTRACT(@json, CONCAT('$[', @i, '].notes')))
        );
        SET @i = @i + 1;
    END WHILE;
    SELECT 'Order placed successfully' AS message, v_order_id AS order_id;
END //

DELIMITER ;


-- LOGIN / REGISTER functions


DELIMITER $$

CREATE PROCEDURE RegisterUser(
    IN p_email VARCHAR(255), 
    IN p_password VARCHAR(255), 
    IN p_name VARCHAR(255),
    IN p_role ENUM('admin', 'user', 'waiter') -- Role is REQUIRED (no default in table)
)
BEGIN
    DECLARE hashed_password VARCHAR(255);
    DECLARE default_role ENUM('admin', 'user', 'waiter') DEFAULT 'user';

    SET hashed_password = SHA2(p_password, 256);
    
    INSERT INTO users (name, email, password, role) -- Match your table's columns
    VALUES (
        p_name, 
        p_email, 
        hashed_password, 
        COALESCE(p_role, default_role) -- Use provided role or default to 'user'
    );

    SELECT LAST_INSERT_ID() AS user_id;
END$$

DELIMITER ;




DELIMITER $$
CREATE PROCEDURE LoginUser(
    IN p_email VARCHAR(255)
)
BEGIN
    SELECT 
        id, 
        name, 
        role, 
        password 
    FROM users 
    WHERE email = p_email;
END$$
DELIMITER ;




CALL RegisterUser('john@example.com', 'password123', 'John Doe', NULL); -- Mukodik
CALL LoginUser('john@example.com', 'password123'); -- Mukodik

