<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="max-w-4xl mx-auto p-8 mt-10 bg-white shadow-lg rounded-lg">
        <h2 class="text-3xl font-bold mb-6 text-center text-indigo-600">Edit Student Information</h2>
        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label class="block font-semibold">First Name:</label>
                <input type="text" name="first_name" value="<?= $row['first_name'] ?>" required
                       class="w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold">Last Name:</label>
                <input type="text" name="last_name" value="<?= $row['last_name'] ?>" required
                       class="w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold">Father's Name:</label>
                <input type="text" name="fathers_name" value="<?= $row['fathers_name'] ?>" required
                       class="w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold">Mother's Name:</label>
                <input type="text" name="mothers_name" value="<?= $row['mothers_name'] ?>" required
                       class="w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold">Course:</label>
                <input type="text" name="course" value="<?= $row['course'] ?>" required
                       class="w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold">Phone No:</label>
                <input type="text" name="phone_no" value="<?= $row['phone_no'] ?>" required
                       class="w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold">Aadhar Number:</label>
                <input type="text" name="aadhar_number" value="<?= $row['aadhar_number'] ?>" required
                       class="w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold">ABC ID:</label>
                <input type="text" name="abc_id" value="<?= $row['abc_id'] ?>"
                       class="w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold">Birthday:</label>
                <input type="date" name="birthday" value="<?= $row['birthday'] ?>"
                       class="w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div class="col-span-2">
                <label class="block font-semibold">Address:</label>
                <textarea name="address" required class="w-full p-2 border border-gray-300 rounded-md"><?= $row['address'] ?></textarea>
            </div>

            <div>
                <label class="block font-semibold">Change Photo:</label>
                <input type="file" name="photo" class="w-full p-2 border border-gray-300 rounded-md">
                <div class="mt-2">
                    <img src="../uploads/<?= $row['photo'] ?>" alt="Student Photo" class="w-20 h-20 rounded-md border">
                </div>
            </div>

            <div>
                <label class="block font-semibold">Change Password:</label>
                <input type="password" name="password" required
                       class="w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div class="col-span-2 text-center mt-6">
                <input type="submit" value="Update Student"
                       class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md text-lg font-semibold cursor-pointer transition duration-200">
            </div>
        </form>
    </div>
</body>
</html>
