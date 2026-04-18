<?php
/**
 * SQLite Attendance Database Initialization
 * Creates tables for attendance tracking
 */

define('DB_PATH', __DIR__ . '/../database/attendance.db');

// Ensure database directory exists
if (!is_dir(__DIR__ . '/../database')) {
    mkdir(__DIR__ . '/../database', 0755, true);
}

try {
    // Create/Connect to SQLite database
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

    // Create indexes for better performance
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_attendance_date ON attendance(attendance_date)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_attendance_student ON attendance(student_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_attendance_status ON attendance(status)");

    echo json_encode([
        'success' => true,
        'message' => 'SQLite database initialized successfully'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
