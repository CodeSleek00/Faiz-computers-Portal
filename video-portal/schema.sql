CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size BIGINT NOT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS video_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    student_id INT NOT NULL,
    student_table VARCHAR(20) NOT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_video_student (video_id, student_id, student_table),
    INDEX idx_student (student_id, student_table),
    CONSTRAINT fk_video_assignments_video
        FOREIGN KEY (video_id)
        REFERENCES videos (id)
        ON DELETE CASCADE
);
