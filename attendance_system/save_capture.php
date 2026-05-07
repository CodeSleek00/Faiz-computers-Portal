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
$id_col = $data['id_col'] ?? 'id';

$count = (int)$count;
$student_id = preg_replace('/[^0-9]/', '', (string)$student_id);
$table_name = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table_name);
$id_col = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$id_col);

if ($count < 1 || $count > 50) {
    $respond(400, ["ok" => false, "error" => "Invalid capture count"]);
}
if ($student_id === "" || $table_name === "") {
    $respond(400, ["ok" => false, "error" => "Missing student_id or table_name"]);
}
if ($id_col === "") {
    $id_col = "id";
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

    include __DIR__ . "/node_config.php";

    $payload = json_encode([
        "student_id" => $student_id,
        "table_name" => $table_name,
        "image" => $data["image"]
    ]);

    $url = rtrim($NODE_API_URL, "/") . "/api/enroll";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false) {
        $respond(500, ["ok" => false, "error" => "node_unreachable", "detail" => $err]);
    }

    http_response_code($code ?: 200);
    header("Content-Type: application/json");
    echo $resp;
    exit;
}

$respond(200, ["ok" => true, "trained" => false]);

?>
