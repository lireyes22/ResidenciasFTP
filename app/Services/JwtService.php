<?php

namespace App\Services;
use Firebase\JWT\JWT;

class JwtService
{
    private $key = JWT_SECRET_KEY;
    public function getToken($dataUser)
    {
        $time = time();
        $expirationTime = $time + (365 * 24 * 60 * 60);
        //$expirationTime = $time + 500;
        $payload = [
            'iat' => $time,
            'exp' => $expirationTime,
            'username' => $dataUser['username'],
            'password' => md5($dataUser['password']),
            'typeuser' => $dataUser['typeuser'],
            'status' => $dataUser['status']
        ];
        return JWT::encode($payload, $this->key, 'HS256');
    }
}