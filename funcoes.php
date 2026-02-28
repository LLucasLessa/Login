<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function validateJWT(string $token): array
{
    try {
        $decoded = JWT::decode(
            $token,
            new Key(JWT_SECRET_KEY, 'HS256')
        );

        return array('status' => 'success', 'data' => $decoded);

    } catch (\Firebase\JWT\ExpiredException $e) {
        return array('status' => 'error', 'message' => 'Token expired');

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
        'password' => $data['pass']
    );

    return JWT::encode($payload, JWT_SECRET_KEY, 'HS256');
}
?>