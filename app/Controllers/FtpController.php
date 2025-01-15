<?php

namespace App\Controllers;

use App\Models\Ftp\ModelFtp;

class FtpController
{
    public function index()
    {
        $ftpServer = new ModelFtp();
        return $ftpServer->getFile('Prueba.docx');
    }
}
