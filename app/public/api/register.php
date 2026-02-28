<?php

$db = new app\Database\Database();

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name']) || !isset($data['email']) || !isset($data['password']) || !isset($data['confirm_password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'invalid input data']);
    exit;
}

if($data['password'] !== $data['confirm_password']) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'password and confirm password do not match']);
    exit;
}

if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

$checkUser = $db->get('users', "email = '".$data['email']."'", "id", null, 1);

if ($checkUser) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
    exit;
}

$password = password_hash($data['password'], PASSWORD_DEFAULT);

$user = $db->insert('users', array('name' => $data['name'], 'email' => $data['email'], 'pass' => $password));

$token = generateJWT(array('user_id' => $user, 'email' => $data['email'], 'pass' => $password));

$db->update('users', array('token' => $token), "id = '".$user."'");

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'user' => [
        'id' => $user,
        'name' => $data['name'],
    ],
    'token' => $token,
    'message' => 'User registered successfully',
    'session_validity' => '8 hours'
]);
?>