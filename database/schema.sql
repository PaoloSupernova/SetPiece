-- Steward Complaint Management Portal
-- Database Schema for MySQL 8.0

-- Drop existing tables (for clean setup)
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `complaint_messages`;
DROP TABLE IF EXISTS `complaints`;
DROP TABLE IF EXISTS `users`;

-- Users table
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'slo', 'dso', 'steward') NOT NULL DEFAULT 'steward',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_role` (`role`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Complaints table
CREATE TABLE `complaints` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `subject` VARCHAR(500) NOT NULL,
  `body` TEXT NOT NULL,
  `status` ENUM('new', 'investigating', 'resolved', 'deadlock') NOT NULL DEFAULT 'new',
  `category` VARCHAR(100) NOT NULL DEFAULT 'general',
  `toxicity_score` DECIMAL(5,4) DEFAULT NULL,
  `deadlock_deadline` DATETIME NOT NULL,
  `stadium_block` ENUM('north', 'south', 'east', 'west', 'unknown') NOT NULL DEFAULT 'unknown',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  INDEX `idx_status` (`status`),
  INDEX `idx_category` (`category`),
  INDEX `idx_deadline` (`deadlock_deadline`),
  INDEX `idx_stadium_block` (`stadium_block`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Complaint messages table
CREATE TABLE `complaint_messages` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `complaint_id` BIGINT UNSIGNED NOT NULL,
  `sender_type` ENUM('supporter', 'staff', 'system') NOT NULL,
  `body` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`complaint_id`) REFERENCES `complaints`(`id`) ON DELETE CASCADE,
  INDEX `idx_complaint_id` (`complaint_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit logs table (append-only, immutable)
CREATE TABLE `audit_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `complaint_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `action` VARCHAR(255) NOT NULL,
  `previous_state` VARCHAR(255) NULL,
  `new_state` VARCHAR(255) NULL,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`complaint_id`) REFERENCES `complaints`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_complaint_id` (`complaint_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create triggers to make audit_logs immutable (append-only)
DELIMITER $$

CREATE TRIGGER prevent_audit_update
BEFORE UPDATE ON `audit_logs`
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000'
  SET MESSAGE_TEXT = 'audit_logs is append-only';
END$$

CREATE TRIGGER prevent_audit_delete
BEFORE DELETE ON `audit_logs`
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000'
  SET MESSAGE_TEXT = 'audit_logs is append-only';
END$$

DELIMITER ;

-- Seed data: Default users with bcrypt hash for "steward2026"
-- Hash: $2y$10$zPyGSt5CycaArZLU4cOpy.e2XUjfINgDPNZoJFtD8/.BiDS0QgeBW

INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) VALUES
('Admin User', 'admin@steward.local', '$2y$10$zPyGSt5CycaArZLU4cOpy.e2XUjfINgDPNZoJFtD8/.BiDS0QgeBW', 'admin'),
('SLO Officer', 'slo@steward.local', '$2y$10$zPyGSt5CycaArZLU4cOpy.e2XUjfINgDPNZoJFtD8/.BiDS0QgeBW', 'slo'),
('DSO Officer', 'dso@steward.local', '$2y$10$zPyGSt5CycaArZLU4cOpy.e2XUjfINgDPNZoJFtD8/.BiDS0QgeBW', 'dso'),
('Steward User', 'steward@steward.local', '$2y$10$zPyGSt5CycaArZLU4cOpy.e2XUjfINgDPNZoJFtD8/.BiDS0QgeBW', 'steward');
