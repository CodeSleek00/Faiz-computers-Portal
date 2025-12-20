<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Admission Form</title>

<style>
body{
    font-family: Arial, sans-serif;
    background:#f4f6f9;
}
.container{
    width:900px;
    margin:20px auto;
    background:#fff;
    padding:20px;
    border-radius:8px;
}
h2,h3{
    text-align:center;
}
input, textarea, select{
    width:100%;
    padding:8px;
    margin:6px 0;
}
.row{
    display:flex;
    gap:10px;
}
.row input{
    flex:1;
}
.eduRow{
    display:flex;
    gap:8px;
    margin-bottom:6px;
}
.eduRow input{
    flex:1;
}
.eduRow button{
    background:red;
    color:white;
    border:none;
    padding:6px 10px;
    cursor:pointer;
}
.add-btn{
    background:green;
    color:white;
    border:none;
    padding:8px 12px;
    margin-top:8px;
    cursor:pointer;
}
.submit-btn{
    background:#007bff;
    color:white;
    border:none;
    padding:12px;
    font-size:16px;
    cursor:pointer;
}
hr{
    margin:20px 0;
}
</style>

</head>
<body>

<div class="container">
<h2>Student Admission Form</h2>

<form action="submit_admission.php" method="POST" enctype="multipart/form-data">

<!-- BASIC DETAILS -->
<h3>Basic Details</h3>
<div class="row">
<input type="text" name="name" placeholder="Student Name" required>
<input type="text" name="phone" placeholder="Phone Number" required>
</div>

<div class="row">
<input type="email" name="email" placeholder="Email">
<input type="date" name="dob">
</div>

<div class="row">
<input type="text" name="religion" placeholder="Religion">
<input type="text" name="caste" placeholder="Caste">
</div>

<textarea name="address" placeholder="Address"></textarea>
<textarea name="permanent_address" placeholder="Permanent Address"></textarea>

<label>Upload Photo</label>
<input type="file" name="image" required>

<hr>

<!-- PARENT DETAILS -->
<h3>Parent Details</h3>
<div class="row">
<input type="text" name="father" placeholder="Father Name">
<input type="text" name="mother" placeholder="Mother Name">
</div>
<input type="text" name="parent_contact" placeholder="Parent Contact Number">

<hr>

<!-- COURSE DETAILS -->
<h3>Course Details</h3>
<div class="row">
<input type="text" name="course" placeholder="Course Name">
<input type="text" name="duration" placeholder="Duration">
</div>

<div class="row">
<input type="number" name="registration_fee" placeholder="Registration Fee">
<input type="number" name="monthly_fee" placeholder="Per Month Fee">
</div>

<div class="row">
<input type="number" name="internal_fee" placeholder="Internal Fee">
<input type="number" name="semester_fee" placeholder="Semester Exam Fee">
</div>

<input type="number" name="additional_fee" placeholder="Additional Fee">

<hr>

<!-- EDUCATION QUALIFICATION -->
<h3>Education Qualification</h3>

<div id="educationContainer">

<div class="eduRow">
<input type="text" name="degree[]" placeholder="Degree (10th / 12th / BCA)">
<input type="text" name="board[]" placeholder="Board / University">
<input type="number" name="year[]" placeholder="Year of Passing">
<input type="text" name="percentage[]" placeholder="Percentage">
</div>

</div>

<button type="button" class="add-btn" onclick="addEducation()">+ Add Qualification</button>

<hr>

<button type="submit" class="submit-btn">Submit Admission</button>

</form>
</div>

<!-- JAVASCRIPT -->
<script>
function addEducation(){
    let container = document.getElementById("educationContainer");

    let div = document.createElement("div");
    div.className = "eduRow";

    div.innerHTML = `
        <input type="text" name="degree[]" placeholder="Degree">
        <input type="text" name="board[]" placeholder="Board / University">
        <input type="number" name="year[]" placeholder="Year">
        <input type="text" name="percentage[]" placeholder="Percentage">
        <button type="button" onclick="this.parentElement.remove()">X</button>
    `;

    container.appendChild(div);
}
</script>

</body>
</html>
