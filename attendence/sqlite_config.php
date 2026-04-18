<?php
/**
 * SQLite Configuration for Attendance System
 */

define('DB_PATH', __DIR__ . '/../database/attendance.db');

// Function to get PDO connection
function getSQLiteConnection() {
    try {
        // Ensure database directory exists
        if (!is_dir(__DIR__ . '/../database')) {
            mkdir(__DIR__ . '/../database', 0755, true);
        }

        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    } catch (PDOException $e) {
        die('SQLite Connection Error: ' . $e->getMessage());
    }
}

// Initialize database if not exists
function initializeAttendanceDB() {
    $pdo = getSQLiteConnection();
    
    try {
        // Create attendance table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS attendance (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id INTEGER NOT NULL,
                student_name TEXT NOT NULL,
                enrollment_id TEXT NOT NULL,
                course TEXT,
                batch TEXT,
                attendance_date DATE NOT NULL,
                status TEXT NOT NULL CHECK(status IN ('Present', 'Absent', 'Leave')),
                remarks TEXT,
                marked_by INTEGER,
                marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(student_id, attendance_date)
            )
        ");

        // Create attendance summary table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS attendance_summary (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id INTEGER NOT NULL UNIQUE,
                total_days INTEGER DEFAULT 0,
                present_days INTEGER DEFAULT 0,
                absent_days INTEGER DEFAULT 0,
                leave_days INTEGER DEFAULT 0,
                attendance_percentage REAL DEFAULT 0,
                last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Create indexes
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_attendance_date ON attendance(attendance_date)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_attendance_student ON attendance(student_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_attendance_status ON attendance(status)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_summary_student ON attendance_summary(student_id)");

        return true;
    } catch (PDOException $e) {
        error_log('Database initialization error: ' . $e->getMessage());
        return false;
    }
}

// Initialize on include
initializeAttendanceDB();

// Get database connection for use in pages
$db = getSQLiteConnection();
?>
