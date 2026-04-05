CREATE DATABASE penpalace_db CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_unicode_ci;

USE penpalace_db;

-- Create users table first since it's referenced by other tables
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    role ENUM ('user', 'admin') NOT NULL DEFAULT 'user',
    is_active TINYINT (1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Insert default admin account (password: Admin@1234)
INSERT INTO
    users (name, email, password_hash, role)
VALUES
    (
        'Admin',
        'admin@penpalace.com',
        '$2y$10$kLSo73c1irJfObvQnPuVv.BNh9J3d6sq3BPftHRY0A0LdkiUo8EGu',
        'admin'
    );

-- Create categories table next since it's referenced by products
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Insert default product categories
INSERT INTO
    categories (name, description)
VALUES
    ('Pens', 'High-quality writing instruments'),
    ('Notebooks', 'Durable and stylish notebooks'),
    ('Bags', 'Versatile and functional bags'),
    ('Accessories', 'Essential accessories for everyday use');

-- Create products table next since it's referenced by cart_items, order_items, and wishlist_items
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    discount_price DECIMAL(10, 2) DEFAULT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    category_id INT UNSIGNED NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    additional_images JSON DEFAULT NULL,
    specifications JSON DEFAULT NULL,
    is_active TINYINT (1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_category_id (category_id),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Placeholder Products for Accessories
INSERT INTO
    products (
        name,
        description,
        price,
        discount_price,
        stock,
        category_id,
        image_url,
        additional_images,
        specifications,
        created_at
    )
VALUES
    (
        'Túi dùng cụ',
        'cất dùng cụ.',
        19.99,
        17.99,
        10,
        4,
        'assets/images/placeholders/accessories/accessory1_main.webp',
        '["assets/images/placeholders/accessories/accessory1_additional1.webp"]',
        '{"color": "Black", "material": "Silicone"}',
        '2023-01-15 10:00:00'
    ),
    (
        'Thước súng',
        'Dùng cụ cho trẻ em.',
        24.99,
        NULL,
        15,
        4,
        'assets/images/placeholders/accessories/accessory2_main.webp',
        '["assets/images/placeholders/accessories/accessory2_additional1.webp"]',
        '{"color": "White", "material": "Plastic"}',
        '2023-02-22 14:30:00'
    ),
    (
        'Gọt bút',
        'Sử dụng khi không còn nhân.',
        29.99,
        26.99,
        20,
        4,
        'assets/images/placeholders/accessories/accessory3_main.webp',
        '["assets/images/placeholders/accessories/accessory3_additional1.webp","assets/images/placeholders/accessories/accessory3_additional2.webp"]',
        '{"color": "Brown", "material": "Rubber"}',
        '2023-03-10 09:45:00'
    ),
    (
        'Gom pa',
        'Dùng để làm hình tròn.',
        34.99,
        NULL,
        25,
        4,
        'assets/images/placeholders/accessories/accessory4_main.webp',
        '["assets/images/placeholders/accessories/accessory4_additional1.webp","assets/images/placeholders/accessories/accessory4_additional2.webp"]',
        '{"color": "Silver", "material": "Metal"}',
        '2024-12-01 11:15:00'
    ),
    (
        'Cái Xoá',
        'Xoá chữ cái.',
        39.99,
        35.99,
        30,
        4,
        'assets/images/placeholders/accessories/accessory5_main.webp',
        '["assets/images/placeholders/accessories/accessory5_additional1.webp", "assets/images/placeholders/accessories/accessory5_additional2.webp"]',
        '{"color": "Silver", "material": "Plastic"}',
        '2023-05-15 13:00:00'
    ),
    (
        'Tẩy',
        'Tẩy xoá chữ.',
        44.99,
        NULL,
        35,
        4,
        'assets/images/placeholders/accessories/accessory6_main.webp',
        '["assets/images/placeholders/accessories/accessory6_additional1.webp"]',
        '{"color": "Gray", "material": "Rubber"}',
        '2023-06-20 14:45:00'
    ),
    (
        ' Thước Loại A',
        'Dùng để Đo.',
        49.99,
        44.99,
        40,
        4,
        'assets/images/placeholders/accessories/accessory7_main.webp',
        '["assets/images/placeholders/accessories/accessory7_additional1.webp"]',
        '{"color": "Gray", "polarization": "Yes"}',
        '2023-07-05 16:30:00'
    ),
    (
        'Set thước ',
        'Set thước.',
        54.99,
        NULL,
        45,
        4,
        'assets/images/placeholders/accessories/accessory8_main.webp',
        '["assets/images/placeholders/accessories/accessory8_additional1.webp"]',
        '{"color": "Yellow", "material": "Steel"}',
        '2024-12-01 10:15:00'
    ),
    (
        'Thước',
        'Thước đo chiều dài',
        59.99,
        55.99,
        50,
        4,
        'assets/images/placeholders/accessories/accessory9_main.webp',
        '["assets/images/placeholders/accessories/accessory9_additional1.webp"]',
        '{"color": "Silver", "material": "Steel"}',
        '2023-09-25 12:00:00'
    ),
    (
        'Túi bút',
        'True Túi bút.',
        64.99,
        NULL,
        55,
        4,
        'assets/images/placeholders/accessories/accessory10_main.webp',
        '["assets/images/placeholders/accessories/accessory10_additional1.webp","assets/images/placeholders/accessories/accessory10_additional2.webp"]',
        '{"color": "Yellow", "material": "Plastic"}',
        '2023-10-10 13:45:00'
    );

-- Placeholder Products for Bags
INSERT INTO
    products (
        name,
        description,
        price,
        discount_price,
        stock,
        category_id,
        image_url,
        additional_images,
        specifications,
        created_at
    )
VALUES
    (
        'Canvas Tote Bag',
        'Eco-friendly canvas tote bag with spacious interior.',
        79.99,
        71.99,
        10,
        3,
        'assets/images/placeholders/bags/bag1_main.webp',
        '["assets/images/placeholders/bags/bag1_additional1.webp","assets/images/placeholders/bags/bag1_additional2.webp"]',
        '{"color": "Green", "material": "Canvas"}',
        '2023-01-20 08:00:00'
    ),
    (
        'Leather Briefcase',
        'Professional leather briefcase for business use.',
        84.99,
        NULL,
        15,
        3,
        'assets/images/placeholders/bags/bag2_main.webp',
        '["assets/images/placeholders/bags/bag2_additional1.webp"]',
        '{"color": "Black", "material": "Leather"}',
        '2024-11-28 10:30:00'
    ),
    (
        'Coffee Backpack',
        'Durable coffee backpack with multiple compartments.',
        89.99,
        80.99,
        20,
        3,
        'assets/images/placeholders/bags/bag3_main.webp',
        NULL,
        '{"color": "Gray", "capacity": "40L"}',
        '2024-11-15 14:00:00'
    ),
    (
        'Messenger Bag',
        'Stylish messenger bag for everyday use.',
        94.99,
        NULL,
        25,
        3,
        'assets/images/placeholders/bags/bag4_main.webp',
        '["assets/images/placeholders/bags/bag4_additional1.webp","assets/images/placeholders/bags/bag4_additional2.webp"]',
        '{"color": "Red", "material": "Nylon"}',
        '2023-04-10 16:15:00'
    ),
    (
        'Shoulder Bag',
        'Fashionable shoulder bag with adjustable straps.',
        99.99,
        89.99,
        30,
        3,
        'assets/images/placeholders/bags/bag5_main.webp',
        '["assets/images/placeholders/bags/bag5_additional1.webp","assets/images/placeholders/bags/bag5_additional2.webp"]',
        '{"color": "Pink", "capacity": "20L"}',
        '2023-05-05 11:30:00'
    ),
    (
        'Hiking Backpack',
        'High-capacity hiking backpack for outdoor adventures.',
        104.99,
        NULL,
        35,
        3,
        'assets/images/placeholders/bags/bag6_main.webp',
        '["assets/images/placeholders/bags/bag6_additional1.webp","assets/images/placeholders/bags/bag6_additional2.webp"]',
        '{"color": "Olive", "capacity": "50L"}',
        '2023-06-20 13:45:00'
    ),
    (
        'Laptop Backpack',
        'Protective backpack with padded compartment for laptops.',
        109.99,
        99.99,
        40,
        3,
        'assets/images/placeholders/bags/bag7_main.webp',
        '["assets/images/placeholders/bags/bag7_additional1.webp","assets/images/placeholders/bags/bag7_additional2.webp"]',
        '{"color": "Blue", "compatibility": "15-inch laptops"}',
        '2023-07-10 15:00:00'
    ),
    (
        'Crossbody Bag',
        'Compact crossbody bag for casual outings.',
        114.99,
        NULL,
        45,
        3,
        'assets/images/placeholders/bags/bag8_main.webp',
        '["assets/images/placeholders/bags/bag8_additional1.webp","assets/images/placeholders/bags/bag8_additional2.webp"]',
        '{"color": "Yellow", "capacity": "10L"}',
        '2023-08-15 12:15:00'
    ),
    (
        'Weekender Bag',
        'Weekender bag for short trips and weekend getaways.',
        119.99,
        107.99,
        50,
        3,
        'assets/images/placeholders/bags/bag9_main.webp',
        '["assets/images/placeholders/bags/bag9_additional1.webp","assets/images/placeholders/bags/bag9_additional2.webp"]',
        '{"color": "Beige", "capacity": "30L"}',
        '2023-09-25 14:30:00'
    ),
    (
        'Camera Bag',
        'Professional camera bag with dedicated compartments for equipment.',
        124.99,
        NULL,
        55,
        3,
        'assets/images/placeholders/bags/bag10_main.webp',
        '["assets/images/placeholders/bags/bag10_additional1.webp","assets/images/placeholders/bags/bag10_additional2.webp"]',
        '{"color": "Black", "compatibility": "DSLR cameras"}',
        '2023-10-10 16:00:00'
    );

-- Placeholder Products for Books
INSERT INTO
    products (
        name,
        description,
        price,
        discount_price,
        stock,
        category_id,
        image_url,
        additional_images,
        specifications,
        created_at
    )
VALUES
    (
        'Tập vở trẻ em ',
        'Tập vở mà trẻ em yêu thích.',
        14.99,
        12.99,
        10,
        2,
        'assets/images/placeholders/books/book1_main.webp',
        '["assets/images/placeholders/books/book1_additional1.webp","assets/images/placeholders/books/book1_additional2.webp"]',
        '{"pages": 180, "binding": "Paperback"}',
        '2024-11-10 09:00:00'
    ),
    (
        'Gift book',
        'Nữ thích bê đê càng thích.',
        19.99,
        NULL,
        15,
        2,
        'assets/images/placeholders/books/book2_main.webp',
        '["assets/images/placeholders/books/book2_additional1.webp","assets/images/placeholders/books/book2_additional2.webp"]',
        '{"pages": 281, "binding": "Hardcover"}',
        '2024-11-15 11:30:00'
    ),
    (
        'Tập viết',
        'Tập viết danh cho tính toán.',
        24.99,
        22.99,
        20,
        2,
        'assets/images/placeholders/books/book3_main.webp',
        '["assets/images/placeholders/books/book3_additional1.webp"]',
        '{"pages": 328, "binding": "Paperback"}',
        '2023-03-22 14:00:00'
    ),
    (
        'Feel your best',
        'this book can you feel b.',
        29.99,
        NULL,
        25,
        2,
        'assets/images/placeholders/books/book4_main.webp',
        '["assets/images/placeholders/books/book4_additional1.webp","assets/images/placeholders/books/book4_additional2.webp"]',
        '{"pages": 432, "binding": "Hardcover"}',
        '2024-11-10 16:15:00'
    ),
    (
        'Blank book',
        'book for learn.',
        34.99, 
        31.99,
        30,
        2,
        'assets/images/placeholders/books/book5_main.webp',
        '["assets/images/placeholders/books/book5_additional1.webp","assets/images/placeholders/books/book5_additional2.webp"]',
        '{"pages": 309, "binding": "Paperback"}',
        '2023-05-05 10:30:00'
    ),
    (
        'Tập kế toán',
        'Hỗ trợ tính toán.',
        39.99,
        NULL,
        35,
        2,
        'assets/images/placeholders/books/book6_main.webp',
        '["assets/images/placeholders/books/book6_additional1.webp","assets/images/placeholders/books/book6_additional2.webp"]',
        '{"pages": 277, "binding": "Hardcover"}',
        '2023-06-15 13:45:00'
    ),
    (
        'Brave New World',
        'A dystopian novel by Aldous Huxley.',
        44.99,
        40.99,
        40,
        2,
        'assets/images/placeholders/books/book7_main.webp',
        '["assets/images/placeholders/books/book7_additional1.webp","assets/images/placeholders/books/book7_additional2.webp"]',
        '{"pages": 288, "binding": "Paperback"}',
        '2023-07-20 15:00:00'
    );

-- Placeholder Products for Pens
INSERT INTO
    products (
        name,
        description,
        price,
        discount_price,
        stock,
        category_id,
        image_url,
        additional_images,
        specifications,
        created_at
    )
VALUES
    (
        'Ballpoint Pen',
        'Durable ballpoint pen with smooth ink flow.',
        9.99,
        NULL,
        10,
        1,
        'assets/images/placeholders/pens/pen1_main.webp',
        '["assets/images/placeholders/pens/pen1_additional1.webp"]',
        '{"color": "Blue", "ink_type": "Ballpoint"}',
        '2023-01-05 10:00:00'
    ),
    (
        'Gel Pen',
        'Gel pen with vibrant ink colors.',
        14.99,
        13.99,
        15,
        1,
        'assets/images/placeholders/pens/pen2_main.webp',
        '["assets/images/placeholders/pens/pen2_additional1.webp","assets/images/placeholders/pens/pen2_additional2.webp"]',
        '{"color": "Red", "ink_type": "Gel"}',
        '2023-02-10 12:30:00'
    ),
    (
        'Fountain Pen',
        'Elegant fountain pen for calligraphy.',
        19.99,
        NULL,
        20,
        1,
        'assets/images/placeholders/pens/pen3_main.webp',
        '["assets/images/placeholders/pens/pen3_additional1.webp","assets/images/placeholders/pens/pen3_additional2.webp","assets/images/placeholders/pens/pen3_additional3.webp","assets/images/placeholders/pens/pen3_additional4.webp"]',
        '{"color": "Black", "ink_type": "Fountain"}',
        '2024-11-15 15:00:00'
    ),
    (
        'Highlighter Pen',
        'Bright highlighter pen for marking text.',
        24.99,
        22.99,
        25,
        1,
        'assets/images/placeholders/pens/pen4_main.webp',
        '["assets/images/placeholders/pens/pen4_additional1.webp","assets/images/placeholders/pens/pen4_additional2.webp","assets/images/placeholders/pens/pen4_additional3.webp","assets/images/placeholders/pens/pen4_additional4.webp"]',
        '{"color": "Yellow", "ink_type": "Highlighter"}',
        '2024-11-20 17:30:00'
    ),
    (
        'Marker Pen',
        'Permanent marker pen for various surfaces.',
        29.99,
        NULL,
        30,
        1,
        'assets/images/placeholders/pens/pen5_main.webp',
        '["assets/images/placeholders/pens/pen5_additional1.webp","assets/images/placeholders/pens/pen5_additional2.webp","assets/images/placeholders/pens/pen5_additional3.webp","assets/images/placeholders/pens/pen5_additional4.webp"]',
        '{"color": "Black", "ink_type": "Permanent"}',
        '2024-11-25 14:45:00'
    ),
    (
        'Pencil',
        'Erasable graphite pencil for sketching.',
        34.99,
        31.99,
        35,
        1,
        'assets/images/placeholders/pens/pen6_main.webp',
        '["assets/images/placeholders/pens/pen6_additional1.webp"]',
        '{"lead_type": "Graphite", "hardness": "HB"}',
        '2023-06-10 16:00:00'
    ),
    (
        'Ink Pen',
        'Fine tip ink pen for detailed writing.',
        39.99,
        NULL,
        40,
        1,
        'assets/images/placeholders/pens/pen7_main.webp',
        '["assets/images/placeholders/pens/pen7_additional1.webp","assets/images/placeholders/pens/pen7_additional2.webp"]',
        '{"color": "Blue", "ink_type": "Ink"}',
        '2024-11-15 18:15:00'
    ),
    (
        'Calligraphy Pen',
        'Specialized pen for calligraphy and handwriting.',
        44.99,
        40.99,
        45,
        1,
        'assets/images/placeholders/pens/pen8_main.webp',
        '["assets/images/placeholders/pens/pen8_additional1.webp","assets/images/placeholders/pens/pen8_additional2.webp"]',
        '{"color": "Black", "ink_type": "Calligraphy"}',
        '2023-08-20 19:30:00'
    ),
    (
        'Ergonomic Pen',
        'Comfortable ergonomic pen for prolonged use.',
        49.99,
        NULL,
        50,
        1,
        'assets/images/placeholders/pens/pen9_main.webp',
        '["assets/images/placeholders/pens/pen9_additional1.webp","assets/images/placeholders/pens/pen9_additional2.webp","assets/images/placeholders/pens/pen9_additional3.webp","assets/images/placeholders/pens/pen9_additional4.webp"]',
        '{"color": "Gray", "ink_type": "Ballpoint"}',
        '2023-09-25 20:45:00'
    ),
    (
        'Pilot Pen',
        'High-quality pen with smooth writing experience.',
        54.99,
        50.99,
        55,
        1,
        'assets/images/placeholders/pens/pen10_main.webp',
        '["assets/images/placeholders/pens/pen10_additional1.webp","assets/images/placeholders/pens/pen10_additional2.webp"]',
        '{"color": "Navy", "ink_type": "Gel"}',
        '2023-10-10 21:00:00'
    );

-- Create carts table next since it's referenced by cart_items
CREATE TABLE IF NOT EXISTS carts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    status ENUM ('active', 'completed', 'abandoned') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create cart_items table
CREATE TABLE IF NOT EXISTS cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cart_id (cart_id),
    INDEX idx_product_id (product_id),
    CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    CONSTRAINT uq_cart_product UNIQUE (cart_id, product_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create orders table next since it's referenced by order_items
CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    status ENUM (
        'pending',
        'processing',
        'shipped',
        'delivered',
        'cancelled'
    ) NOT NULL DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    billing_address TEXT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create newsletter_subscribers table
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    token VARCHAR(64) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create wishlist_items table
CREATE TABLE IF NOT EXISTS wishlist_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_product_id (product_id),
    CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    CONSTRAINT uq_wishlist_user_product UNIQUE (user_id, product_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create login_attempts table
CREATE TABLE IF NOT EXISTS login_attempts (
    ip VARCHAR(45) PRIMARY KEY,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_last_attempt (last_attempt)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
