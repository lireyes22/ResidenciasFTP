<?php

namespace App\Models\Ftp;

use Exception;
use Lib\Responses;
use ZipArchive;

class ModelFtp
{

    protected $ftp_server = FTP_SERVER;
    protected $ftp_username = FTP_USERNAME;
    protected $ftp_userpass = FTP_USERPASS;
    protected $local_file;
    protected $ftp_conn;
    protected $bucket = 'SolicitudesResidencia'; // Define your bucket or directory

    public function __construct()
    {
        $this->connection();
    }

    public function connection()
    {
        // Inicia la conexión FTP
        $this->ftp_conn = ftp_connect($this->ftp_server) or die("Could not connect to $this->ftp_server");

        // Intenta iniciar sesión
        $login = @ftp_login($this->ftp_conn, $this->ftp_username, $this->ftp_userpass);

        // Activa el modo pasivo
        ftp_pasv($this->ftp_conn, true);

        if (!$login) {
            // En caso de error al conectar, lanza una excepción o maneja el error de otra manera
            throw new \Exception("Couldn't connect as $this->ftp_username");
        }
    }
    public function listFiles()
    {
        try {
            $responses = new Responses('ModelFtp-listFiles');
            $files_list = ftp_nlist($this->ftp_conn, $this->bucket);
            if ($files_list !== false) {
                return array('status' => 'success', 'files' => $files_list);
            } else {
                return $responses->error_500('Error al listar los archivos');
            }
        } catch (Exception $e) {
            return $responses->error_500($e->getMessage());
        }
    }

    public function getFile($remote_file, $local_file = "File.docx")
    {
        $responses = new Responses('ModelFtp-getFile');
        try {
            $remote_file = $this->bucket . "/" . $remote_file;
            // Intenta descargar un archivo
            if (@ftp_get($this->ftp_conn, $local_file, $remote_file, FTP_BINARY)) {
                // Prepara para enviar el archivo al cliente
                if (file_exists($local_file)) {
                    // Envía las cabeceras adecuadas
                    return [
                        'status' => 'success',
                        'local_file' => $local_file
                    ];
                }
            } else {
                return $responses->errorDoc_404();
            }
        } catch (\Throwable $th) {
            return $responses->error_500();
        }
    }
    public function uploadFile($file)
    {
        try {
            $responses = new Responses('ModelFtp-uploadFile');
            $remote_file = $this->bucket . "/" . $file['name'];
            $file_tmp_name = $file['tmp_name'];
            if (@ftp_put($this->ftp_conn, $remote_file, $file_tmp_name, FTP_BINARY)) {
                return array('status' => 'success', 'message' => 'Archivo Subido Correctamente.');
            } else {
                return $responses->error_500('Error en el servidor FTP');
            }
        } catch (Exception $e) {
            return $responses->error_500($e->getMessage());
        }
    }
    public function deleteFile($file)
    {
        try {
            $responses = new Responses('ModelFtp-deleteFile');
            $remote_file = $this->bucket . "/" . $file;
            if (@ftp_delete($this->ftp_conn, $remote_file)) {
                return array('status' => 'success', 'message' => 'Archivo Eliminado Correctamente.');
            } else {
                return $responses->error_500('Error en el servidor FTP');
            }
        } catch (Exception $e) {
            return $responses->error_500($e->getMessage());
        }
    }
    public function zipFiles($files)
    {
        $zip = new ZipArchive();
        $zip_filename = tempnam(sys_get_temp_dir(), 'zip');
        if ($zip->open($zip_filename, ZipArchive::CREATE) !== TRUE) {
            return array('status' => 'error', 'message' => 'No se pudo abrir el archivo ZIP');
        }

        foreach ($files as $file) {
            $local_file = tempnam(sys_get_temp_dir(), 'ftp');
            if (ftp_get($this->ftp_conn, $local_file, $this->bucket . "/" . $file, FTP_BINARY)) {
                $zip->addFile($local_file, $file);
                unlink($local_file); // Borra el archivo temporal después de añadirlo al ZIP
            } else {
                $zip->close();
                unlink($zip_filename);
                return array('status' => 'error', 'message' => "Error al descargar el archivo: $file");
            }
        }

        $zip->close();

        return array(
            'status' => 'success',
            'zip_file' => $zip_filename,
            'message' => 'Archivos empaquetados correctamente'
        );
    }

    public function __destruct()
    {
        // Cierra la conexión FTP al destruir el objeto
        if ($this->ftp_conn) {
            ftp_close($this->ftp_conn);
        }
    }
}
