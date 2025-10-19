-- Focus Bridge Database - Indexes and Sample Data
-- Database: fb-sql
-- Run this after the main schema is created

USE `fb-sql`;

-- =============================================
-- CREATE INDEXES (Safe Version)
-- =============================================

-- Users table indexes
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_student_id ON users(student_id);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);

-- Exams table indexes
CREATE INDEX IF NOT EXISTS idx_exams_date ON exams(exam_date);
CREATE INDEX IF NOT EXISTS idx_exams_subject ON exams(subject);
CREATE INDEX IF NOT EXISTS idx_exams_status ON exams(status);
CREATE INDEX IF NOT EXISTS idx_exams_created_by ON exams(created_by);

-- Notes table indexes
CREATE INDEX IF NOT EXISTS idx_notes_subject ON notes(subject);
CREATE INDEX IF NOT EXISTS idx_notes_upload_date ON notes(upload_date);
CREATE INDEX IF NOT EXISTS idx_notes_uploaded_by ON notes(uploaded_by);
CREATE INDEX IF NOT EXISTS idx_notes_is_public ON notes(is_public);

-- Sessions table indexes
CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_sessions_date ON sessions(session_date);
CREATE INDEX IF NOT EXISTS idx_sessions_subject ON sessions(subject);

-- Quotes table indexes
CREATE INDEX IF NOT EXISTS idx_quotes_category ON quotes(category);
CREATE INDEX IF NOT EXISTS idx_quotes_is_featured ON quotes(is_featured);
CREATE INDEX IF NOT EXISTS idx_quotes_created_by ON quotes(created_by);

-- Notices table indexes
CREATE INDEX IF NOT EXISTS idx_notices_priority ON notices(priority);
CREATE INDEX IF NOT EXISTS idx_notices_is_active ON notices(is_active);
CREATE INDEX IF NOT EXISTS idx_notices_created_by ON notices(created_by);

-- Additional useful indexes
CREATE INDEX IF NOT EXISTS idx_user_favorites_user_id ON user_favorites(user_id);
CREATE INDEX IF NOT EXISTS idx_user_favorites_quote_id ON user_favorites(quote_id);
CREATE INDEX IF NOT EXISTS idx_study_goals_user_id ON study_goals(user_id);
CREATE INDEX IF NOT EXISTS idx_study_goals_status ON study_goals(status);
CREATE INDEX IF NOT EXISTS idx_timer_sessions_user_id ON timer_sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_timer_sessions_status ON timer_sessions(status);
CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications(user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_is_read ON notifications(is_read);
CREATE INDEX IF NOT EXISTS idx_user_activity_log_user_id ON user_activity_log(user_id);
CREATE INDEX IF NOT EXISTS idx_user_activity_log_created_at ON user_activity_log(created_at);

-- =============================================
-- SAMPLE DATA INSERTION
-- =============================================

-- Insert sample motivational quotes (only if no quotes exist)
INSERT IGNORE INTO quotes (id, quote_text, author, category, created_by) VALUES 
(1, 'The future belongs to those who believe in the beauty of their dreams.', 'Eleanor Roosevelt', 'motivation', 1),
(2, 'Success is not final, failure is not fatal: it is the courage to continue that counts.', 'Winston Churchill', 'perseverance', 1),
(3, 'Education is the most powerful weapon which you can use to change the world.', 'Nelson Mandela', 'education', 1),
(4, 'The only way to do great work is to love what you do.', 'Steve Jobs', 'passion', 1),
(5, 'Believe you can and you''re halfway there.', 'Theodore Roosevelt', 'motivation', 1),
(6, 'The expert in anything was once a beginner.', 'Helen Hayes', 'learning', 1),
(7, 'Don''t watch the clock; do what it does. Keep going.', 'Sam Levenson', 'perseverance', 1),
(8, 'Success is walking from failure to failure with no loss of enthusiasm.', 'Winston Churchill', 'perseverance', 1);

-- =============================================
-- VERIFY INDEXES AND DATA
-- =============================================

-- Show all indexes
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    NON_UNIQUE
FROM information_schema.statistics 
WHERE table_schema = 'fb-sql'
ORDER BY TABLE_NAME, INDEX_NAME;

-- Show sample data
SELECT COUNT(*) as total_quotes FROM quotes;
SELECT * FROM quotes LIMIT 5;

-- =============================================
-- END OF INDEXES AND DATA
-- =============================================

