<?php

$db = new app\Database\Database();

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
    exit;
}

if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

$user = $db->get('users', "email = '".$data['email']."'", "id, name, pass, token", null, 1)[0];

if (!$user) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Email not found']);
    exit;
}

$checkPassword = password_verify($data['password'], $user['pass']);

if (!$checkPassword) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
    exit;
}

$token = $user['token'];

$result = validateJWT($token);

if($result['status'] === 'error') {
    $token = generateJWT([
        'user_id' => $user['id'],
        'email' => $data['email'],
        'pass' => $user['pass']
    ]);

    $db->update('users', array('token' => $token), "id = '".$user['id']."'");
}

echo json_encode([
    'status' => 'success',
    'user' => [
        'id' => $user['id'],
        'name' => $user['name'],
    ],
    'new_token' => $result['status'] === 'error' ? 'true' : 'false',
    'token' => $token,
    'message' => 'Login realizado com sucesso',
    'session_validity' => '8 horas'
]);

?>
