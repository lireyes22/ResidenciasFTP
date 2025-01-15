<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\key;
use Exception;

class AuthMiddleware
{
    public function handle()
    {
        $token = $this->obtenerTokenDeEncabezado();
        if ($token) {
            $datosToken = $this->verificarToken($token);
            if ($datosToken) {
                return $datosToken;  // El token es válido, retorna los datos decodificados
            } else {
                http_response_code(401);
                echo json_encode(["error" => "Acceso denegado, token inválido"]);
                exit();  // Detiene la ejecución del script
            }
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Acceso denegado, no se proporcionó el token"]);
            exit();  // Detiene la ejecución del script
        }
    }

    private function verificarToken($token) {
        $key = JWT_SECRET_KEY;
        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            return (array) $decoded;
        } catch (Exception $e) {
            return null;  // Token inválido o expirado
        }
    }

    private function obtenerTokenDeEncabezado() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return null;  // No se encontró el encabezado
        }
        $authHeader = $headers['Authorization'];
        $parts = explode(" ", $authHeader);
        if (count($parts) < 2) {
            return null;  // Formato incorrecto del encabezado
        }
        $scheme = $parts[0];
        $token = $parts[1];
        if (strcasecmp($scheme, "Bearer") != 0) {
            return null;  // No es un token de portador
        }
        return $token;
    }
}

