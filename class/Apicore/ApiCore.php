<?php
namespace ApiCore;

class ApiCore extends bin\Core {
    function __construct(){
        bin\Core::__construct();
        try{
            $this->user = new modules\user;
        }catch(PDOException $e){
            die('no se pudo conectar');
        }
    }

    /**
     * Main with no attributes load on server
     * @noAuth
     * @url GET /
     */
    public function main(){
        return ['data'=>null,'message'=> 'ready on GET Request'];
    }

    /**
     * Main with no attributes load on server
     * @noAuth
     * @url GET /about
     */
    public function about(){
        return ['data'=>['application'=>APP,'ver'=>VER,'web'=>DEVELOPER],'message'=> 'ready'];
    }

    /**
     * Registro de Usuario.
     * @noAuth
     * @url POST /login
     */
    public function login(){
        return $this->user->login($this->data);
     }

    /**
     * Nuevo Usuario.
     * @noAuth
     * @url GET /user
     * @url GET /user/$id
     */
    public function users($id = null){
        $user = null;
        if($id){
            $this->response($user = $this->user->user($id));
        }else{
            $this->response($user = $this->user->user());
        }
        return $user;
     }

    /**
     * Nuevo Usuario.
     * @noAuth
     * @url POST /user/new
     */
    public function new_user(){
       return $this->user->add($this->data);
    }

    /**
     * Eliminar Usurio.
     * @url POST /user/del
     */
    public function delete_user(){
        return $this->user->del($this->data);
    }

    /**
     * Editar Usuario [Datos, Quien, Por].
     * @url POST /user/edit
     */
    public function edit_user(){

        return $this->user->edit($this->data);
    }

    /**
     * Editar Usuario [Datos, Quien, Por].
     * @url POST /user/check
     * @noAuth
     */
    public function check_user(){
        return $this->user->check_user($this->data);
    }

     /**
     * Throws an error
     * @url GET /error
     */
    public function throwError(){
        throw new RestException(500, "Empty password not allowed");
    }
}?>