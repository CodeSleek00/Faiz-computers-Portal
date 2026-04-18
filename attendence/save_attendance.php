<?php
session_start();
include 'sqlite_config.php';

$is_admin = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'staff']);
if (!$is_admin) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marked_date = isset($_POST['marked_date']) ? $_POST['marked_date'] : date('Y-m-d');
    $students = isset($_POST['students']) ? $_POST['students'] : [];
    $statuses = isset($_POST['status']) ? $_POST['status'] : [];
    $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : [];
    $marked_by = isset($_POST['marked_by']) ? intval($_POST['marked_by']) : 0;

    if (empty($students) || empty($statuses)) {
        $_SESSION['attendance_error'] = 'No attendance data received!';
        header('Location: mark_attendance.php?date=' . $marked_date);
        exit;
    }

    $success_count = 0;
    $error_count = 0;

    try {
        $db->beginTransaction();

        foreach ($students as $student_id) {
            try {
                $student_id = intval($student_id);
                if (!isset($statuses[$student_id])) continue;

                $status = $statuses[$student_id];
                $remark = isset($remarks[$student_id]) ? trim($remarks[$student_id]) : '';

                if (!in_array($status, ['Present', 'Absent', 'Leave'])) {
                    $status = 'Absent';
                }

                $checkStmt = $db->prepare("SELECT id FROM attendance WHERE student_id = ? AND attendance_date = ?");
                $checkStmt->execute([$student_id, $marked_date]);

                if ($checkStmt->rowCount() > 0) {
                    $updateStmt = $db->prepare("UPDATE attendance SET status = ?, remarks = ?, marked_by = ?, updated_at = CURRENT_TIMESTAMP WHERE student_id = ? AND attendance_date = ?");
                    $updateStmt->execute([$status, $remark, $marked_by, $student_id, $marked_date]);
                    $success_count++;
                } else {
                    $insertStmt = $db->prepare("INSERT INTO attendance (student_id, student_name, enrollment_id, attendance_date, status, remarks, marked_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $insertStmt->execute([$student_id, 'Student ' . $student_id, 'ENR' . $student_id, $marked_date, $status, $remark, $marked_by]);
                    $success_count++;
                }
            } catch (PDOException $e) {
                $error_count++;
                error_log('Attendance save error: ' . $e->getMessage());
            }
        }

        $db->commit();
        $_SESSION['attendance_success'] = "Attendance marked successfully! ($success_count records saved)";

    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['attendance_error'] = 'Transaction failed: ' . $e->getMessage();
        error_log('Attendance transaction error: ' . $e->getMessage());
    }
}

header('Location: mark_attendance.php?date=' . (isset($marked_date) ? $marked_date : date('Y-m-d')));
exit;
