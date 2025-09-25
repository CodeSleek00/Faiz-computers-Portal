<?php
// add_enrolled_student.php
include 'db_connect.php';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = $_POST['name'];
    $en_no = $_POST['enrollment_no'];
    $phone = $_POST['phone'];
    $course = $_POST['course'];

    // handle photo
    $photo_path = '';
    if(isset($_FILES['photo']) && $_FILES['photo']['error']===0){
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $fn = 'uploads/photos/' . time() . '_' . rand(100,999) . '.' . $ext;
        if(!is_dir('uploads/photos')) mkdir('uploads/photos',0755,true);
        move_uploaded_file($_FILES['photo']['tmp_name'], $fn);
        $photo_path = $fn;
    }

    $stmt = $conn->prepare("INSERT INTO student_enrolled (photo,name,enrollment_no,phone,course) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssss",$photo_path,$name,$en_no,$phone,$course);
    $stmt->execute();
    header('Location: admin_dashboard.php');
    exit;
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Add Student</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
<body class="p-4">
<div class="container">
  <h3>Add Enrolled Student</h3>
  <form method="post" enctype="multipart/form-data">
    <div class="mb-2"><label>Photo</label><input type="file" name="photo" class="form-control"></div>
    <div class="mb-2"><label>Name</label><input required name="name" class="form-control"></div>
    <div class="mb-2"><label>Enrollment No</label><input required name="enrollment_no" class="form-control"></div>
    <div class="mb-2"><label>Phone</label><input required name="phone" class="form-control"></div>
    <div class="mb-2"><label>Course</label><input required name="course" class="form-control"></div>
    <button class="btn btn-primary">Add</button>
  </form>
</div>
</body></html>
