-- =========================================================
-- Update 1: mark products as Men, Women or Unisex
-- Run once in phpMyAdmin (reye_db -> SQL tab).
-- =========================================================
ALTER TABLE products
  ADD COLUMN gender ENUM('men','women','unisex') NOT NULL DEFAULT 'men' AFTER category_id;

-- Pouches are for everyone
UPDATE products SET gender = 'unisex' WHERE category_id = (SELECT id FROM (SELECT id FROM categories WHERE slug = 'pouches') AS c);
