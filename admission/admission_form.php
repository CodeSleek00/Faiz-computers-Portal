<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Form</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 30px auto;
            background: #fff;
            padding: 25px 35px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #bbb;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea {
            height: 80px;
        }

        .flex {
            display: flex;
            gap: 20px;
        }

        .flex .group {
            flex: 1;
        }

        button {
            padding: 12px 25px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Student Admission Form</h2>

    <form action="submit_admission.php" method="POST" enctype="multipart/form-data">

        <!-- PHOTO -->
        <div class="group">
            <label>Student Photo</label>
            <input type="file" name="photo" required>
        </div>

        <!-- PERSONAL DETAILS -->
        <h3>Personal Information</h3>

        <div class="flex">
            <div class="group">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>

            <div class="group">
                <label>Father's Name</label>
                <input type="text" name="father_name" required>
            </div>
        </div>

        <div class="flex">
            <div class="group">
                <label>Mother's Name</label>
                <input type="text" name="mother_name" required>
            </div>

            <div class="group">
                <label>Caste</label>
                <input type="text" name="caste" required>
            </div>
        </div>

        <div class="flex">
            <div class="group">
                <label>Aadhar Number</label>
                <input type="text" name="aadhar_number" required>
            </div>

            <div class="group">
                <label>Aapar ID</label>
                <input type="text" name="aapar_id" required>
            </div>
        </div>

        <div class="flex">
            <div class="group">
                <label>Gender</label>
                <select name="gender" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            <div class="group">
                <label>Phone</label>
                <input type="text" name="phone" required>
            </div>
        </div>

        <div class="flex">
            <div class="group">
                <label>Date of Birth</label>
                <input type="date" name="dob" required>
            </div>

            <div class="group">
                <label>Religion</label>
                <input type="text" name="religion" required>
            </div>
        </div>

        <div class="group">
            <label>Address</label>
            <textarea name="address" required></textarea>
        </div>

        <div class="group">
            <label>Permanent Address</label>
            <textarea name="permanent_address" required></textarea>
        </div>

        <div class="flex">
            <div class="group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="group">
                <label>Parents Mobile</label>
                <input type="text" name="parents_mobile" required>
            </div>
        </div>

        <!-- EDUCATION DETAILS -->
        <h3>10th Details</h3>
        <div class="flex">
            <div class="group">
                <label>School</label>
                <input type="text" name="tenth_school" required>
            </div>
            <div class="group">
                <label>Board</label>
                <input type="text" name="tenth_board" required>
            </div>
        </div>

        <div class="flex">
            <div class="group">
                <label>Percentage</label>
                <input type="text" name="tenth_percentage" required>
            </div>
            <div class="group">
                <label>Passing Year</label>
                <input type="text" name="tenth_year" required>
            </div>
        </div>

        <h3>12th Details</h3>
        <div class="flex">
            <div class="group">
                <label>School</label>
                <input type="text" name="twelfth_school" required>
            </div>
            <div class="group">
                <label>Board</label>
                <input type="text" name="twelfth_board" required>
            </div>
        </div>

        <div class="flex">
            <div class="group">
                <label>Percentage</label>
                <input type="text" name="twelfth_percentage" required>
            </div>
            <div class="group">
                <label>Passing Year</label>
                <input type="text" name="twelfth_year" required>
            </div>
        </div>

        <h3>Graduation Details</h3>

        <div class="flex">
            <div class="group">
                <label>Degree Name</label>
                <input type="text" name="degree_name" required>
            </div>
            <div class="group">
                <label>College Name</label>
                <input type="text" name="college_name" required>
            </div>
        </div>

        <div class="flex">
            <div class="group">
                <label>Year</label>
                <input type="text" name="degree_year" required>
            </div>
            <div class="group">
                <label>Percentage</label>
                <input type="text" name="degree_percentage" required>
            </div>
        </div>

        <!-- COURSE DETAILS -->
        <h3>Course Details</h3>

        <div class="flex">
            <div class="group">
                <label>Course Name</label>
                <input type="text" name="course_name" required>
            </div>

            <div class="group">
                <label>Duration</label>
                <input type="text" name="duration" required>
            </div>
        </div>

        <div class="flex">
            <div class="group">
                <label>Registration Fee</label>
                <input type="text" name="reg_fee" required>
            </div>

            <div class="group">
                <label>Per Month Fee</label>
                <input type="text" name="per_month_fee" required>
            </div>
        </div>

        <div class="flex">
            <div class="group">
                <label>Exam Fee</label>
                <input type="text" name="exam_fee" required>
            </div>

            <div class="group">
                <label>Internal Exam Fee</label>
                <input type="text" name="internal_exam_fee" required>
            </div>
        </div>

        <div class="group">
            <label>Payment Method</label>
            <select name="payment_method" required>
                <option value="cash">Cash</option>
                <option value="online">Online (Razorpay)</option>
            </select>
        </div>

        <button type="submit">Submit Admission</button>
    </form>
</div>

</body>
</html>
