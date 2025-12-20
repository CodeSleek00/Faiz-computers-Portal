<?php
include("db_connect.php");

// Fetch all students and their monthly fees
$students = $conn->query("SELECT DISTINCT enrollment_id, name, photo, course_name FROM student_monthly_fee");

echo "<h2>Student Fee Dashboard</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr>
<th>Enrollment ID</th><th>Name</th><th>Course</th><th>Photo</th>
<th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th>
<th>May</th><th>Jun</th><th>Jul</th><th>Aug</th>
<th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th>
<th>Action</th>
</tr>";

while($student = $students->fetch_assoc()){
    echo "<tr>";
    echo "<td>{$student['enrollment_id']}</td>";
    echo "<td>{$student['name']}</td>";
    echo "<td>{$student['course_name']}</td>";
    echo "<td><img src='uploads/{$student['photo']}' width='50'></td>";

    // Fetch monthly fees for this student
    $fees = $conn->query("SELECT * FROM student_monthly_fee WHERE enrollment_id='{$student['enrollment_id']}' ORDER BY month_no");
    $month_arr = [];
    while($fee = $fees->fetch_assoc()){
        if($fee['payment_status']=='Paid'){
            $month_arr[] = "₹".$fee['fee_amount']."<br>".date('d-M', strtotime($fee['payment_date']));
        } else {
            $month_arr[] = "<a href='submit_monthly_fee.php?eid={$student['enrollment_id']}&month_no={$fee['month_no']}'>Pay Now</a>";
        }
    }

    foreach($month_arr as $m){
        echo "<td>$m</td>";
    }

    echo "<td><a href='view_receipt.php?eid={$student['enrollment_id']}'>View Receipt</a></td>";
    echo "</tr>";
}
echo "</table>";
?>
 