<?php
include 'PHPExcel/Classes/PHPExcel.php'; // adjust path if needed

$conn = new mysqli("localhost", "u298112699_FAIZ2912", "Faiz2912", "u298112699_FAIZ_COMPUTER");
if ($conn->connect_error) {
    die("DB Connection Failed: " . $conn->connect_error);
}

$inputFileName = 'questions.xlsx';

try {
    $excel = PHPExcel_IOFactory::load($inputFileName);
    $sheet = $excel->getActiveSheet();
    $rows = $sheet->toArray();

    foreach ($rows as $index => $row) {
        if ($index == 0) continue; // skip header

        $question = $conn->real_escape_string($row[0]);
        $a = $conn->real_escape_string($row[1]);
        $b = $conn->real_escape_string($row[2]);
        $c = $conn->real_escape_string($row[3]);
        $d = $conn->real_escape_string($row[4]);
        $correct = strtoupper(trim($row[5]));

        $correct_answer = '';
        if ($correct == 'A') $correct_answer = $a;
        elseif ($correct == 'B') $correct_answer = $b;
        elseif ($correct == 'C') $correct_answer = $c;
        elseif ($correct == 'D') $correct_answer = $d;

        $sql = "INSERT INTO questions (question, option_a, option_b, option_c, option_d, correct_answer)
                VALUES ('$question', '$a', '$b', '$c', '$d', '$correct_answer')";
        $conn->query($sql);
    }

    echo "✅ All questions uploaded successfully!";
} catch(Exception $e) {
    die('Error loading file: '.$e->getMessage());
}
?>
