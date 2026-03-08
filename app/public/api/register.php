<?php

$db = new app\Database\Database();

$data = json_decode(file_get_contents('php://input'), true);

validateInputData([
    'name' => $data['name'],
    'email' => $data['email'],
    'password' => $data['password'],
    'confirm_password' => $data['confirm_password']
]);

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

sendEmail($data['email'], 'Bem-vindo ao ' . APP_NAME, bodyHTMLEmailValidation('Bem-vindo ao ' . APP_NAME, $data['name'], $token, $data['email']));

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