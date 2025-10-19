-- Focus Bridge Database Schema
-- Database: fb-sql
-- Complete SQL for the Focus Bridge project

-- Create database
CREATE DATABASE IF NOT EXISTS `fb-sql`;
USE `fb-sql`;

-- =============================================
-- 1. USERS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    student_id VARCHAR(20) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin', 'teacher') DEFAULT 'student',
    profile_image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- 2. EXAMS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(100) NOT NULL,
    exam_type ENUM('exam', 'assignment', 'tutorial', 'quiz') DEFAULT 'exam',
    title VARCHAR(200) NOT NULL,
    description TEXT,
    exam_date DATE NOT NULL,
    exam_time TIME NOT NULL,
    duration INT DEFAULT 60,
    location VARCHAR(200),
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- 3. NOTES TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    subject VARCHAR(100) NOT NULL,
    tags TEXT,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(10) NOT NULL,
    file_size INT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT NOT NULL,
    is_public BOOLEAN DEFAULT TRUE,
    download_count INT DEFAULT 0,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- 4. MOTIVATIONAL QUOTES TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS quotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quote_text TEXT NOT NULL,
    author VARCHAR(100),
    category VARCHAR(50) DEFAULT 'general',
    is_featured BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- 5. NOTICES TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    notice_type ENUM('text', 'image', 'urgent') DEFAULT 'text',
    image_path VARCHAR(500),
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- 6. WORK TRACKER SESSIONS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    activity_type ENUM('Study', 'Assignment', 'Homework', 'Book Reading', 'Research', 'Project') DEFAULT 'Study',
    notes TEXT,
    duration INT NOT NULL,
    session_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- 7. USER FAVORITES TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS user_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quote_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_quote (user_id, quote_id)
);

-- =============================================
-- 8. STUDY GOALS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS study_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    goal_title VARCHAR(200) NOT NULL,
    goal_description TEXT,
    target_hours INT DEFAULT 0,
    current_hours INT DEFAULT 0,
    goal_type ENUM('daily', 'weekly', 'monthly', 'semester') DEFAULT 'weekly',
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('active', 'completed', 'paused', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- 9. USER PREFERENCES TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    theme ENUM('light', 'dark') DEFAULT 'light',
    notifications_enabled BOOLEAN DEFAULT TRUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    study_reminders BOOLEAN DEFAULT TRUE,
    exam_reminders BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- 10. STUDY TIMER SESSIONS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS timer_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_name VARCHAR(200),
    duration_planned INT NOT NULL,
    duration_actual INT,
    subject VARCHAR(100),
    notes TEXT,
    session_type ENUM('pomodoro', 'study', 'break', 'focus') DEFAULT 'study',
    status ENUM('active', 'completed', 'paused', 'cancelled') DEFAULT 'active',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- 11. NOTIFICATIONS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('exam_reminder', 'goal_reminder', 'general', 'urgent') DEFAULT 'general',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- 12. USER ACTIVITY LOG TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS user_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    activity_description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- INDEXES FOR BETTER PERFORMANCE
-- =============================================

-- Users table indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_student_id ON users(student_id);
CREATE INDEX idx_users_role ON users(role);

-- Exams table indexes
CREATE INDEX idx_exams_date ON exams(exam_date);
CREATE INDEX idx_exams_subject ON exams(subject);
CREATE INDEX idx_exams_status ON exams(status);
CREATE INDEX idx_exams_created_by ON exams(created_by);

-- Notes table indexes
CREATE INDEX idx_notes_subject ON notes(subject);
CREATE INDEX idx_notes_upload_date ON notes(upload_date);
CREATE INDEX idx_notes_uploaded_by ON notes(uploaded_by);
CREATE INDEX idx_notes_is_public ON notes(is_public);

-- Sessions table indexes
CREATE INDEX idx_sessions_user_id ON sessions(user_id);
CREATE INDEX idx_sessions_date ON sessions(session_date);
CREATE INDEX idx_sessions_subject ON sessions(subject);

-- Quotes table indexes
CREATE INDEX idx_quotes_category ON quotes(category);
CREATE INDEX idx_quotes_is_featured ON quotes(is_featured);
CREATE INDEX idx_quotes_created_by ON quotes(created_by);

-- Notices table indexes
CREATE INDEX idx_notices_priority ON notices(priority);
CREATE INDEX idx_notices_is_active ON notices(is_active);
CREATE INDEX idx_notices_created_by ON notices(created_by);

-- =============================================
-- SAMPLE DATA INSERTION
-- =============================================

-- Insert sample motivational quotes
INSERT INTO quotes (quote_text, author, category, created_by) VALUES 
('The future belongs to those who believe in the beauty of their dreams.', 'Eleanor Roosevelt', 'motivation', 1),
('Success is not final, failure is not fatal: it is the courage to continue that counts.', 'Winston Churchill', 'perseverance', 1),
('Education is the most powerful weapon which you can use to change the world.', 'Nelson Mandela', 'education', 1),
('The only way to do great work is to love what you do.', 'Steve Jobs', 'passion', 1),
('Believe you can and you\'re halfway there.', 'Theodore Roosevelt', 'motivation', 1),
('The expert in anything was once a beginner.', 'Helen Hayes', 'learning', 1),
('Don\'t watch the clock; do what it does. Keep going.', 'Sam Levenson', 'perseverance', 1),
('Success is walking from failure to failure with no loss of enthusiasm.', 'Winston Churchill', 'perseverance', 1);

-- =============================================
-- END OF SCHEMA
-- =============================================

