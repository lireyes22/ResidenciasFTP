<?php

namespace App\Controllers;

use App\Models\Ftp\Anteproyecto;
use Lib\Responses;
use App\Middleware\AuthMiddleware;
use Exception;

class AnteproyectoController
{
    public function show()
    {
        $middleware = new AuthMiddleware();
        $middleware->handle();
        $ftpServer = new Anteproyecto();
        return $ftpServer->listFiles();
    }
    public function index($id, $ext)
    {
        $middleware = new AuthMiddleware();
        $middleware->handle();
        $responses = new Responses('AnteproyectoController-index');
        $ftpServer = new Anteproyecto();
        $result = $ftpServer->getFile($id . '.' . $ext);

        if ($result['status'] == 'error') {
            return $result;
        } else if ($result['status'] == 'success') {
            $local_file = $result['local_file'];
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($local_file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($local_file));
            flush();
            readfile($local_file);
            unlink($local_file);
        } else {
            return $responses->error_500();
        }
    }
    public function store()
    {
        try {
            $responses = new Responses('AnteproyectoController-store');
            $middleware = new AuthMiddleware();
            $ftpServer = new Anteproyecto();
            $middleware->handle();
            if (!isset($_FILES['file'])) {
                return $responses->error_400('No se proporciono un archivo');
            }
            $file = $_FILES['file'];
            return $ftpServer->uploadFile($file);
        } catch (Exception $e) {
            return $responses->error_500();
        }
    }
    public function destroy($id, $ext)
    {
        $middleware = new AuthMiddleware();
        $middleware->handle();
        $ftpServer = new Anteproyecto();
        return $ftpServer->deleteFile($id . '.' . $ext);
    }
    public function delete($file)
    {
        $responses = new Responses('AnteproyectoController-delete');
        $middleware = new AuthMiddleware();
        $middleware->handle();
        $file = json_decode($file, true);
        if(!isset($file['file'])){
            return $responses->error_400('No se proporciono nombre de archivo');
        }
        $ftpServer = new Anteproyecto();
        return $ftpServer->deleteFile($file['file']);
    }
}
