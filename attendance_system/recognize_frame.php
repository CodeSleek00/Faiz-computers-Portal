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
include __DIR__ . "/node_config.php";

if ($NODE_API_URL === '') {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "error" => "node_not_configured",
        "detail" => "Set NODE_API_URL to your Node.js Web App public URL (no trailing slash)."
    ]);
    exit;
}

$payload = json_encode(["image" => $data["image"]]);
$url = rtrim($NODE_API_URL, "/") . "/api/recognize";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
$resp = curl_exec($ch);
$err = curl_error($ch);
$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($resp === false) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "node_unreachable", "detail" => $err]);
    exit;
}

http_response_code($code ?: 200);
echo $resp;
