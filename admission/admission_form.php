<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admission Form</title>
<link rel="stylesheet" href="styles.css">

<style>
.container{
    width: 60%;
    margin: auto;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
}
.step{ display: none; }
.step.active{ display: block; }
button{
    padding: 10px 20px;
    margin-top: 10px;
    cursor: pointer;
}
.next-btn, .prev-btn{
    background: #007bff;
    color: white;
    border: none;
}
.submit-btn{
    background: green;
    color: white;
    border: none;
}
.progress{
    margin-bottom: 20px;
    font-size: 18px;
    font-weight: bold;
}
input, select, textarea{
    width: 100%;
    margin: 8px 0;
    padding: 10px;
}
</style>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

</head>
<body>

<div class="container">

<h2>Student Admission Form</h2>
<div class="progress" id="progressText">Step 1 of 6</div>

<form id="admissionForm" action="submit_admission.php" method="POST" enctype="multipart/form-data">

<!-- HIDDEN INPUT FOR PAYMENT -->
<input type="hidden" name="razorpay_payment_id">
<input type="hidden" name="razorpay_order_id">

<!-- STEP 1 PERSONAL DETAILS -->
<div class="step active">
    <h3>Personal Details</h3>

    <label>Upload Photo</label>
    <input type="file" name="photo" required>

    <input type="text" name="full_name" placeholder="Full Name" required>
    <input type="text" name="aadhar_number" placeholder="Aadhar Number" required>
    <input type="text" name="aapar_id" placeholder="Aapar ID">

    <select name="gender" required>
        <option value="">Select Gender</option>
        <option>Male</option>
        <option>Female</option>
        <option>Other</option>
    </select>

    <input type="text" name="phone" placeholder="Phone Number" required>
    <input type="date" name="dob" required>
    <textarea name="address" placeholder="Address" required></textarea>
    <textarea name="permanent_address" placeholder="Permanent Address" required></textarea>

    <input type="text" name="religion" placeholder="Religion">
    <input type="email" name="email" placeholder="Email Address">
    <input type="text" name="parents_mobile" placeholder="Parents Mobile Number">

    <button type="button" class="next-btn" onclick="nextStep()">Next</button>
</div>

<!-- STEP 2: 10TH DETAILS -->
<div class="step">
    <h3>10th Details</h3>

    <input type="text" name="tenth_school" placeholder="School Name">
    <input type="text" name="tenth_board" placeholder="Board">
    <input type="text" name="tenth_percentage" placeholder="Percentage">
    <input type="text" name="tenth_year" placeholder="Year Of Passing">

    <button type="button" class="prev-btn" onclick="prevStep()">Back</button>
    <button type="button" class="next-btn" onclick="nextStep()">Next</button>
</div>

<!-- STEP 3: 12TH DETAILS -->
<div class="step">
    <h3>12th Details</h3>

    <input type="text" name="twelfth_school" placeholder="School Name">
    <input type="text" name="twelfth_board" placeholder="Board">
    <input type="text" name="twelfth_percentage" placeholder="Percentage">
    <input type="text" name="twelfth_year" placeholder="Year Of Passing">

    <button type="button" class="prev-btn" onclick="prevStep()">Back</button>
    <button type="button" class="next-btn" onclick="nextStep()">Next</button>
</div>

<!-- STEP 4: OTHER QUALIFICATION -->
<div class="step">
    <h3>Other Qualification</h3>

    <input type="text" name="degree_name" placeholder="Degree Name">
    <input type="text" name="college_name" placeholder="College Name">
    <input type="text" name="degree_year" placeholder="Year Of Passing">
    <input type="text" name="degree_percentage" placeholder="Percentage">

    <button type="button" class="prev-btn" onclick="prevStep()">Back</button>
    <button type="button" class="next-btn" onclick="nextStep()">Next</button>
</div>

<!-- STEP 5: COURSE DETAILS -->
<div class="step">
    <h3>Course Details</h3>

    <input type="text" name="course_name" placeholder="Course Name" required>
    <input type="text" name="duration" placeholder="Duration" required>
    <input type="text" name="reg_fee" placeholder="Registration Fee" required>
    <input type="text" name="per_month_fee" placeholder="Per Month Fee" required>
    <input type="text" name="exam_fee" placeholder="Exam Fee" required>
    <input type="text" name="internal_exam_fee" placeholder="Internal Exam Fee" required>

    <button type="button" class="prev-btn" onclick="prevStep()">Back</button>
    <button type="button" class="next-btn" onclick="nextStep()">Next</button>
</div>

<!-- STEP 6 = PAYMENT -->
<div class="step">
    <h3>Payment Method</h3>

    <select name="payment_method" id="payment_method" required>
        <option value="">Choose</option>
        <option value="razorpay">Pay Online (Razorpay)</option>
        <option value="cash">Cash</option>
    </select>

    <button type="button" class="prev-btn" onclick="prevStep()">Back</button>
    <button type="button" class="submit-btn" onclick="submitForm()">Proceed to Payment</button>
</div>

</form>
</div>

<script>
let current = 0;
let steps = document.querySelectorAll(".step");
let progress = document.getElementById("progressText");

function showStep() {
    steps.forEach((step, index) => {
        step.classList.toggle("active", index === current);
    });
    progress.innerHTML = `Step ${current+1} of 6`;
}

function nextStep() {
    let requiredFields = steps[current].querySelectorAll("[required]");
    for (let field of requiredFields) {
        if (!field.value.trim()) {
            alert("Please fill all required fields!");
            return;
        }
    }
    current++;
    showStep();
}

function prevStep() {
    current--;
    showStep();
}
function submitForm() {
    let method = document.getElementById("payment_method").value;

    if (method === "") {
        alert("Please select payment method");
        return;
    }

    if (method === "razorpay") {
        startRazorpay();
    }

    if (method === "cash") {
        if (confirm("Are you sure you want to continue with Cash Payment?")) {
            document.getElementById("admissionForm").submit();
        }
    }
}


function startRazorpay() {
    fetch("create_razorpay_order.php", { method: "POST" })
    .then(res => res.json())
    .then(data => {
        let options = {
            key: data.key,
            amount: data.amount,
            currency: "INR",
            order_id: data.order_id,
            name: "Student Admission",
            handler: function (response) {
                document.querySelector("input[name='razorpay_payment_id']").value = response.razorpay_payment_id;
                document.querySelector("input[name='razorpay_order_id']").value = response.razorpay_order_id;
                document.getElementById("admissionForm").submit();
            }
        };
        let rzp = new Razorpay(options);
        rzp.open();
    });
}
</script>

</body>
</html>
