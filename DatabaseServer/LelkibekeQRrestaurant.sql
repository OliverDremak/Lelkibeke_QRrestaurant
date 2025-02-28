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
	`is_available` boolean NOT NULL,
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
	`status` enum('pending', 'cooking', 'cooked', 'served') NOT NULL DEFAULT 'pending',
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

CREATE TABLE IF NOT EXISTS `opening_hours` (
    `id` int AUTO_INCREMENT NOT NULL UNIQUE,
    `day_of_week` varchar(20) NOT NULL,
    `open_time` time,
    `close_time` time,
    `is_closed` boolean DEFAULT false,
    PRIMARY KEY (`id`)
);

CREATE TABLE `coupons` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `code` VARCHAR(255) NOT NULL UNIQUE,
    `discount` DECIMAL(5, 2) NOT NULL,
    `is_used` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP
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
INSERT INTO tables (table_number, qr_code_url, is_available) VALUES
('T1', 'https://example.com/qr1', true),
('T2', 'https://example.com/qr2', false),
('T3', 'https://example.com/qr3', true),
('T4', 'https://example.com/qr4', true),
('T5', 'https://example.com/qr5', false),
('T6', 'https://example.com/qr6', true);

-- MODIFIED: Insert new categories instead of previous ones
INSERT INTO category (name) VALUES
('Burger'),
('Pizza'),
('Sushi');

-- MODIFIED: Insert new menu items with updated image URLs
-- Original menu items INSERT with shortened descriptions (8 words maximum)
-- Locate the existing INSERT statements for menu items and replace with these
INSERT INTO menu_items (category_id, name, description, price, image_url) VALUES
(1, 'Boombox Burger', 'Hearty burger with rhythm in every bite.', 2800, 'https://bgs.jedlik.eu/innerpeace/images/boombox_burger.jpeg'),
(1, 'Cupido Burger', 'Love-infused burger to steal your heart.', 2900, 'https://bgs.jedlik.eu/innerpeace/images/cupido_burger.jpeg'),
(1, 'Mammut Burger', 'Colossal patty with prehistoric flavors.', 3200, 'https://bgs.jedlik.eu/innerpeace/images/mamut_burger.png'),
(1, 'Sci-Fi Burger', 'Futuristic flavors from the galactic depths.', 3100, 'https://bgs.jedlik.eu/innerpeace/images/scifi_burger.jpeg'),
(1, 'Gamer Burger', 'Energy-packed burger for marathon gaming sessions.', 3000, 'https://bgs.jedlik.eu/innerpeace/images/gamer_burger.jpeg'),
(2, 'Inferno Pizza', 'Fiery spices that ignite your taste buds.', 3200, 'https://bgs.jedlik.eu/innerpeace/images/inferno_pizza.png'),
(2, 'Colosseum Pizza', 'Grand mix reminiscent of ancient feasts.', 3100, 'https://bgs.jedlik.eu/innerpeace/images/colosseum_pizza.png'),
(2, 'Party Pizza', 'Festive toppings perfect for any celebration.', 3000, 'https://bgs.jedlik.eu/innerpeace/images/party_pizza.png'),
(2, 'Samurai Pizza', 'Bold flavors with refined warrior balance.', 3300, 'https://bgs.jedlik.eu/innerpeace/images/samurai_pizza.png'),
(3, 'Anime Maki', 'Vibrant ingredients inspired by Japanese animation.', 3400, 'https://bgs.jedlik.eu/innerpeace/images/anime_maki.png'),
(3, 'Robot Nigiri', 'Precision-cut fish with futuristic presentation.', 3500, 'https://bgs.jedlik.eu/innerpeace/images/robot_nigiri.png'),
(3, 'Volcano Sushi', 'Spicy toppings that erupt with flavor.', 3600, 'https://bgs.jedlik.eu/innerpeace/images/volcano_sushi.png'),
(3, 'Phoenix Roll', 'Fiery and fresh flavors in harmony.', 3300, 'https://bgs.jedlik.eu/innerpeace/images/phoenix_roll.png');

-- Clear existing order data
SET SQL_SAFE_UPDATES = 0;

-- Safe delete with WHERE clause using primary keys
DELETE FROM order_items WHERE id > 0;
DELETE FROM orders WHERE id > 0;

-- Insert sample orders with various statuses
INSERT INTO orders (user_id, table_id, status, total_price, created_at) VALUES
-- Pending orders (just received)
(2, 1, 'pending', '4500', NOW() - INTERVAL 2 MINUTE),
(2, 3, 'pending', '3200', NOW() - INTERVAL 1 MINUTE),

-- Orders being cooked
(3, 2, 'cooking', '2800', NOW() - INTERVAL 15 MINUTE),
(3, 4, 'cooking', '5100', NOW() - INTERVAL 10 MINUTE),

-- Orders ready to be served
(2, 5, 'cooked', '3600', NOW() - INTERVAL 8 MINUTE),
(4, 6, 'cooked', '4200', NOW() - INTERVAL 5 MINUTE),

-- Orders that have been served
(3, 1, 'served', '2900', NOW() - INTERVAL 30 MINUTE),
(4, 2, 'served', '3800', NOW() - INTERVAL 25 MINUTE);

-- Insert corresponding order items
INSERT INTO order_items (order_id, menu_item_id, quantity, notes) VALUES
-- For pending orders
(1, 1, 2, 'Extra cheese'),
(1, 3, 1, 'No onions'),
(2, 2, 1, 'Well done'),
(2, 6, 2, 'No ice'),

-- For cooking orders
(3, 4, 2, 'Extra spicy'),
(3, 6, 2, 'With lemon'),
(4, 1, 1, 'No basil'),
(4, 2, 2, 'Extra toppings'),

-- For cooked orders
(5, 3, 2, 'No pickles'),
(5, 5, 1, 'Extra sauce'),
(6, 4, 2, 'Regular spice'),
(6, 6, 2, 'Extra ice'),

-- For served orders
(7, 2, 1, 'Medium rare'),
(7, 6, 1, 'No ice'),
(8, 1, 2, 'Extra cheese'),
(8, 5, 1, 'Gluten free');

-- Insert default opening hours
INSERT INTO `opening_hours` (day_of_week, open_time, close_time, is_closed) VALUES
('Monday', '09:00:00', '18:00:00', false),
('Tuesday', '09:00:00', '18:00:00', false),
('Wednesday', '09:00:00', '18:00:00', false),
('Thursday', '09:00:00', '18:00:00', false),
('Friday', '09:00:00', '18:00:00', false),
('Saturday', '10:00:00', '16:00:00', false),
('Sunday', NULL, NULL, true);

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
CREATE PROCEDURE GetTables()
BEGIN
    SELECT * FROM tables;
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
    INSERT INTO `tables` (table_number, qr_code_url, is_available)
    VALUES (p_table_number, p_qr_code_url, p_is_available);
END //

DELIMITER //
CREATE PROCEDURE ModifyTableById(IN p_table_id INT, IN p_table_number VARCHAR(255), IN p_qr_code_url TEXT, IN p_is_available BOOLEAN)
BEGIN
    UPDATE `tables`
    SET table_number = p_table_number,
        qr_code_url = p_qr_code_url,
        is_available = p_is_available
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
        o.id AS order_id,
        o.table_id,
        o.created_at AS order_date,
        o.status,
        o.total_price,
        CONCAT('[', GROUP_CONCAT(
            JSON_OBJECT(
                'quantity', oi.quantity,
                'menu_item_name', mi.name,
                'notes', COALESCE(NULLIF(oi.notes, ''), 'null')
            )
        ), ']') as items
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN menu_items mi ON oi.menu_item_id = mi.id
    WHERE o.table_id = p_table_id
    AND o.status IN ('pending', 'cooking', 'cooked')
    GROUP BY o.id, o.table_id, o.created_at, o.status, o.total_price
    ORDER BY o.created_at DESC;
END //

DELIMITER ;

DELIMITER //

CREATE PROCEDURE SetOrderStatusById(
    IN p_order_id INT,
    IN p_status ENUM('pending', 'cooking', 'cooked', 'served')
)
BEGIN
    UPDATE `orders`
    SET status = p_status
    WHERE id = p_order_id;
    
    -- Return the updated order data
    SELECT 
        id AS order_id,
        status
    FROM orders
    WHERE id = p_order_id;
END//

DELIMITER ;

DELIMITER //
CREATE PROCEDURE SetTableOccupancyStatus(IN p_table_id INT, IN p_occupied BOOLEAN)
BEGIN
    UPDATE `tables`
    SET is_available = p_occupied
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
    VALUES (p_user_id, p_table_id, 'pending', p_total_price, NOW());
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




-- CALL RegisterUser('john@example.com', 'password123', 'John Doe', NULL); -- Mukodik
-- CALL LoginUser('john@example.com', 'password123'); -- Mukodik

DELIMITER //

CREATE PROCEDURE GetActiveOrdersForTableById(IN p_table_id INT)
BEGIN
    SELECT 
        orders.id AS order_id,
        orders.created_at AS order_date,
        orders.status,
        orders.total_price,
        menu_items.name AS menu_item_name,
        order_items.quantity,
        order_items.notes
    FROM orders
    JOIN order_items ON orders.id = order_items.order_id
    JOIN menu_items ON order_items.menu_item_id = menu_items.id
    WHERE orders.table_id = p_table_id
    AND orders.status IN ('pending', 'cooking', 'cooked') -- Include all active statuses
    ORDER BY orders.created_at DESC;
END //

DELIMITER //


CREATE PROCEDURE GetDailySales()
BEGIN
    SELECT DATE(created_at) AS sale_date, 
           SUM(CAST(total_price AS DECIMAL(10,2))) AS total_sales
    FROM orders
    GROUP BY sale_date
    ORDER BY total_sales DESC;
END //

DELIMITER ;


DELIMITER //

CREATE PROCEDURE GetTopSellingItems()
BEGIN
    SELECT mi.name AS menu_item, 
           SUM(oi.quantity) AS total_sold, 
           mi.description AS menu_item_desc
    FROM order_items oi
    JOIN menu_items mi ON oi.menu_item_id = mi.id
    GROUP BY mi.name, mi.description
    ORDER BY total_sold DESC
    LIMIT 10;
END //

DELIMITER ;

DELIMITER //

CREATE PROCEDURE GetSalesSummary()
BEGIN
    SELECT COUNT(id) AS total_orders, 
           SUM(CAST(total_price AS DECIMAL(10,2))) AS total_revenue, 
           AVG(CAST(total_price AS DECIMAL(10,2))) AS average_order_value
    FROM orders;
END //

DELIMITER ;

DELIMITER //

CREATE PROCEDURE GetAllActiveOrders()
BEGIN
    SELECT 
        orders.id AS order_id,
        orders.table_id,
        orders.created_at AS order_date,
        orders.status,
        orders.total_price,
        menu_items.name AS menu_item_name,
        order_items.quantity,
        order_items.notes
    FROM orders
    JOIN order_items ON orders.id = order_items.order_id
    JOIN menu_items ON order_items.menu_item_id = menu_items.id
    WHERE orders.status IN ('pending', 'cooking', 'cooked') -- Include all active statuses
    ORDER BY orders.created_at DESC;
END //

DELIMITER ;

DELIMITER //

CREATE PROCEDURE GetOpeningHours()
BEGIN
    SELECT * FROM opening_hours ORDER BY 
        CASE day_of_week
            WHEN 'Monday' THEN 1
            WHEN 'Tuesday' THEN 2
            WHEN 'Wednesday' THEN 3
            WHEN 'Thursday' THEN 4
            WHEN 'Friday' THEN 5
            WHEN 'Saturday' THEN 6
            WHEN 'Sunday' THEN 7
        END;
END //

DELIMITER ;

DELIMITER //

CREATE PROCEDURE UpdateOpeningHours(
    IN p_day_of_week VARCHAR(20),
    IN p_open_time TIME,
    IN p_close_time TIME,
    IN p_is_closed BOOLEAN
)
BEGIN
    UPDATE opening_hours 
    SET open_time = p_open_time,
        close_time = p_close_time,
        is_closed = p_is_closed
    WHERE day_of_week = p_day_of_week;
END //

DELIMITER ;

CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` int AUTO_INCREMENT NOT NULL UNIQUE,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `message` text NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

DELIMITER //

CREATE PROCEDURE CreateContactMessage(
    IN p_name VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_message TEXT
)
BEGIN
    INSERT INTO contact_messages (name, email, message)
    VALUES (p_name, p_email, p_message);
    SELECT LAST_INSERT_ID() as message_id;
END //

CREATE PROCEDURE GetAllContactMessages()
BEGIN
    SELECT * FROM contact_messages ORDER BY created_at DESC;
END //

DELIMITER ;

DELIMITER //


DROP PROCEDURE IF EXISTS GetPendingOrders //

CREATE PROCEDURE GetPendingOrders()
BEGIN
    SELECT 
        o.id AS order_id,
        o.table_id,
        o.created_at AS order_date,
        o.status,
        o.total_price,
        CONCAT('[', GROUP_CONCAT(
            JSON_OBJECT(
                'quantity', oi.quantity,
                'menu_item_name', mi.name,
                'notes', COALESCE(NULLIF(oi.notes, ''), 'null')
            )
        ), ']') as items
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN menu_items mi ON oi.menu_item_id = mi.id
    WHERE o.status IN ('pending', 'cooking')
    GROUP BY o.id, o.table_id, o.created_at, o.status, o.total_price
    ORDER BY o.created_at;
END //

DELIMITER ;


DELIMITER //

CREATE PROCEDURE GenerateCoupon(IN p_user_id BIGINT UNSIGNED)
BEGIN
    DECLARE v_order_count INT;
    DECLARE v_coupon_count INT;
    DECLARE v_coupon_code VARCHAR(255);
    
    -- Count the number of orders for the user
    SELECT COUNT(*) INTO v_order_count FROM orders WHERE user_id = p_user_id;
    
    -- Count existing coupons for the user
    SELECT COUNT(*) INTO v_coupon_count FROM coupons WHERE user_id = p_user_id;
    
    -- Check if user should get a new coupon (order count is multiple of 10 and greater than existing coupons * 10)
    IF v_order_count >= 10 AND v_order_count MOD 10 = 0 AND v_order_count > (v_coupon_count * 10) THEN
        SET v_coupon_code = CONCAT('DISCOUNT-', UUID());
        INSERT INTO coupons (user_id, code, discount, expires_at)
        VALUES (p_user_id, v_coupon_code, 10.00, DATE_ADD(NOW(), INTERVAL 1 YEAR));
    END IF;
END //
DELIMITER //

DELIMITER //
CREATE PROCEDURE GetAllCoupons()
BEGIN
    SELECT * FROM coupons;
END //
DELIMITER //

DELIMITER //
CREATE PROCEDURE GetCouponById(IN p_coupon_id INT)
BEGIN
    SELECT * FROM coupons WHERE id = p_coupon_id;
END //
DELIMITER //

DELIMITER //
CREATE PROCEDURE GetCouponsByUserId(IN p_user_id BIGINT UNSIGNED)
BEGIN
    SELECT * FROM coupons WHERE user_id = p_user_id;
END //
DELIMITER //

DELIMITER //

CREATE PROCEDURE GetUserById(IN p_user_id BIGINT UNSIGNED)
BEGIN
    SELECT id, name, email, role
    FROM users
    WHERE id = p_user_id;
END //

DELIMITER //

CREATE PROCEDURE UpdateUser(
    IN p_user_id BIGINT UNSIGNED,
    IN p_name VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_current_password VARCHAR(255),
    IN p_new_password VARCHAR(255)
)
BEGIN
    DECLARE current_stored_password VARCHAR(255);
    
    -- Get current password hash
    SELECT password INTO current_stored_password
    FROM users
    WHERE id = p_user_id;
    
    -- Verify current password if provided
    IF p_current_password IS NOT NULL THEN
        IF SHA2(p_current_password, 256) != current_stored_password THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Current password is incorrect';
        END IF;
    END IF;
    
    -- Update user information
    UPDATE users 
    SET name = p_name,
        email = p_email,
        password = CASE 
            WHEN p_new_password IS NOT NULL THEN SHA2(p_new_password, 256)
            ELSE password
        END,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_user_id;
END //

DELIMITER //

DROP PROCEDURE IF EXISTS GetUserOrders //

CREATE PROCEDURE GetUserOrders(IN p_user_id BIGINT UNSIGNED)
BEGIN
    SELECT 
        o.id,
        o.created_at,
        CAST(o.total_price AS DECIMAL(10,2)) as total_price,
        o.status,
        GROUP_CONCAT(
            CONCAT(
                '{"id":', oi.id,
                ',"name":"', mi.name,
                '","quantity":', oi.quantity,
                ',"price":', CAST(mi.price * oi.quantity AS DECIMAL(10,2)),
                ',"notes":"', COALESCE(oi.notes, ''),
                '"}'
            )
        ) as items
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN menu_items mi ON oi.menu_item_id = mi.id
    WHERE o.user_id = p_user_id
    GROUP BY o.id, o.created_at, o.total_price, o.status
    ORDER BY o.created_at DESC;
END //

DELIMITER ;
