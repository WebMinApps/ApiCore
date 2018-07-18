<?php


header('Content-Type: application/json');

 /* Archivo de Constantes y Funciones Nativas en ApiCore */
// Valores de constantes Definidas por el usuario
// Contraseñas
define('HASH',          '!DEP*&;=7z!Q');    // String SALT para contraseña crypt
define('MKEY',          'KtBz2.A5*ia1');    // Contraseña para JWT
define('MEMORY_LIMIT',  '128M');

//Configuracion de Correo Electronico
define('BASEMAIL',      '');
define('BASEMAILPASS',  '');
   //Config STMP
define('BASESMTP', 		'');
define('SMTPORT',		'');
//Config POP3
define('BASEPOP3',		'');
define('POP3PORT',		'');

// Estos Valores no se puede cambiar
//Constantes Basicas
define('APP',           'Apicore');
define('WEBMASTER',     'David Salinas');
define('DEVELOPER',     'www.webminapps.com');
define('VER',           '0.1.6 Beta');
define('MODE',          'debug');           // debug or production
define('KEY_HASH',      '$1$'.HASH.'$');
define('HTTP',          'http://');
define('URL',           "{$_SERVER['HTTP_HOST']}/");
define('BASEURL',       HTTP.URL);
define('IP',            "{$_SERVER['REMOTE_ADDR']}");

// Valores de fechas Usados en Estadisticas
define("D",date("d"));
define("M",date("m"));
define("Y",date("Y"));
define("AHORA", time());
define("DIAP", (1 * 24 * 60 * 60));
define("HOY",date("Y-m-d"));
define("AYER",date('Y-m-d',strtotime("-1 day",strtotime(HOY))));
define("MES",date('Y-m-d',strtotime("-1 month",strtotime(date(Y.'-'.M.'-01')))));
define("MES_EX",date('Y-m-d',strtotime("-1 month",strtotime(HOY))));
define("ANO",date('Y-m-d',strtotime("-1 year",strtotime(date(Y.'-01-01')))));
define("ANO_EX",date('Y-m-d',strtotime("-1 year",strtotime(HOY))));

// Constantes de conducta del sitio
define('TIMEZONE',	    'UTC');
define('SESSION_TIME',  AHORA + DIAP);
define('CHARSET',       'utf-8');

//Ajuste de configuracion inicial del servidor
ini_set("default_charset", CHARSET);
ini_set('memory_limit', MEMORY_LIMIT);
date_default_timezone_set(TIMEZONE);

// Configuracion de base de Datos
// MySQL
$MySQL = [
    // required
    'database_type' => 'mysql',
    'database_name' => 'name',
    'server' => 'localhost',
    'username' => 'your_username',
    'password' => 'your_password',

    // [optional]
    'charset' => 'utf8',
    'port' => 3306,

    // [optional] Table prefix
    'prefix' => 'PREFIX_',

    // [optional] Enable logging (Logging is disabled by default for better performance)
    'logging' => true,

    // [optional] MySQL socket (shouldn't be used with server and port)
    //'socket' => '/tmp/mysql.sock',

    // [optional] driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
    'option' => [
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ],

    // [optional] Medoo will execute those commands after connected to the database for initialization
    'command' => [
        'SET SQL_MODE=ANSI_QUOTES'
    ]
];

// MSSQL
$MSSQL = [
    'database_type' => 'mssql',
    'database_name' => '',
    'server' => 'localhost',
    'username' => '',
    'password' => '',

    // [optional] If you want to force Medoo to use dblib driver for connecting MSSQL database
    'driver' => 'dblib'
];

// SQLite
$SQLite = [
    'database_type' => 'sqlite',
    'database_file' => __DIR__.'/db/database.db'
];

// Memory database
$memory = [
    'database_type' => 'sqlite',
    'database_file' => ':memory:'
];

$conexion = $SQLite;

$JWT_DATA = [
    "token_base" => array(
        "iss"       => BASEURL,
        "aud"       => BASEURL,
        "auth_time" => AHORA,
        "iat"       => AHORA,
        "exp"       => AHORA + DIAP
    ),
    "algorithms" => array('HS256')
];
?>
