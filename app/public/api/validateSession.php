<?php

$db = new app\Database\Database();

$JWT_TOKEN = $_GET['token'] ?? '';
$user_id = $_GET['user_id'] ?? '';

validateInputData([
    'token' => $JWT_TOKEN,
    'user_id' => $user_id
]);

$result = validateJWT($JWT_TOKEN);

if ($result['status'] === 'error' && $result['new_token'] !== 'true') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => $result['message']]);
    exit;
}

if($result['new_token'] === 'true'){
    $user = $db->get('users', "id = '".$user_id."'", "email, pass, token", null, 1)[0];

    if (!$user) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        exit;
    }
    
    $token = $user['token'];
    $checkToken = validateJWT($token);

    if ($checkToken['status'] === 'error') {
        
        $token = generateJWT([
            'user_id' => $user_id,
            'email' => $user['email'],
            'pass' => $user['pass']
        ]);
    
        $db->update('users', array('token' => $token), "id = '".$user_id."'");
    }


    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'token' => $token,
        'message' => 'Token updated successfully',
        'session_validity' => '8 hours'
    ]);
    exit;
}

if ($result['data']->user_id != $user_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'User id does not match with token']);
    exit;
}

http_response_code(200);
echo json_encode($result);
