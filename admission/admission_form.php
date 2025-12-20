<?php
// admission_form.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Admission Form</title>
<style>
body{font-family:Arial;background:#f4f6f8}
form{background:#fff;padding:20px;width:90%;max-width:1000px;margin:20px auto;border-radius:8px}
h2{background:#0d6efd;color:#fff;padding:10px;border-radius:4px}
input,select,textarea{width:100%;padding:8px;margin:6px 0}
.row{display:flex;gap:15px;flex-wrap:wrap}
.col{flex:1}
button{padding:10px 15px;border:none;background:#198754;color:#fff;cursor:pointer;margin-top:10px}
.add-btn{background:#0d6efd}
.remove-btn{background:#dc3545}
.edu-box{border:1px solid #ccc;padding:10px;margin-top:10px;border-radius:6px}
</style>
</head>

<body>

<form action="submit_admission.php" method="POST" enctype="multipart/form-data">

<h2>Personal Details</h2>
<div class="row">
  <div class="col"><input type="text" name="name" placeholder="Student Name" required></div>
  <div class="col"><input type="date" name="dob" required></div>
</div>

<div class="row">
  <div class="col"><input type="text" name="aadhar" placeholder="Aadhar Number"></div>
  <div class="col"><input type="text" name="apaar" placeholder="APAAR ID"></div>
</div>

<div class="row">
  <div class="col"><input type="text" name="phone" placeholder="Phone Number" required></div>
  <div class="col"><input type="email" name="email" placeholder="Email ID"></div>
</div>

<input type="text" name="religion" placeholder="Religion">
<input type="text" name="caste" placeholder="Caste">

<textarea name="address" placeholder="Address"></textarea>
<textarea name="permanent_address" placeholder="Permanent Address"></textarea>

<input type="file" name="photo" required>

<hr>

<h2>Parents Details</h2>
<div class="row">
  <div class="col"><input type="text" name="father_name" placeholder="Father's Name"></div>
  <div class="col"><input type="text" name="mother_name" placeholder="Mother's Name"></div>
</div>
<input type="text" name="parent_contact" placeholder="Parent Contact Number">

<hr>

<h2>Education Qualification</h2>

<div id="education_area">

<div class="edu-box">
  <input type="text" name="degree[]" placeholder="Degree Name">
  <input type="text" name="school_college[]" placeholder="School / College Name">
  <input type="text" name="board[]" placeholder="Board / University">
  <input type="text" name="year[]" placeholder="Year of Passing">
  <input type="text" name="percentage[]" placeholder="Percentage">
</div>

</div>

<button type="button" class="add-btn" onclick="addEducation()">➕ Add More Qualification</button>

<hr>

<h2>Course & Fees</h2>
<input type="text" name="course_name" placeholder="Course Name" required>

<div class="row">
  <div class="col"><input type="number" name="duration" placeholder="Duration (Months)" required></div>
  <div class="col"><input type="number" name="registration_fee" placeholder="Registration Fee"></div>
</div>

<div class="row">
  <div class="col"><input type="number" name="monthly_fee" placeholder="Per Month Fee"></div>
  <div class="col"><input type="number" name="internal_fee" placeholder="Internal Fee"></div>
</div>

<div class="row">
  <div class="col"><input type="number" name="semester_fee" placeholder="Semester Exam Fee"></div>
  <div class="col"><input type="number" name="additional_fee" placeholder="Additional Fee"></div>
</div>

<br>
<button type="submit">✅ Submit Admission</button>

</form>

<script>
function addEducation(){
  let div = document.createElement("div");
  div.className = "edu-box";
  div.innerHTML = `
    <input type="text" name="degree[]" placeholder="Degree Name">
    <input type="text" name="school_college[]" placeholder="School / College Name">
    <input type="text" name="board[]" placeholder="Board / University">
    <input type="text" name="year[]" placeholder="Year of Passing">
    <input type="text" name="percentage[]" placeholder="Percentage">
    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
  `;
  document.getElementById("education_area").appendChild(div);
}
</script>

</body>
</html>
