<?php
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
    'database_name' => 'globaltr_nextlevel',
    'server' => 'localhost',
    'username' => 'globaltr_nluser',
    'password' => 'tvxq1aca',

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
    'database_file' => __DIR__.DIRECTORY_SEPARATOR.'db'.DIRECTORY_SEPARATOR.'database.db'
];

// Memory database
$memory = [
    'database_type' => 'sqlite',
    'database_file' => ':memory:'
];

$conexion = $SQLite;
//$conexion = $MySQL;
//$conexion = $memory;

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

/*
  determine which language out of an available set the user prefers most
  $available_languages        array with language-tag-strings (must be lowercase) that are available
  $http_accept_language    a HTTP_ACCEPT_LANGUAGE string (read from $_SERVER['HTTP_ACCEPT_LANGUAGE'] if left out)
*/
function prefered_language ($available_languages,$http_accept_language="auto") {
    // if $http_accept_language was left out, read it from the HTTP-Header
    if ($http_accept_language == "auto") $http_accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';

    // standard  for HTTP_ACCEPT_LANGUAGE is defined under
    // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
    // pattern to find is therefore something like this:
    //    1#( language-range [ ";" "q" "=" qvalue ] )
    // where:
    //    language-range  = ( ( 1*8ALPHA *( "-" 1*8ALPHA ) ) | "*" )
    //    qvalue         = ( "0" [ "." 0*3DIGIT ] )
    //            | ( "1" [ "." 0*3("0") ] )
    preg_match_all("/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?" .
                   "(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i",
                   $http_accept_language, $hits, PREG_SET_ORDER);

    // default language (in case of no hits) is the first in the array
    $bestlang = $available_languages[0];
    $bestqval = 0;

    foreach ($hits as $arr) {
        // read data from the array of this hit
        $langprefix = strtolower ($arr[1]);
        if (!empty($arr[3])) {
            $langrange = strtolower ($arr[3]);
            $language = $langprefix . "-" . $langrange;
        }
        else $language = $langprefix;
        $qvalue = 1.0;
        if (!empty($arr[5])) $qvalue = floatval($arr[5]);

        // find q-maximal language
        if (in_array($language,$available_languages) && ($qvalue > $bestqval)) {
            $bestlang = $language;
            $bestqval = $qvalue;
        }
        // if no direct hit, try the prefix only but decrease q-value by 10% (as http_negotiate_language does)
        else if (in_array($langprefix,$available_languages) && (($qvalue*0.9) > $bestqval)) {
            $bestlang = $langprefix;
            $bestqval = $qvalue*0.9;
        }
    }
    return $bestlang;
}


$langs = [
    'es' => 'esES',
    'en' => 'enUS'
];

$pref_lang = prefered_language(array_keys($langs));

$_lng = $langs[$pref_lang];

$lngpath = __DIR__.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_lng.'.php';
if(file_exists($lngpath)){
    require_once($lngpath);
}else{
    $_lgn = $langs['es'];
    $lngpath = __DIR__.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_lgn.'.php';
    require_once($lngpath);
}

?>
