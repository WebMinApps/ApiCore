<?php
namespace apicore\modules;

include_once(__DIR__.'/../../config/config.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config/lang/esES.php');

class user extends \Apicore\bin\Core{

    protected $cols = [
        'user'   => true,
        'pass'   => true,
        'name'   => false,
        'email'  => true,
        'last'   => false,
        'birth'  => false,
        'access' => false,
        'active' => false
    ];

    // Funcion Constructora
    function __construct($ID=false){

        global $conexion, $JWT_DATA, $lang;
        $this->t = 'users';

        $this->l = $lang['user'];

        // Json Web Token
        $this->JWT = new \Firebase\JWT\JWT;

        // Conexion a la Base de Datos
        try{
            $this->db = new \Medoo\Medoo($conexion);
        }catch(\Medoo\Medoo\PDOException $e){
            echo $e->getMessage();
        }


        // Verifica si la table existe
        $tdbr = $this->db->table_exist($this->t);

        // Si no hay tabla
        if(!$tdbr){
            // Create table data

        }

        if($ID){
            $this->msg = "Cargado ".HOY;
            $this->data = $this->user($ID);
        }else{
            $this->msg = "ready";
            $this->data = null;
        }
    }

    // Permite ver si se carga un usuario (LOGIN) o si
    // un campo unico existe
    function exists($where){
        return $this->db->has($this->t,$where);
    }

    // Encriptar contraseña
    function crypt_pass($string){
        $encripted = crypt($string,KEY_HASH);
        return $encripted;
    }

    // Encriptar el JWT
    function encript($data){
        $token = array(
            'iat'=>time(),                      //Token init
            'exp'=>time() + (24 * 60 * 60),     //Token Expires 24h
            'data' => $data                     //Data encrypted
        );
        return $this->JWT->encode($token,MKEY);
    }

    // Desencriptar el JWT
    function decript($data){
        return $this->JWT->decode($data,MKEY,array('HS256'));
    }

    // Mostrar la informacion de un usuarios o todos
    function user($ID = NULL, $cols = null){
        if(is_null($cols)){ $cols = array_keys($this->cols); }
        unset($cols['pass']);
        if($ID){
            $user = $this->db->get($this->t,$cols,['ID'=>$ID]);
        }else{
            $user = $this->db->select($this->t,$cols);
        }
        var_dump($this->db->last());
        return $user;
    }

    // Carga de usuario de JWT
    function JWT_user($data){
        if(isset($data['ID']) && isset($data['user']) && isset($data['email']) && isset($data['pass']) && isset($data['active'])){
            $w = [
                'ID'=>$data['ID'],
                'user'=>$data['user'],
                'pass'=>$data['pass'],
                'email'=>$data['email']
            ];
            $is_user = $this->db->has($this->t,$w);
            return $is_user;
        }else{
            return false;
        }
    }

    //Inicio de sesion de usuario
    function login($data){
        $c = ['ID','user','email','pass','name','last','birth','access','active'];
        $u = isset($data['user'])?(strtolower($data['user'])):NULL;
        $p = isset($data['pass'])?($this->crypt_pass($data['pass'])):NULL;
        if($u){
            if($p){
                $w = ['OR'=>['user'=>$u,'email'=>$u],'pass'=>$p];
                $user = $this->db->get($this->t,$c,$w);
                if($user){
                    if($user['active']){
                        // Variables de tabla de configuracion
                        $tc = 'user_config';
                        $ID = $user['ID'];
                        $ip = $_SERVER['SERVER_ADDR'];

                        // Construactor de datos para MEDOO
                        $data = ['ID' => $ID,'ip' => $ip, 'count[+]'=>1];
                        $w = ['ID'=>$ID];

                        $this->token = $this->encript($user);
                        $ft = $this->db->has($tc,$w);

                        if($ft){
                            $this->db->update($tc,$data,$w);
                        }else{
                            unset($data['count[+]']);
                            $data['theme'] = 1;
                            $data['count'] = 1;
                            $this->db->insert($tc,$data);
                        }
                        return $this->response(['Token'=>$this->token],'Usuario Cargado');
                    }else{
                        return $this->response(null,null,'Cuenta de usuario inactiva', '401');
                    }
                }else{
                    return $this->response(null,null,'Datos inválidos', '401');
                }
            }else{
                // No hay Contraseña enviada
                return $this->response(null,null,'Debes ingresar una contraseña', '401');
            }
        }else{
                // No hay usuario enviado
                return $this->response(null,null,'Debes ingresar tu nombre de usuario', '401');
        }
    }

    // Añadir nuevo usuario
    function add($data){
        $conflict = NULL;
        $message = NULL;
        foreach($this->unique as $col => $msg){
            $check = $this->db->select($this->t,'*',[$col=>strtolower(@$data[$col])]);
            if($check){
                $conflict = $col;
                $message = $msg;
            }
        }
        if($conflict){
            return $this->response(null,null,$message,'409');
        }else{
            $add_data = [];
            foreach($this->cols as $key => $val){
                if($val){
                    if(!empty($data[$key])){
                        // Variable Requerida y existente [OK]
                        if($key === "user" || $key === "email"){
                            if($key === "email"){
                                $checkemail = filter_var($add_data[$key], FILTER_VALIDATE_EMAIL);
                                if($checkemail){
                                    // Formato de correo electronico valido
                                    $add_data[$key] = strtolower($data[$key]);
                                }else{
                                    // formato de correo electronico invalido
                                    return $this->response(null,null,'Formato de correo electronico inválido','400');
                                }
                            }else{
                                $add_data[$key] = strtolower($data[$key]);
                            }
                        }elseif($key === "pass"){
                            $add_data[$key] = $this->crypt_pass($data[$key]);
                        }elseif($key === "active"){
                            $add_data[$key] = '1';
                        }else{
                            $add_data[$key] = $data[$key];
                        }
                    }else{
                        // Variable Requerida y no Existe [Error]
                        return $this->response(null,null,'Falta un campo requerido '.$key,'400');
                    }
                }else{
                    // Variable no requerida [OK] si existe o no
                    if(!empty($data[$key])){
                        if($key === "birth"){
                            if($data[$key]) {
                                $tempdate = $data[$key];
                            }else{
                                $tempdate = HOY;
                            }
                            $add_data[$key] = $tempdate;
                        }else{
                            $add_data[$key] = $data[$key];
                        }
                    }else{
                        if($key === "birth"){
                            $add_data[$key] = HOY;
                        }elseif($key === "access"){
                            $add_data[$key] = 1;
                        }else{
                            $add_data[$key] = NULL;
                        }
                    }
                }
            }
            $this->db->insert($this->t,$add_data);
            $id = $this->db->id();
            $error = $this->db->error();
            if($error[2]){
                $serror = null;
                if(MODE == "debug"){ $serror = ' '.$error[2]; }
                return $this->response(null,null,'Error en consulta DB'.$serror,'406');
            }
            if(!empty($id)){
                //array_unshift($add_data,array());
                $add_data = array('ID'=>$this->db->id()) + $add_data;
                return $this->response($add_data,USER.CREATED);
            }else{
                return $this->response(null,'No se Realizaron cambios',null,'200');
            }
        }

    }

    // Eliminar Usuario
    function del($data){
        $ID = isset($data['ID'])?$data['ID']:null;
        $from = isset($data['from'])?$data['from']:null;
        if(!is_null($ID)){
            if(!is_null($from)){
                if($ID === $from){ // Si la misma persona es la que elimina la cuenta
                    $deleted = $this->db->delete($this->t,['ID'=>$ID]);
                    $afected = $deleted->rowCount();
                    if($afected > 0){
                        $this->db->delete('user_config',['ID'=>$ID]);
                        return $this->response(null,'User Deleted');
                    }else{
                        return $this->response(null,null,'User do not exist or is already deleted',406);
                    }
                }else{
                    $userdta = $this->user($from);
                    $access = $userdta['access'];
                    if($access === '5'){
                        $deleted = $this->db->delete($this->t,['ID'=>$ID]);
                        $afected = $deleted->rowCount();
                        if($afected > 0){
                            return $this->response(null,'User Deleted');
                        }else{
                            return $this->response(null,null,'User do not exist or is already deleted',406);
                        }
                    }else{
                        return $this->response(null,null,'You cannot delete acounts',403);
                    }
                }
            }else{
                // No se puede borrar sin el from
                return $this->response(null,null,'You cannot delete acounts without proper credentials',403);
            }
        }else{
            // No se puede borrar sin ID
            return $this->response(null,null,'You must specify acount to delete',400);
        }
    }

    // Editar Usuario
    function edit($data){
        $ID = isset($data['ID'])?$data['ID']:NULL;
        $from = isset($data['from'])?$data['from']:NULL;
        $data = isset($data['data'])?$this->check_data($data['data'],true):NULL;
        $w = ['ID'=>$ID];
        if($ID){
            if($from){
                if($data){
                    // Edit Data
                    if($ID === $from){
                        // Same user edit
                        if(isset($data['user'])){
                            unset($data['user']);
                        }
                        if(isset($data['email'])){
                            $check_email = $this->db->has($this->t,['email'=> $data['email']]);
                            if(!$check_email){
                                //User change Email Without conflict
                                $edit_user = $this->db->update($this->t,$data,$w);
                                $err = $this->db->error();
                                if($err[2]){
                                    // Si Existe error en la consulta
                                    return $this->response(null,null,'Error update changes','400');
                                }else{
                                    $afected = $edit_user->rowCount();
                                    if(!isset($afected)){
                                        $afected = 0;
                                    }
                                    if($afected>0){
                                        //user edited
                                        return $this->response(null,'User edited');
                                    }else{
                                        // No changes
                                        return $this->response(null,'No Changes');
                                    }
                                }
                            }else{
                                // User Email change conflict
                                return $this->response(null,null,'Email Alredy used',400);
                            }
                        }
                    }else{
                        // Admin user edit
                        $checkadmin = $this->user($from);
                        if($checkadmin['access'] === "5"){
                            // Edited with administrator
                            $edit_user = $this->db->update($this->t,$data,$w);
                            $err = $this->db->error();
                            if($err[2]){
                                // Si Existe error en la consulta
                                return $this->response(null,null,'Error update changes','400');
                            }else{
                                $afected = $edit_user->rowCount();
                                if(!isset($afected)){
                                    $afected = 0;
                                }
                                if($afected>0){
                                //user edited
                                return $this->response(null,'User edited');
                                }else{
                                // No changes
                                return $this->response(null,'No Changes');
                                }
                            }
                        }else{
                            return $this->response(null,null,'You cannot edit user data',400);
                        }
                    }
                }else{
                    return $this->response(null,null,'You Must Specify the data to edit',400);
                }
            }else{
                return $this->response(null,null,'You cannot edit without credentials',400);
            }
        }else{
            return $this->response(null,null,$this->l['editnouser'],400);
        }
    }

    function check_user($data){
        $result = $this->db->has($this->t,$data);
        $error = $this->db->error();
        if($error[2]){
            return $this->response(null,null,$error[2],'400');
        }
        return $this->response($result);
    }
}
?>

