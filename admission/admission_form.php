<!DOCTYPE html>
<html>
<head>
<title>Student Admission</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
<h2>Student Admission Form</h2>

<form action="submit_admission.php" method="POST" enctype="multipart/form-data">

<div class="row">
<input type="text" name="name" placeholder="Student Name" required>
<input type="text" name="aadhar" placeholder="Aadhar Number" required>
</div>

<div class="row">
<input type="text" name="apaar" placeholder="APAAR ID">
<input type="text" name="phone" placeholder="Phone Number" required>
</div>

<input type="email" name="email" placeholder="Email">

<div class="row">
<input type="text" name="religion" placeholder="Religion">
<input type="text" name="caste" placeholder="Caste">
</div>

<textarea name="address" placeholder="Address"></textarea>
<textarea name="permanent_address" placeholder="Permanent Address"></textarea>

<input type="date" name="dob">

<label>Upload Photo</label>
<input type="file" name="image" required>

<hr>

<input type="text" name="degree" placeholder="Degree Name">
<input type="text" name="board" placeholder="Board / University">

<div class="row">
<input type="number" name="year" placeholder="Year of Passing">
<input type="text" name="percentage" placeholder="Percentage">
</div>

<div class="row">
<input type="text" name="father" placeholder="Father Name">
<input type="text" name="mother" placeholder="Mother Name">
</div>

<input type="text" name="parent_contact" placeholder="Parent Contact">

<hr>

<input type="text" name="course" placeholder="Course Name">
<input type="text" name="duration" placeholder="Duration">

<div class="row">
<input type="number" name="registration_fee" placeholder="Registration Fee">
<input type="number" name="monthly_fee" placeholder="Per Month Fee">
</div>

<div class="row">
<input type="number" name="internal_fee" placeholder="Internal Fee">
<input type="number" name="semester_fee" placeholder="Semester Exam Fee">
</div>

<input type="number" name="additional_fee" placeholder="Additional Fee">

<button type="submit">Submit Admission</button>

</form>
</div>

</body>
</html>
