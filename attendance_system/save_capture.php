<?php

$data = json_decode(file_get_contents("php://input"), true);

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

// We only enroll on the final capture (15). Intermediate frames are ignored.
if($count == 15){

    include __DIR__ . "/node_config.php";

    if ($NODE_API_URL === '') {
        $respond(500, [
            "ok" => false,
            "error" => "node_not_configured",
            "detail" => "Set NODE_API_URL to your Node.js Web App public URL (no trailing slash)."
        ]);
    }

    $embedding = $data["embedding"] ?? null;
    $payload = json_encode([
        "student_id" => $student_id,
        "table_name" => $table_name,
        "embedding" => $embedding
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

$respond(200, ["ok" => true, "enrolled" => false]);

?>
