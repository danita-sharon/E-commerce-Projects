-- =========================================================
-- REYE - Handmade footwear and pouches
-- Database: reye_db  (MySQL / MariaDB)
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS payments, order_items, orders, wishlist, product_colours,
  products, categories, delivery_regions, promo_codes, customer_requests,
  reviews, newsletter_subscribers, users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------- Users (customers and admins) ----------
CREATE TABLE users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  phone         VARCHAR(20),
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Catalogue ----------
CREATE TABLE categories (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  slug VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE products (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name        VARCHAR(100) NOT NULL,
  slug        VARCHAR(120) NOT NULL UNIQUE,
  description TEXT,
  price       DECIMAL(10,2) NOT NULL,
  sizes       VARCHAR(100) NOT NULL DEFAULT 'One size',
  stock       INT NOT NULL DEFAULT 0,
  badge       VARCHAR(30) NULL,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE product_colours (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  product_id  INT NOT NULL,
  colour_name VARCHAR(50) NOT NULL,
  hex_main    CHAR(7) NOT NULL,
  hex_accent  CHAR(7) NOT NULL,
  image       VARCHAR(255) NULL,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE wishlist (
  user_id    INT NOT NULL,
  product_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, product_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Delivery and promotions ----------
CREATE TABLE delivery_regions (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(50) NOT NULL,
  fee       DECIMAL(10,2) NOT NULL,
  free_over DECIMAL(10,2) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE promo_codes (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  code        VARCHAR(30) NOT NULL UNIQUE,
  percent_off TINYINT NOT NULL,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  expires_at  DATE NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Orders and payments ----------
CREATE TABLE orders (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  order_no       VARCHAR(20) NOT NULL UNIQUE,
  user_id        INT NOT NULL,
  full_name      VARCHAR(100) NOT NULL,
  email          VARCHAR(150) NOT NULL,
  phone          VARCHAR(20) NOT NULL,
  address        VARCHAR(255) NOT NULL,
  region_id      INT NOT NULL,
  subtotal       DECIMAL(10,2) NOT NULL,
  delivery_fee   DECIMAL(10,2) NOT NULL,
  discount       DECIMAL(10,2) NOT NULL DEFAULT 0,
  total          DECIMAL(10,2) NOT NULL,
  promo_code     VARCHAR(30) NULL,
  status         ENUM('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  payment_status ENUM('unpaid','paid','failed') NOT NULL DEFAULT 'unpaid',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  paid_at        DATETIME NULL,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (region_id) REFERENCES delivery_regions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  order_id     INT NOT NULL,
  product_id   INT NULL,
  product_name VARCHAR(100) NOT NULL,
  colour       VARCHAR(50) NOT NULL,
  size         VARCHAR(20) NOT NULL,
  unit_price   DECIMAL(10,2) NOT NULL,
  quantity     INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  order_id         INT NOT NULL,
  reference        VARCHAR(100) NOT NULL UNIQUE,
  amount           DECIMAL(10,2) NOT NULL,
  currency         CHAR(3) NOT NULL DEFAULT 'GHS',
  channel          VARCHAR(30) NULL,
  status           VARCHAR(20) NOT NULL,
  gateway_response VARCHAR(255) NULL,
  paid_at          DATETIME NULL,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Reye AI requests, reviews, newsletter ----------
CREATE TABLE customer_requests (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NULL,
  name       VARCHAR(100) NOT NULL,
  contact    VARCHAR(150) NOT NULL,
  message    TEXT NOT NULL,
  type       ENUM('request','bespoke','question') NOT NULL DEFAULT 'request',
  status     ENUM('new','seen','done') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reviews (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(100) NOT NULL,
  location      VARCHAR(100) NULL,
  review_text   TEXT NOT NULL,
  rating        TINYINT NOT NULL DEFAULT 5,
  is_approved   TINYINT(1) NOT NULL DEFAULT 0,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE newsletter_subscribers (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  email      VARCHAR(150) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- Starter data (placeholders - edit from the admin dashboard)
-- =========================================================

-- Admin login: admin@reye.com / Reye@Admin2026  (change after first login)
INSERT INTO users (full_name, email, phone, password_hash, role) VALUES
('Reye Admin', 'admin@reye.com', '0536137600',
 '$2y$10$8.aZz6QtRegMWpbCJOJxbuBMWyKvRuF2vIQL8JqgU9V6pfVrngrvW', 'admin');

INSERT INTO categories (name, slug) VALUES
('Slides', 'slides'), ('Pouches', 'pouches');

INSERT INTO products (category_id, name, slug, description, price, sizes, stock, badge) VALUES
(1, 'The Onyx slide', 'onyx-slide', 'Black croc-embossed leather cross straps on a cushioned leather footbed, finished with the gold R.', 450.00, '40,41,42,43,44,45', 12, 'BESTSELLER'),
(1, 'The Crimson slide', 'crimson-slide', 'Rich crimson leather with a pony-hair strap. Made for the moments you want to be noticed.', 480.00, '40,41,42,43,44,45', 3, 'ONLY 3 LEFT'),
(1, 'The Cognac slide', 'cognac-slide', 'Soft cognac suede and smooth leather straps on a suede footbed. Our softest pair.', 450.00, '40,41,42,43,44,45', 10, 'NEW'),
(1, 'The Navy slide', 'navy-slide', 'Navy and slate textured leather, an easy everyday pair that goes with everything.', 450.00, '40,41,42,43,44,45', 8, NULL),
(2, 'The Everyday pouch', 'everyday-pouch', 'A hand-stitched leather pouch for your phone, keys and cards.', 280.00, 'One size', 15, NULL),
(2, 'The Mini pouch', 'mini-pouch', 'A small leather pouch with the gold R, perfect as a gift.', 180.00, 'One size', 20, 'NEW');

INSERT INTO product_colours (product_id, colour_name, hex_main, hex_accent) VALUES
(1, 'Black croc', '#141210', '#26221f'), (1, 'Navy slate', '#1f2a3d', '#7d838c'),
(2, 'Crimson', '#7a2a22', '#922f25'),   (2, 'Black', '#141210', '#26221f'),
(3, 'Cognac', '#6b3418', '#b06a35'),    (3, 'Chocolate', '#3d2a1c', '#6b4a30'),
(4, 'Navy slate', '#1f2a3d', '#7d838c'), (4, 'Black', '#141210', '#26221f'),
(5, 'Black', '#141210', '#26221f'),     (5, 'Tan', '#6b3418', '#b06a35'),
(6, 'Crimson', '#7a2a22', '#922f25'),   (6, 'Navy', '#1f2a3d', '#7d838c');

INSERT INTO delivery_regions (name, fee, free_over) VALUES
('Accra', 30.00, 800.00), ('Kumasi', 40.00, NULL),
('Other regions in Ghana', 50.00, NULL), ('International', 250.00, NULL);

INSERT INTO promo_codes (code, percent_off) VALUES ('REYEFAM10', 10);