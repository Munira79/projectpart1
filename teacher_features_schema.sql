-- Additional tables for teacher features
-- Run this after the main schema

USE `fb-sql`;

-- =============================================
-- 1. ATTENDANCE TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    student_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    class_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'present',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (teacher_id, student_id, subject, class_date)
);

-- =============================================
-- 2. LECTURES TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS lectures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    subject VARCHAR(100) NOT NULL,
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
-- 3. ASSIGNMENTS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    subject VARCHAR(100) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(10) NOT NULL,
    file_size INT,
    due_date DATE,
    max_marks INT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT NOT NULL,
    is_public BOOLEAN DEFAULT TRUE,
    download_count INT DEFAULT 0,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- 4. TEACHER WORK TRACKER TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS teacher_work_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    activity_type ENUM('Lecture', 'Assignment Review', 'Exam Preparation', 'Research', 'Grading', 'Meeting') DEFAULT 'Lecture',
    notes TEXT,
    duration INT NOT NULL,
    session_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- INDEXES FOR BETTER PERFORMANCE
-- =============================================

-- Attendance table indexes
CREATE INDEX idx_attendance_teacher_id ON attendance(teacher_id);
CREATE INDEX idx_attendance_student_id ON attendance(student_id);
CREATE INDEX idx_attendance_subject ON attendance(subject);
CREATE INDEX idx_attendance_class_date ON attendance(class_date);

-- Lectures table indexes
CREATE INDEX idx_lectures_subject ON lectures(subject);
CREATE INDEX idx_lectures_upload_date ON lectures(upload_date);
CREATE INDEX idx_lectures_uploaded_by ON lectures(uploaded_by);
CREATE INDEX idx_lectures_is_public ON lectures(is_public);

-- Assignments table indexes
CREATE INDEX idx_assignments_subject ON assignments(subject);
CREATE INDEX idx_assignments_upload_date ON assignments(upload_date);
CREATE INDEX idx_assignments_uploaded_by ON assignments(uploaded_by);
CREATE INDEX idx_assignments_is_public ON assignments(is_public);
CREATE INDEX idx_assignments_due_date ON assignments(due_date);

-- Teacher work sessions table indexes
CREATE INDEX idx_teacher_work_teacher_id ON teacher_work_sessions(teacher_id);
CREATE INDEX idx_teacher_work_date ON teacher_work_sessions(session_date);
CREATE INDEX idx_teacher_work_subject ON teacher_work_sessions(subject);
