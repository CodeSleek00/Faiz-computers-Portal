<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Admission Form</title>
    <!-- Poppins Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --primary-blue: #0d6efd;
            --accent-color: #198754;
            --light-blue: #e8f4ff;
            --dark-text: #333;
            --light-text: #666;
            --border-color: #ddd;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        body {
            background-color: #f8fafc;
            color: var(--dark-text);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
        }

        .header h1 {
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            color: var(--light-text);
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
        }

        /* Form Container */
        .form-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        /* Progress Bar */
        .progress-container {
            background-color: var(--light-blue);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            position: relative;
        }

        .progress-bar {
            position: absolute;
            top: 50%;
            left: 10%;
            right: 10%;
            height: 4px;
            background-color: #e0e0e0;
            transform: translateY(-50%);
            z-index: 1;
        }

        .progress-fill {
            height: 100%;
            background-color: var(--primary-blue);
            width: 0%;
            transition: var(--transition);
        }

        .step-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 2;
            position: relative;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: white;
            border: 3px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--light-text);
            transition: var(--transition);
        }

        .step-indicator.active .step-circle {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            color: white;
        }

        .step-indicator.completed .step-circle {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
        }

        .step-label {
            margin-top: 8px;
            font-size: 0.85rem;
            color: var(--light-text);
            font-weight: 500;
        }

        .step-indicator.active .step-label {
            color: var(--primary-blue);
            font-weight: 600;
        }

        /* Form Steps */
        .form-step {
            padding: 30px;
            display: none;
        }

        .form-step.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .step-title {
            color: var(--primary-blue);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-blue);
            font-size: 1.5rem;
            font-weight: 600;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-text);
        }

        .required::after {
            content: " *";
            color: #dc3545;
        }

        input, textarea, select {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            background-color: #fcfcfc;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
            background-color: white;
        }

        .file-input-container {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 16px;
            background-color: #f8f9fa;
            border: 2px dashed #ccc;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }

        .file-input-label:hover {
            background-color: #e9ecef;
            border-color: var(--primary-blue);
        }

        .file-input-label i {
            margin-right: 10px;
            color: var(--primary-blue);
        }

        /* Education Qualification Box */
        .education-box {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-blue);
        }

        .education-box:first-child {
            margin-top: 10px;
        }

        .education-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .education-title {
            font-weight: 600;
            color: var(--primary-blue);
        }

        .remove-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .remove-btn:hover {
            background-color: #c82333;
        }

        .add-btn {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px auto;
            transition: var(--transition);
        }

        .add-btn:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
        }

        .add-btn i {
            margin-right: 8px;
        }

        /* Form Navigation */
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }

        .btn-prev {
            background-color: #6c757d;
            color: white;
        }

        .btn-prev:hover {
            background-color: #5a6268;
        }

        .btn-next {
            background-color: var(--primary-blue);
            color: white;
            margin-left: auto;
        }

        .btn-next:hover {
            background-color: #0b5ed7;
        }

        .btn-submit {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-submit:hover {
            background-color: #157347;
        }

        .btn i {
            margin: 0 5px;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .form-step {
                padding: 20px;
            }
            
            .progress-container {
                padding: 15px 10px;
            }
            
            .step-circle {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }
            
            .step-label {
                font-size: 0.75rem;
                text-align: center;
            }
            
            .btn {
                padding: 12px 20px;
                font-size: 0.9rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .progress-bar {
                left: 5%;
                right: 5%;
            }
        }

        @media (max-width: 480px) {
            .form-navigation {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .education-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .remove-btn {
                align-self: flex-end;
            }
        }

        /* Validation Styling */
        .error {
            border-color: #dc3545;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
            display: none;
        }

        /* Success Message */
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--accent-color);
            margin-top: 20px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>FAIZ COMPUTER INSTITUTE</h1>
            <h1>Student Admission Form</h1>
            <p>Complete the following steps to submit your admission application. All fields marked with * are required.</p>
        </div>

        <div class="form-container">
            <!-- Progress Bar -->
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                
                <div class="step-indicator active" id="step1">
                    <div class="step-circle">1</div>
                    <div class="step-label">Personal</div>
                </div>
                <div class="step-indicator" id="step2">
                    <div class="step-circle">2</div>
                    <div class="step-label">Parents</div>
                </div>
                <div class="step-indicator" id="step3">
                    <div class="step-circle">3</div>
                    <div class="step-label">Education</div>
                </div>
                <div class="step-indicator" id="step4">
                    <div class="step-circle">4</div>
                    <div class="step-label">Course & Fees</div>
                </div>
            </div>

            <form id="admissionForm" action="submit_admission.php" method="POST" enctype="multipart/form-data">
                <!-- Step 1: Personal Details -->
                <div class="form-step active" id="formStep1">
                    <h2 class="step-title">Personal Details</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name" class="required">Student Full Name</label>
                            <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                            <div class="error-message" id="nameError">Please enter a valid name</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="dob" class="required">Date of Birth</label>
                            <input type="date" id="dob" name="dob" required>
                            <div class="error-message" id="dobError">Please select your date of birth</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="aadhar">Aadhar Number</label>
                            <input type="text" id="aadhar" name="aadhar" placeholder="12-digit Aadhar number">
                        </div>
                        
                        <div class="form-group">
                            <label for="apaar">APAAR ID</label>
                            <input type="text" id="apaar" name="apaar" placeholder="Enter APAAR ID">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="required">Phone Number</label>
                            <input type="tel" id="phone" name="phone" placeholder="10-digit phone number" required>
                            <div class="error-message" id="phoneError">Please enter a valid phone number</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="your.email@example.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="religion">Religion</label>
                            <input type="text" id="religion" name="religion" placeholder="Enter your religion">
                        </div>
                        
                        <div class="form-group">
                            <label for="caste">Caste</label>
                            <input type="text" id="caste" name="caste" placeholder="Enter your caste">
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="address">Current Address</label>
                            <textarea id="address" name="address" rows="3" placeholder="Enter your current address"></textarea>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="permanent_address">Permanent Address</label>
                            <textarea id="permanent_address" name="permanent_address" rows="3" placeholder="Enter your permanent address"></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label for="identification_mark">Visible Identification Mark</label>
                            <input type="text" id="identification_mark" name="identification_mark"
                                placeholder="e.g., Mole on left cheek, Scar on right hand">
                        </div>

                        
                        <div class="form-group full-width">
                            <label for="photo" class="required">Student Photograph</label>
                            <div class="file-input-container">
                                <input type="file" id="photo" name="photo" class="file-input" accept="image/*" required>
                                <label for="photo" class="file-input-label">
                                    <i>📷</i> <span id="fileLabel">Upload your recent passport size photo (Max 2MB)</span>
                                </label>
                            </div>
                            <div class="error-message" id="photoError">Please upload a valid photograph</div>
                        </div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn btn-next" onclick="nextStep()">Next <i>→</i></button>
                    </div>
                </div>

                <!-- Step 2: Parents Details -->
                <div class="form-step" id="formStep2">
                    <h2 class="step-title">Parents Details</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="father_name">Father's Name</label>
                            <input type="text" id="father_name" name="father_name" placeholder="Enter father's full name">
                        </div>
                        
                        <div class="form-group">
                            <label for="mother_name">Mother's Name</label>
                            <input type="text" id="mother_name" name="mother_name" placeholder="Enter mother's full name">
                        </div>

                        <div class="form-group">
                            <label for="husband_name">Husband Name (if applicable)</label>
                            <input type="text" id="husband_name" name="husband_name" placeholder="Enter husband's name">
                        </div>

                        
                        <div class="form-group full-width">
                            <label for="parent_contact">Parent Contact Number</label>
                            <input type="tel" id="parent_contact" name="parent_contact" placeholder="Parent's phone number">
                        </div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn btn-prev" onclick="prevStep()"><i>←</i> Previous</button>
                        <button type="button" class="btn btn-next" onclick="nextStep()">Next <i>→</i></button>
                    </div>
                </div>

                <!-- Step 3: Education Qualification -->
                <div class="form-step" id="formStep3">
                    <h2 class="step-title">Education Qualification</h2>
                    
                    <div id="education_area">
                        <div class="education-box">
                            <div class="education-header">
                                <div class="education-title">Qualification 1</div>
                                <button type="button" class="remove-btn" onclick="removeEducation(this)">Remove</button>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Degree Name</label>
                                    <input type="text" name="degree[]" placeholder="e.g., Bachelor of Science">
                                </div>
                                <div class="form-group">
                                    <label>School / College Name</label>
                                    <input type="text" name="school_college[]" placeholder="Institution name">
                                </div>
                                <div class="form-group">
                                    <label>Board / University</label>
                                    <input type="text" name="board[]" placeholder="Board or University">
                                </div>
                                <div class="form-group">
                                    <label>Year of Passing</label>
                                    <input type="number" name="year[]" placeholder="YYYY" min="1950" max="2100">
                                </div>
                                <div class="form-group">
                                    <label>Percentage / CGPA</label>
                                    <input type="text" name="percentage[]" placeholder="Percentage or CGPA">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="add-btn" onclick="addEducation()"><i>+</i> Add More Qualification</button>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn btn-prev" onclick="prevStep()"><i>←</i> Previous</button>
                        <button type="button" class="btn btn-next" onclick="nextStep()">Next <i>→</i></button>
                    </div>
                </div>

                <!-- Step 4: Course & Fees -->
                <div class="form-step" id="formStep4">
                    <h2 class="step-title">Course & Fees Details</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="course_name" class="required">Course Name</label>
                            <input type="text" id="course_name" name="course_name" placeholder="e.g., Computer Science" required>
                            <div class="error-message" id="courseError">Please enter a course name</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="duration" class="required">Duration (Months)</label>
                            <input type="number" id="duration" name="duration" placeholder="e.g., 24" min="1" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="registration_fee">Registration Fee (₹)</label>
                            <input type="number" id="registration_fee" name="registration_fee" placeholder="0" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="per_month_fee">Per Month Fee (₹)</label>
                            <input type="number" id="per_month_fee" name="per_month_fee" placeholder="0" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="internal_fee">Internal Fee (₹)</label>
                            <input type="number" id="internal_fee" name="internal_fee" placeholder="0" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="semester_exam_fee">Semester Exam Fee (₹)</label>
                            <input type="number" id="semester_exam_fee" name="semester_exam_fee" placeholder="0" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="additional_fee">Additional Fee (₹)</label>
                            <input type="number" id="additional_fee" name="additional_fee" placeholder="0" min="0">
                        </div>
                    </div>
                    
                    <!-- Success Message -->
                    <div class="success-message" id="successMessage">
                        <strong>Form validation successful!</strong> Please review all information before submitting.
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn btn-prev" onclick="prevStep()"><i>←</i> Previous</button>
                        <button type="submit" class="btn btn-submit">Submit Admission <i>✓</i></button>
                    </div>
                </div>
            </form>
        </div>
       
    </div>

    <script>
/* =========================
   GLOBAL VARIABLES
========================= */
let currentStep = 0;
const steps = document.querySelectorAll(".form-step");
const stepIndicators = document.querySelectorAll(".step-indicator");
const progressFill = document.getElementById("progressFill");

let educationCount = 1;

/* =========================
   STEP DISPLAY FUNCTION
========================= */
function showStep(index) {
    steps.forEach(step => step.classList.remove("active"));
    stepIndicators.forEach(ind => ind.classList.remove("active", "completed"));

    steps[index].classList.add("active");
    stepIndicators[index].classList.add("active");

    for (let i = 0; i < index; i++) {
        stepIndicators[i].classList.add("completed");
    }

    const percent = (index / (steps.length - 1)) * 100;
    progressFill.style.width = percent + "%";

    currentStep = index;
}

/* =========================
   NAVIGATION
========================= */
function nextStep() {
    if (validateStep(currentStep)) {
        if (currentStep < steps.length - 1) {
            showStep(currentStep + 1);
        }
    }
}

function prevStep() {
    if (currentStep > 0) {
        showStep(currentStep - 1);
    }
}

/* =========================
   VALIDATION
========================= */
function clearErrors() {
    document.querySelectorAll(".error").forEach(el => el.classList.remove("error"));
    document.querySelectorAll(".error-message").forEach(el => el.style.display = "none");
}

function validateStep(step) {
    clearErrors();
    let valid = true;

    /* STEP 1 : PERSONAL */
    if (step === 0) {
        const name = document.getElementById("name");
        const dob = document.getElementById("dob");
        const phone = document.getElementById("phone");
        const photo = document.getElementById("photo");

        if (name.value.trim() === "") {
            name.classList.add("error");
            document.getElementById("nameError").style.display = "block";
            valid = false;
        }

        if (!dob.value) {
            dob.classList.add("error");
            document.getElementById("dobError").style.display = "block";
            valid = false;
        }

        if (!/^[0-9]{10}$/.test(phone.value)) {
            phone.classList.add("error");
            document.getElementById("phoneError").style.display = "block";
            valid = false;
        }

        if (photo.files.length === 0) {
            photo.classList.add("error");
            document.getElementById("photoError").style.display = "block";
            valid = false;
        } else if (photo.files[0].size > 2 * 1024 * 1024) {
            photo.classList.add("error");
            document.getElementById("photoError").innerText = "Photo must be under 2MB";
            document.getElementById("photoError").style.display = "block";
            valid = false;
        }
    }

    /* STEP 4 : COURSE & FEES */
    if (step === 3) {
        const course = document.getElementById("course_name");
        const duration = document.getElementById("duration");

        if (course.value.trim() === "") {
            course.classList.add("error");
            document.getElementById("courseError").style.display = "block";
            valid = false;
        }

        if (!duration.value || duration.value <= 0) {
            duration.classList.add("error");
            valid = false;
        }

        if (valid) {
            document.getElementById("successMessage").style.display = "block";
        }
    }

    return valid;
}

/* =========================
   EDUCATION SECTION
========================= */
function addEducation() {
    educationCount++;
    const area = document.getElementById("education_area");

    const div = document.createElement("div");
    div.className = "education-box";
    div.innerHTML = `
        <div class="education-header">
            <div class="education-title">Qualification ${educationCount}</div>
            <button type="button" class="remove-btn" onclick="removeEducation(this)">Remove</button>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label>Degree Name</label>
                <input type="text" name="degree[]">
            </div>
            <div class="form-group">
                <label>School / College</label>
                <input type="text" name="school_college[]">
            </div>
            <div class="form-group">
                <label>Board / University</label>
                <input type="text" name="board[]">
            </div>
            <div class="form-group">
                <label>Year of Passing</label>
                <input type="number" name="year[]" min="1950" max="2100">
            </div>
            <div class="form-group">
                <label>Percentage / CGPA</label>
                <input type="text" name="percentage[]">
            </div>
        </div>
    `;

    area.appendChild(div);
    updateEducationTitles();
    div.scrollIntoView({ behavior: "smooth" });
}

function removeEducation(btn) {
    const boxes = document.querySelectorAll(".education-box");
    if (boxes.length > 1) {
        btn.closest(".education-box").remove();
        updateEducationTitles();
    }
}

function updateEducationTitles() {
    const boxes = document.querySelectorAll(".education-box");
    boxes.forEach((box, i) => {
        box.querySelector(".education-title").innerText = `Qualification ${i + 1}`;
        const removeBtn = box.querySelector(".remove-btn");
        removeBtn.style.display = boxes.length > 1 ? "inline-block" : "none";
    });
    educationCount = boxes.length;
}

/* =========================
   FILE LABEL UPDATE
========================= */
document.getElementById("photo").addEventListener("change", function () {
    const label = document.getElementById("fileLabel");
    label.innerText = this.files.length ? this.files[0].name : "Upload your recent passport size photo (Max 2MB)";
});

/* =========================
   FINAL SUBMIT
========================= */
document.getElementById("admissionForm").addEventListener("submit", function (e) {
    e.preventDefault();

    for (let i = 0; i < steps.length; i++) {
        if (!validateStep(i)) {
            showStep(i);
            return;
        }
    }

    this.submit(); // ✅ ACTUAL SUBMISSION
});

/* =========================
   INIT
========================= */
showStep(0);
updateEducationTitles();
</script>

</body>
</html>