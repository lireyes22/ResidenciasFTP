<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Asegura que autoload se carga primero.

// Carga configuraciones y archivos específicos del proyecto.
require_once __DIR__ . '/../config/ftp.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';

// Autoload personalizado para tu proyecto, si es necesario.
require_once __DIR__ . '/../autoload.php'; 

// Sistema de enrutamiento
require_once __DIR__ . '/../routes/api.php';


