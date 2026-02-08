-- Steward Complaint Management Portal
-- Database Schema for SQLite (for testing/demo purposes)

-- Drop existing tables (for clean setup)
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS complaint_messages;
DROP TABLE IF EXISTS complaints;
DROP TABLE IF EXISTS users;

-- Users table
CREATE TABLE users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL CHECK(role IN ('admin', 'slo', 'dso', 'steward')) DEFAULT 'steward',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_email ON users(email);

-- Complaints table
CREATE TABLE complaints (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  subject TEXT NOT NULL,
  body TEXT NOT NULL,
  status TEXT NOT NULL CHECK(status IN ('new', 'investigating', 'resolved', 'deadlock')) DEFAULT 'new',
  category TEXT NOT NULL DEFAULT 'general',
  toxicity_score REAL DEFAULT NULL,
  deadlock_deadline DATETIME NOT NULL,
  stadium_block TEXT NOT NULL CHECK(stadium_block IN ('north', 'south', 'east', 'west', 'unknown')) DEFAULT 'unknown',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
);

CREATE INDEX idx_complaints_status ON complaints(status);
CREATE INDEX idx_complaints_category ON complaints(category);
CREATE INDEX idx_complaints_deadline ON complaints(deadlock_deadline);
CREATE INDEX idx_complaints_stadium_block ON complaints(stadium_block);
CREATE INDEX idx_complaints_user_id ON complaints(user_id);
CREATE INDEX idx_complaints_created_at ON complaints(created_at);

-- Complaint messages table
CREATE TABLE complaint_messages (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  complaint_id INTEGER NOT NULL,
  sender_type TEXT NOT NULL CHECK(sender_type IN ('supporter', 'staff', 'system')),
  body TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE
);

CREATE INDEX idx_complaint_messages_complaint_id ON complaint_messages(complaint_id);
CREATE INDEX idx_complaint_messages_created_at ON complaint_messages(created_at);

-- Audit logs table (append-only, immutable via triggers)
CREATE TABLE audit_logs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  complaint_id INTEGER NOT NULL,
  user_id INTEGER NULL,
  action TEXT NOT NULL,
  previous_state TEXT NULL,
  new_state TEXT NULL,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE RESTRICT,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_audit_logs_complaint_id ON audit_logs(complaint_id);
CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_timestamp ON audit_logs(timestamp);

-- Triggers to make audit_logs immutable (append-only)
CREATE TRIGGER prevent_audit_update
BEFORE UPDATE ON audit_logs
BEGIN
  SELECT RAISE(ABORT, 'audit_logs is append-only');
END;

CREATE TRIGGER prevent_audit_delete
BEFORE DELETE ON audit_logs
BEGIN
  SELECT RAISE(ABORT, 'audit_logs is append-only');
END;

-- Seed data: Default users with bcrypt hash for "steward2026"
INSERT INTO users (name, email, password_hash, role) VALUES
('Admin User', 'admin@steward.local', '$2y$10$zPyGSt5CycaArZLU4cOpy.e2XUjfINgDPNZoJFtD8/.BiDS0QgeBW', 'admin'),
('SLO Officer', 'slo@steward.local', '$2y$10$zPyGSt5CycaArZLU4cOpy.e2XUjfINgDPNZoJFtD8/.BiDS0QgeBW', 'slo'),
('DSO Officer', 'dso@steward.local', '$2y$10$zPyGSt5CycaArZLU4cOpy.e2XUjfINgDPNZoJFtD8/.BiDS0QgeBW', 'dso'),
('Steward User', 'steward@steward.local', '$2y$10$zPyGSt5CycaArZLU4cOpy.e2XUjfINgDPNZoJFtD8/.BiDS0QgeBW', 'steward');
