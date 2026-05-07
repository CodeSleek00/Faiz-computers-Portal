<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Invalid JSON body"]);
    exit;
}

$image = (string)($data["image"] ?? "");
if ($image === "") {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Missing image"]);
    exit;
}

$image = preg_replace('#^data:image/\\w+;base64,#i', '', $image);
$image = str_replace(' ', '+', $image);
$decoded = base64_decode($image);
if ($decoded === false) {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Invalid base64"]);
    exit;
}

$tmpDir = __DIR__ . "/uploads/tmp";
if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}

$tmpFile = $tmpDir . "/frame_" . time() . "_" . bin2hex(random_bytes(4)) . ".jpg";
file_put_contents($tmpFile, $decoded);

$python = "python3";
$script = __DIR__ . "/python/recognize_frame.py";
$cmd = escapeshellcmd($python) . " " . escapeshellarg($script) . " " . escapeshellarg($tmpFile);
$out = trim((string)shell_exec($cmd . " 2>&1"));

@unlink($tmpFile);

if (str_starts_with($out, "OK ")) {
    $parts = explode(" ", $out);
    $student_id = $parts[1] ?? "";
    $table_name = $parts[2] ?? "";

    echo json_encode([
        "ok" => true,
        "recognized" => true,
        "student_id" => $student_id,
        "table_name" => $table_name
    ]);
    exit;
}

echo json_encode([
    "ok" => true,
    "recognized" => false,
    "result" => $out
]);

