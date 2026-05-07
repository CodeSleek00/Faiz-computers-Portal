<?php
header("Content-Type: application/json");

$disabled = (string)ini_get('disable_functions');
$disabledList = array_values(array_filter(array_map('trim', explode(',', $disabled))));

echo json_encode([
    "ok" => true,
    "php_version" => PHP_VERSION,
    "sapi" => PHP_SAPI,
    "disable_functions_raw" => $disabled,
    "shell_exec_function_exists" => function_exists('shell_exec'),
    "exec_function_exists" => function_exists('exec'),
    "shell_exec_listed_disabled" => in_array('shell_exec', $disabledList, true),
    "exec_listed_disabled" => in_array('exec', $disabledList, true),
    "open_basedir" => ini_get('open_basedir'),
    "safe_mode" => ini_get('safe_mode'),
]);

