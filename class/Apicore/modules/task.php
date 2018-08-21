<?php
namespace apicore\modules;

require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'esES.php');

class task extends \Apicore\bin\Core{

    protected $cols = [];

    // LANG required errors
    protected $unique;

    // Funcion Inicial
    function __construct($ID = false){

        // Variables de Uso general
        global $conexion, $JWT_DATA, $lang;

        // Tabla de BBDD de trabajo de la clase
        $this->t = 'task';

        $this->unique = [
            
        ];

    }


}

?>
