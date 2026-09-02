CREATE TABLE IF NOT EXISTS certificates (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    certificate_id VARCHAR(40) NOT NULL,
    student_name VARCHAR(150) NOT NULL,
    enrollment_no VARCHAR(100) NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    issue_date DATE NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'COURSE COMPLETED',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_certificate_id (certificate_id),
    KEY idx_enrollment (enrollment_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
