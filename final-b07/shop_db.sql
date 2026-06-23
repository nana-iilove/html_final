-- 1. 如果舊的資料庫存在就先刪除（方便你重置測試），並建立全新的 shop_db
DROP DATABASE IF EXISTS `shop_db`;
CREATE DATABASE `shop_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shop_db;

-- =========================================================================
-- 2. 建立會員表 (users)
-- =========================================================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user', -- 身分區分：'user' 或 'admin'
  `is_active` tinyint(1) NOT NULL DEFAULT 1,  -- 1 代表直接開通啟用
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================================
-- 3. 建立商品表 (products)
-- =========================================================================
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,                -- 商品或服務名稱
  `price` int(11) NOT NULL,                   -- 價格
  `image_path` varchar(255) NOT NULL,          -- 圖片的檔案名稱
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================================
-- 4. 建立訂單主檔 (orders)
-- =========================================================================
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,                 -- 顧客ID (若未登入結帳則為999)
  `total_price` int(11) NOT NULL,             -- 訂單總金額
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, -- 下單時間
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================================
-- 5. 建立訂單明細表 (order_items) -> 給 Chart.js 統計圖表撈取關鍵數據
-- =========================================================================
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,                -- 對應的訂單編號
  `product_id` int(11) NOT NULL,              -- 對應的商品編號
  `quantity` int(11) NOT NULL,                -- 購買數量
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================================
-- 6. 預先插入一組管理員專用帳號 (Demo 登入後台用)
-- =========================================================================
-- 帳號：admin@test.com
-- 密碼：1234
INSERT INTO `users` (`email`, `password`, `role`, `is_active`) VALUES
('admin@test.com', '1234', 'admin', 1);