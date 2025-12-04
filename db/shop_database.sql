DROP DATABASE IF EXISTS online_shop;
CREATE DATABASE online_shop;
USE online_shop;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_name VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Додаємо тестових користувачів
INSERT INTO users (username, email, password) VALUES 
('admin', 'admin@shop.com', 'admin123'),
('john', 'john@shop.com', 'john123'),
('mary', 'mary@shop.com', 'mary123'),
('seller1', 'seller1@shop.com', 'seller123'),
('seller2', 'seller2@shop.com', 'seller456');

-- Додаємо тестові товари (без зображень, вони будуть додані окремо в product_images)
INSERT INTO products (user_id, name, description, price, category) VALUES
(1, 'iPhone 15 Pro', 'Новий смартфон від Apple з потрійною камерою', 1299.99, 'Електроніка'),
(1, 'Ноутбук Dell XPS 13', 'Ультрабук з сенсорним екраном, 16GB RAM', 1999.99, 'Електроніка'),
(2, 'Книга "Веб-програмування"', 'Повний посібник з PHP, MySQL та JavaScript', 29.99, 'Книги'),
(2, 'Футболка чорна', 'Бавовняна футболка, розмір M, якісний матеріал', 19.99, 'Одяг'),
(3, 'Навушники Sony WH-1000XM4', 'Бездротові навушники з активним шумозаглушенням', 349.99, 'Електроніка'),
(3, 'Кава Melitta Aroma', 'Натуральна кава в зернах, 500г, середня обсмажка', 15.99, 'Продукти');

-- Додаємо тестові зображення для товарів (припустимо, що у нас є файли з такими іменами)
INSERT INTO product_images (product_id, image_name, is_main) VALUES
(1, 'iphone_front.webp', TRUE),
(1, 'iphone_back.webp', FALSE),
(1, 'iphone_side.webp', FALSE),
(2, 'laptop_open.webp', TRUE),
(2, 'laptop_closed.webp', FALSE),
(3, 'book_cover.webp', TRUE),
(3, 'book_content.webp', FALSE),
(4, 'tshirt_front.webp', TRUE),
(4, 'tshirt_back.webp', FALSE),
(5, 'headphones_box.webp', TRUE),
(5, 'headphones_wearing.webp', FALSE),
(6, 'coffee_pack.webp', TRUE),
(6, 'coffee_beans.webp', FALSE);

