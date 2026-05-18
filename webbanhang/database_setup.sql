-- Create database
CREATE DATABASE IF NOT EXISTS my_store;
USE my_store;

-- Create category table
CREATE TABLE IF NOT EXISTS category (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create product table
CREATE TABLE IF NOT EXISTS product (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  category_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE SET NULL
);

-- Insert sample categories
INSERT INTO category (name, description) VALUES
('Electronics', 'Các sản phẩm điện tử'),
('Clothing', 'Quần áo và thời trang'),
('Books', 'Sách và tài liệu'),
('Food', 'Thực phẩm và đồ ăn');

-- Insert sample products
INSERT INTO product (name, description, price, category_id) VALUES
('Laptop Dell XPS 13', 'Laptop cao cấp với bộ xử lý Intel Core i7', 899.99, 1),
('Áo thun trắng', 'Áo thun 100% cotton màu trắng', 15.99, 2),
('PHP Programming', 'Hướng dẫn lập trình PHP cho người mới bắt đầu', 29.99, 3),
('Cà phê hạt Arabica', 'Cà phê nguyên chất nhập khẩu từ Brazil', 12.99, 4);
