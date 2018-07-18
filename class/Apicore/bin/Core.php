<?php
namespace Apicore\bin;

include_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'autoloader.php');

// Manejo de Exepciones
use \Jacwright\Restserver\RestException;
use \Firebase\JWT\BeforeValidException;
use \Firebase\JWT\ExpiredException;
use \Firebase\JWT\SignatureInvalidException;

abstract class Core{
    // Reservado Sistema
    protected $auth, $data, $head;

    // Codigos de Error en HTTP
    protected $codes = array(
        '100' => 'Continue',

        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',

        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '307' => 'Temporary Redirect',

        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '409' => 'Conflict',
        '410' => 'Gone',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request-URI Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range Not Satisfiable',
        '417' => 'Expectation Failed',

        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '503' => 'Service Unavailable'
    );

    // Constructor
    function __construct(){
        // Varibles de entrada formato Json Decodificado a un [array assoc]
        $this->data = json_decode(file_get_contents('php://input'),true,1024);
        $this->head = apache_request_headers();
        $this->auth = (!empty($this->head['Authorization']))?$this->head['Authorization']:null;
    }

    // Funcion para autorizar urls seguras
    public function authorize(){
        if(isset($this->auth)){
            try{
                //Verificamos si el Token es Válido
                $token  = (array) $this->user->decript($this->auth);
            }catch(ExpiredException $e){
                $this->response(null,null,$e->getMessage(),'401');
            }catch(BeforeValidException $e){
                $this->response(null,null,$e->getMessage(),'401');
            }catch(SignatureInvalidException $e){
                $this->response(null,null,$e->getMessage(),'401');
            }finally{
                $data = isset($token)? ((array) $token['data']):null;
                if($data){
                    $userchecked = $this->user->JWT_user($data);
                    if($userchecked){
                        // si el Usuario y el token son válidos
                        return $userchecked;
                    }else{
                        // Si el token es valido pero el usuario no
                        return false;
                    }
                }else{
                    // Token inválido
                    return false;
                }
            }
        }else{
            return false;
        }
    }

    // Respuesta por defecto de clase
    public function response($data=null, $message = 'OK', $error = null, $code = '200'){
        if($code == '200'){
            http_response_code($code);
            return [ "data"=>$data, "message" => $message, "error"=>$error, "code"=>$code];
        }elseif($code >= 201 && $code <= 299){
            http_response_code($code);
            return [ "data"=>$data, "message" => $message, "error"=>$error, "code"=>$code];
        }elseif($code >= 300 && $code <= 399){
            throw new RestException($code, $error);
        }elseif($code >= 400 && $code <= 499){
            throw new RestException($code, $error);
        }elseif($code >= 500 && $code <= 599){
            throw new RestException($code, $error);
        }
    }

    function time_elapsed_A($secs){
        $bit = array(
            'y' => $secs / 31556926 % 12,
            'w' => $secs / 604800 % 52,
            'd' => $secs / 86400 % 7,
            'h' => $secs / 3600 % 24,
            'm' => $secs / 60 % 60,
            's' => $secs % 60
        );
        foreach($bit as $k => $v){
            if($v > 0){$ret[] = $v . $k;}
        }
        return join(' ', $ret);
    }


    function time_elapsed_B($secs){
        $bit = array(
            ' año'     => $secs / 31556926 % 12,
            ' semana'  => $secs / 604800 % 52,
            ' dia'     => $secs / 86400 % 7,
            ' hora'    => $secs / 3600 % 24,
            ' minuto'  => $secs / 60 % 60,
            ' segundo' => $secs % 60
            );

        foreach($bit as $k => $v){
            if($v > 1)$ret[] = $v . $k . 's';
            if($v == 1)$ret[] = $v . $k;
            }
        array_splice($ret, count($ret)-1, 0, 'y');
        $ret[] = 'ago.';

        return join(' ', $ret);
    }

    function clrstr($string){

        $string = trim($string);
        $string = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $string
        );
        $string = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $string
        );
        $string = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $string
        );
        $string = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $string
        );
        $string = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $string
        );
        $string = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'),
            array('n', 'N', 'c', 'C',),
            $string
        );
        //Esta parte se encarga de eliminar cualquier caracter extraño
        $string = str_replace(
            array("\\", "¨", "º", "-", "~",
                "#", "@", "|", "!", "\"",
                "·", "$", "%", "&", "/",
                "(", ")", "?", "'", "¡",
                "¿", "[", "^", "<code>", "]",
                "+", "}", "{", "¨", "´",
                ">", "< ", ";", ",", ":",
                ".", " "),
            '',
            $string
        );
        return $string;
    }

    function check_data($data,$force = false){
        $clean_data = [];
        foreach($this->cols as $col => $req){
            if($req || $force){
                if(isset($data[$col])){
                    @$clean_data[$col] = $data[$col];
                }else{
                    // No puede continuar si Falta una variable Requerida no se ejecuta con force
                    if(!$force){
                        return false;
                    }
                }
            }else{
                // No es requerida agrega si existe en el listado
                @$clean_data[$col] = $data[$col];
            }
        }
        return $clean_data;
    }
}
?>