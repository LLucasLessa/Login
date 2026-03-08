<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function validateJWT(string $token): array
{
    try {
        $decoded = JWT::decode(
            $token,
            new Key(JWT_SECRET_KEY, 'HS256')
        );
        return array('status' => 'success', 'data' => $decoded);
    } catch (\Firebase\JWT\ExpiredException $e) {
        return array('status' => 'error', 'message' => 'Token expired', 'new_token' => 'true');
    } catch (\Firebase\JWT\SignatureInvalidException $e) {
        return array('status' => 'error', 'message' => 'Invalid token signature');
    } catch (\Exception $e) {
        return array('status' => 'error', 'message' => 'Invalid token');
    }
}

function generateJWT(array $data): string
{
    $issuedAt = time();
    $expirationTime = $issuedAt + EXPIRED_TIME;
    $payload = array(
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'user_id' => $data['user_id'],
        'email' => $data['email'],
        'pass' => $data['pass']
    );

    return JWT::encode($payload, JWT_SECRET_KEY, 'HS256');
}

function sendEmail($to, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_USER, APP_NAME);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'Email sent successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Email could not be sent', 'error' => $e->getMessage(), 'MailerError' => $mail->ErrorInfo]);
    }
}

function bodyHTMLEmailResetPassword(string $title, string $to, string $token, string $emailTo): string
{
    return "<!DOCTYPE html>
    <html>
    <head>
        <meta charset=\"UTF-8\">
        <title>" . $title . "</title>
    </head>
    <body>
        <h1>" . $title . "</h1>
        <p>Olá " . $to . ",</p>
        <p>Recebemos uma solicitação para redefinir sua senha. Se você não fez essa solicitação, por favor ignore este email.</p>
        <p>Se você fez essa solicitação, clique no link abaixo para redefinir sua senha:</p>
        <a href=\"" . APP_URL . "/app/public/api/resetPassword.php?email=" . $emailTo . "\&token=" . $token . "\">Redefinir Senha</a>
        <p>Este link é válido por 8 horas.</p>
        <p>Atenciosamente,<br>" . APP_NAME . "</p>
    </body>
    </html>";

    // irá enviar o usuário para um front que chamará o endpoint forgetPassword
}

function bodyHTMLEmailValidation(string $title, string $to, string $token, string $emailTo): string
{
    return "<!DOCTYPE html>
    <html>
    <head>
        <meta charset=\"UTF-8\">
        <title>" . $title . "</title>
    </head>
    <body>
        <h1>" . $title . "</h1>
        <p>Olá " . $to . ",</p>
        <p>Obrigado por se registrar. Por favor, clique no link abaixo para validar seu email:</p>
        <a href=\"" . APP_URL . "/app/public/api/emailValidate.php?email=" . $emailTo . "\&token=" . $token . "\">Validar Email</a>
        <p>Este link é válido por 8 horas.</p>
        <p>Atenciosamente,<br>" . APP_NAME . "</p>
    </body>
    </html>";
}

function validateInputData(array $fields): void
{

    if(isset($fields['password']) && isset($fields['confirm_password'])){
        comparePasswords($fields['password'], $fields['confirm_password']);
    }

    foreach ($fields as $field => $value) {

        if (empty($value)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $field . ' is required']);
            exit;
        }

        if($field === 'email'){
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
                exit;
            }
        }

    }
}

function comparePasswords(string $password, string $confirm_password): void
{
    if ($password !== $confirm_password) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'password and confirm password do not match']);
        exit;
    }
}