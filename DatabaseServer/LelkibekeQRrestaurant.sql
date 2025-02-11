CREATE DATABASE LelkibekeQR
	CHARACTER SET utf8
	COLLATE utf8_hungarian_ci;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'user', 'waiter') NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE (`email`)
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
	`user_id` int NOT NULL,
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
-- Mukodik
-- CALL sendOrder(
--     1, -- user_id
--     3, -- table_id
--     29.99, -- total_price
--     '[{"menu_item_id": 2, "quantity": 1, "notes": "Extra cheese"},
--       {"menu_item_id": 5, "quantity": 2, "notes": "No onions"}]' -- order items JSON
-- );


