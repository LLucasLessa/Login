<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=UTF-8');

define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);

define('BASIC_TOKEN', $_ENV['BASIC_TOKEN']);
define('JWT_SECRET_KEY', $_ENV['JWT_SECRET_KEY']);
define('EXPIRED_TIME', 8 * 60 * 60); // 8 horas

const ENDPOINT = array(
    'login' => [
        'dir' => '/app/public/api/login.php',
        'method' => 'POST'
    ],
    'forget_password' => [
        'dir' => '/app/public/api/forgetPassword.php',
        'method' => 'POST'
    ],
    'register' => [
        'dir' => '/app/public/api/register.php',
        'method' => 'POST'
    ],
    'validate' => [
        'dir' => '/app/public/api/validateSession.php',
        'method' => 'GET'
    ],
    'email_validate' => [
        'dir' => '/app/public/api/emailValidate.php',
        'method' => 'GET'
    ]
);

?>