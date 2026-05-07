<?php
// JSON endpoint: never emit HTML warnings/notices into responses.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/php-errors.log');
error_reporting(E_ALL);

header("Content-Type: application/json");

if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0777, true);
}

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        http_response_code(500);
        echo json_encode([
            "ok" => false,
            "error" => "Fatal error",
            "detail" => $err['message'],
            "file" => basename($err['file']),
            "line" => $err['line']
        ]);
    }
});

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

$disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
$shellExecOk = function_exists('shell_exec') && !in_array('shell_exec', $disabled, true) && is_callable('shell_exec');
$execOk = function_exists('exec') && !in_array('exec', $disabled, true) && is_callable('exec');
if (!$shellExecOk && !$execOk) {
    @unlink($tmpFile);
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "error" => "exec disabled on hosting",
        "detail" => "This server blocks running Python from PHP. Enable exec/shell_exec or use a separate Python service."
    ]);
    exit;
}

$python = "python3";
$script = __DIR__ . "/python/recognize_frame.py";
$cmd = escapeshellcmd($python) . " " . escapeshellarg($script) . " " . escapeshellarg($tmpFile);

$out = "";
if ($shellExecOk) {
    $out = (string)\shell_exec($cmd . " 2>&1");
} else {
    $lines = [];
    $exitCode = 0;
    \exec($cmd . " 2>&1", $lines, $exitCode);
    $out = implode("\n", $lines);
}
$out = trim($out);

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
