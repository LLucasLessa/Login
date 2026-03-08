<?php
include_once __DIR__ . '/../../../configs.php';
include_once __DIR__ . '/../../../funcoes.php';
include_once __DIR__ . '/../../Database/Database.php';

$email = $_GET['email'];
$token = $_GET['token'];

validateInputData([
    'email' => $email,
    'token' => $token
]);

try{
    $db = new app\Database\Database();
    
    $user = $db->get('users', "email = '" . $email . "' AND token = '" . $token . "'", "email_check, id", null, 1)[0];
    
    if (!$user) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid token or email']);
        exit;
    }
    if ($user['email_check'] == 1) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Email already validated']);
        exit;
    }
    
    $db->update('users', array('email_check' => 1), "id = '" . $user['id'] . "'");
    
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Email validated']);

}catch(Exception $e){
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
