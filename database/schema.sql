-- Puihaha Electric: schema-only setup for a fresh local installation.
-- No user accounts, passwords, customer records, or database grants included.
-- The two databases stay separate. Existing tables and data are not replaced.

CREATE DATABASE IF NOT EXISTS `electriccompany`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `electriccompany`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('customer','admin') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE DATABASE IF NOT EXISTS `electric_company`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `electric_company`;

CREATE TABLE IF NOT EXISTS `customer_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_number` varchar(50) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `meter_number` varchar(50) DEFAULT NULL,
  `connection_type` enum('residential','commercial','industrial') DEFAULT 'residential',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_number` (`account_number`),
  KEY `idx_account_number` (`account_number`),
  KEY `idx_status` (`status`),
  KEY `idx_connection_type` (`connection_type`),
  KEY `idx_customer_name` (`customer_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
