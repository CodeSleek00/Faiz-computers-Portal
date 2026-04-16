<?php
include '../database_connection/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $date = $_POST['date'];
    $statuses = $_POST['status'];

    if (empty($statuses)) {
        die("No attendance data received.");
    }

    foreach ($statuses as $table_name => $students) {

        foreach ($students as $student_id => $status) {

            $student_id = intval($student_id);
            $table_name = $conn->real_escape_string($table_name);
            $status     = $conn->real_escape_string($status);

            /*
            =====================================
            CHECK IF ALREADY EXISTS
            =====================================
            */
            $check = $conn->query("
                SELECT id FROM attendance 
                WHERE student_id = $student_id 
                AND table_name = '$table_name'
                AND date = '$date'
            ");

            if ($check->num_rows > 0) {

                // UPDATE
                $conn->query("
                    UPDATE attendance 
                    SET status = '$status'
                    WHERE student_id = $student_id 
                    AND table_name = '$table_name'
                    AND date = '$date'
                ");

            } else {

                // INSERT
                $conn->query("
                    INSERT INTO attendance (student_id, table_name, date, status)
                    VALUES ($student_id, '$table_name', '$date', '$status')
                ");
            }
        }
    }

    echo "<script>
        alert('Attendance Saved Successfully!');
        window.location.href='mark_attendance.php';
    </script>";
}
?>