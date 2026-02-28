<?php

include_once __DIR__ . '/configs.php';
include_once __DIR__ . '/funcoes.php';
include_once __DIR__ . '/app/Database/Database.php';

if (!isset($_SERVER['HTTP_AUTHORIZATION']) || $_SERVER['HTTP_AUTHORIZATION'] !== BASIC_TOKEN) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$endpoint = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

$endpointData = ENDPOINT[$endpoint] ?? false;

if (!$endpointData) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Endpoint not found or not exist']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== $endpointData['method']) {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    require_once __DIR__ . $endpointData['dir'];
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>