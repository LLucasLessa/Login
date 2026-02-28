<?php

$db = new app\Database\Database();

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['new_password']) || !isset($data['confirm_password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'invalid input data']);
    exit;
}

if($data['new_password'] !== $data['confirm_password']) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'new password and confirm password do not match']);
    exit;
}

if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

$checkUser = $db->get('users', "email = '".$data['email']."'", "id, name", null, 1)[0];

if (!$checkUser) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email not found']);
    exit;
}

$password = password_hash($data['new_password'], PASSWORD_DEFAULT);

$user = $db->update('users', array('pass' => $password), "email = '".$data['email']."'");

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'user' => [
        'id' => $user,
        'name' => $checkUser['name'],
    ],
    'message' => 'Password changed successfully',
]);
?>