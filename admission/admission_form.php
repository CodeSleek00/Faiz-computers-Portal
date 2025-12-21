<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Student Admission</title>

<style>
body{
    font-family: Arial;
    background:#f4f6f8;
}
form{
    background:#fff;
    width:90%;
    max-width:900px;
    margin:20px auto;
    padding:20px;
    border-radius:8px;
}
.step{ display:none; }
.step.active{ display:block; }

h2{
    background:#0d6efd;
    color:#fff;
    padding:10px;
    border-radius:4px;
}
input,textarea{
    width:100%;
    padding:8px;
    margin:6px 0;
}
.row{
    display:flex;
    gap:15px;
    flex-wrap:wrap;
}
.col{ flex:1; }

button{
    padding:10px 15px;
    border:none;
    border-radius:4px;
    cursor:pointer;
}
.next{ background:#0d6efd; color:#fff; }
.prev{ background:#6c757d; color:#fff; }
.submit{ background:#198754; color:#fff; }
</style>
</head>

<body>

<form action="submit_admission.php" method="POST" enctype="multipart/form-data">

<!-- ================= STEP 1 ================= -->
<div class="step active">
<h2>Step 1: Personal Details</h2>

<input type="text" name="name" placeholder="Student Name" required>
<input type="date" name="dob" required>

<input type="text" name="aadhar" placeholder="Aadhar Number">
<input type="text" name="phone" placeholder="Phone Number" required>
<input type="email" name="email" placeholder="Email">

<textarea name="address" placeholder="Address"></textarea>

<button type="button" class="next" onclick="nextStep()">Next ➡</button>
</div>

<!-- ================= STEP 2 ================= -->
<div class="step">
<h2>Step 2: Parents & Education</h2>

<input type="text" name="father_name" placeholder="Father Name">
<input type="text" name="mother_name" placeholder="Mother Name">

<h4>Education</h4>
<input type="text" name="degree[]" placeholder="Degree">
<input type="text" name="board[]" placeholder="Board / University">
<input type="text" name="year[]" placeholder="Year">

<button type="button" class="prev" onclick="prevStep()">⬅ Back</button>
<button type="button" class="next" onclick="nextStep()">Next ➡</button>
</div>

<!-- ================= STEP 3 ================= -->
<div class="step">
<h2>Step 3: Course & Fees</h2>

<input type="text" name="course_name" placeholder="Course Name" required>
<input type="number" name="duration" placeholder="Duration (Months)">
<input type="number" name="monthly_fee" placeholder="Monthly Fee">

<button type="button" class="prev" onclick="prevStep()">⬅ Back</button>
<button type="button" class="next" onclick="nextStep()">Next ➡</button>
</div>

<!-- ================= STEP 4 ================= -->
<div class="step">
<h2>Step 4: Review & Submit</h2>
<p>✔ Please check all details before submit</p>

<button type="button" class="prev" onclick="prevStep()">⬅ Back</button>
<button type="submit" class="submit">✅ Submit Admission</button>
</div>

</form>

<script>
let currentStep = 0;
let steps = document.querySelectorAll(".step");

function nextStep(){
    steps[currentStep].classList.remove("active");
    currentStep++;
    steps[currentStep].classList.add("active");
}

function prevStep(){
    steps[currentStep].classList.remove("active");
    currentStep--;
    steps[currentStep].classList.add("active");
}
</script>

</body>
</html>
