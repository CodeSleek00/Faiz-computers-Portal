<?php

$data = json_decode(file_get_contents("php://input"), true);

$image = $data['image'];
$count = $data['count'];
$student_id = $data['student_id'];
$table_name = $data['table_name'];

$image = str_replace('data:image/jpeg;base64,', '', $image);
$image = str_replace(' ', '+', $image);

$decoded = base64_decode($image);

$folder = "../dataset/" . $student_id . "_" . $table_name;

if(!file_exists($folder)){
    mkdir($folder,0777,true);
}

$file = $folder . "/" . $count . ".jpg";

file_put_contents($file, $decoded);

if($count == 15){

    $command = "python ../python/train_single_student.py $student_id $table_name";

    shell_exec($command);
}

?>