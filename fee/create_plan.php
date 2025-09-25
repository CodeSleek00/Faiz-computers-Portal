<?php
// create_plan.php
include 'db_connect.php';

$enrolled_id = intval($_POST['enrolled_id']);
$course_id = intval($_POST['course_id']);
$new_course = trim($_POST['new_course_name'] ?? '');
$duration = intval($_POST['duration_months']);
$internals = intval($_POST['internals']);
$semesters = intval($_POST['semesters']);

// if new course provided, insert into courses
if($new_course){
  $stmt = $conn->prepare("INSERT INTO courses (course_name) VALUES (?)");
  $stmt->bind_param("s",$new_course);
  $stmt->execute();
  $course_id = $stmt->insert_id;
}

// create plan
$stmt = $conn->prepare("INSERT INTO enrollment_plans (enrolled_id, course_id, duration_months, internals_per_course, semesters) VALUES (?,?,?,?,?)");
$stmt->bind_param("iiiii",$enrolled_id,$course_id,$duration,$internals,$semesters);
$stmt->execute();
$plan_id = $stmt->insert_id;

// generate months: assume starting from today; you can change start date as needed
$start = new DateTime();
for($i=0;$i<$duration;$i++){
    $m = clone $start;
    $m->modify("+{$i} months");
    $monthName = $m->format('F Y'); // e.g., September 2025
    // default amount = 0 (admin can update) OR set formula using course base_fee
    $amount = 0;
    $ins = $conn->prepare("INSERT INTO fee_schedule (plan_id,item_type,item_name,month_name,amount,due_date) VALUES (?,?,?,?,?,?)");
    $name = "Month ".($i+1);
    $due = $m->format('Y-m-d');
    $ins->bind_param("issssd",$plan_id,$type='month',$name,$monthName,$amount,$due);
    $ins->execute();
}

// generate internals
for($j=1;$j<=$internals;$j++){
    $iname = "Internal ".$j;
    $ins = $conn->prepare("INSERT INTO fee_schedule (plan_id,item_type,item_name,amount) VALUES (?,?,?,?)");
    $ins->bind_param("issd",$plan_id,$type='internal',$iname,$amt=0);
    $ins->execute();
}

// generate semesters
for($s=1;$s<=$semesters;$s++){
    $sname = "Semester ".$s;
    $ins = $conn->prepare("INSERT INTO fee_schedule (plan_id,item_type,item_name,amount) VALUES (?,?,?,?)");
    $ins->bind_param("issd",$plan_id,$type='semester',$sname,$amt=0);
    $ins->execute();
}

header("Location: fee_submit.php?enrolled_id={$enrolled_id}");
exit;