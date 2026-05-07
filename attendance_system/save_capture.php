<?php

$data = json_decode(file_get_contents("php://input"), true);

$baseDir = __DIR__;
$datasetDir = $baseDir . "/dataset";
$trainerDir = $baseDir . "/trainer";

$ensureDir = function(string $dir): void {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
};

$ensureDir($datasetDir);
$ensureDir($trainerDir);

$respond = function(int $status, array $payload): void {
    http_response_code($status);
    header("Content-Type: application/json");
    echo json_encode($payload);
    exit;
};

if (!is_array($data)) {
    $respond(400, ["ok" => false, "error" => "Invalid JSON body"]);
}

$image = $data['image'];
$count = $data['count'];
$student_id = $data['student_id'];
$table_name = $data['table_name'];

$count = (int)$count;
$student_id = preg_replace('/[^0-9]/', '', (string)$student_id);
$table_name = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table_name);

if ($count < 1 || $count > 50) {
    $respond(400, ["ok" => false, "error" => "Invalid capture count"]);
}
if ($student_id === "" || $table_name === "") {
    $respond(400, ["ok" => false, "error" => "Missing student_id or table_name"]);
}

$image = str_replace('data:image/jpeg;base64,', '', $image);
$image = str_replace(' ', '+', $image);

$decoded = base64_decode($image);

$folder = $datasetDir . "/" . $student_id . "_" . $table_name;

if(!file_exists($folder)){
    mkdir($folder,0777,true);
}

$file = $folder . "/" . $count . ".jpg";

file_put_contents($file, $decoded);

if($count == 15){

    $python = "python3";
    $script = $baseDir . "/python/train_single_student.py";
    $command = escapeshellcmd($python) . " " . escapeshellarg($script) . " " . escapeshellarg($student_id) . " " . escapeshellarg($table_name);
    $output = shell_exec($command . " 2>&1");
    $respond(200, ["ok" => true, "trained" => true, "output" => $output]);
}

$respond(200, ["ok" => true, "trained" => false]);

?>
