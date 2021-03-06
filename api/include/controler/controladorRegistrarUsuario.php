<?php
require_once FOLDER_MODEL_EXTEND. "model.usuario.inc.php";
require_once FOLDER_MODEL_EXTEND. "model.login.inc.php";

include_once 'php-jwt-master/src/BeforeValidException.php';
include_once 'php-jwt-master/src/ExpiredException.php';
include_once 'php-jwt-master/src/SignatureInvalidException.php';
include_once 'php-jwt-master/src/JWT.php';

use \Firebase\JWT\JWT;


error_reporting(E_ALL);

class controladorRegistrarUsuario extends ModeloUsuario{
  #------------------------------------------------------------------------------------------------------#
  #--------------------------------------------Inicializacion--------------------------------------------#
  #------------------------------------------------------------------------------------------------------#


  function __construct()
  {
    parent::__construct();
  }

  function __destruct()
  {

  }

  

  
  public function postRegistrarUsuario($datos) {
  	$d = json_decode($datos, true);
  	
//.trim()!=="" && this.state.form.genero.trim()!=="" && this.state.form.email.
        if(isset($d['nombre']) &&  isset($d['apellidop']) &&  isset($d['genero']) && isset($d['email']) && isset($d['password']) ){ //
      		$fecha = date('Y-m-j H:i:s');
      		$login = new ModeloLogin();
      		$res = $login->validarUsername($d['email']);
      		if(count($res)>0){
      			return json_encode(array("status" => "ok","estado" => "error", "message" => "ya esta registrado el correo ingresado.", "codigo"=> "200"));
      		}
      		
      		$this->transaccionIniciar();
    	  	$this->setNombre($d['nombre']);
    	  	$this->setApellidoPaterno($d['apellidop']);
    	  	$this->setApellidoMaterno($d['apellidom']);
    	  	$this->setCorreoElectronico($d['email']);
    	  	$this->setSexo($d['genero']);
    	  	//$this->setTelefono($d['telefono']);    	  	
    	  	$this->setEstatusActivo();
    	  	$this->setFechaRegistro($fecha);
    	  	$this->Guardar();
    	  	if($this->getError()){
    	  		$this->transaccionRollback();
    	  		return json_encode(array("status" => "error", "message" => $this->getStrError()));
    	  	}else{
    	  		
    	  		$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
    	  		$passwordSalt = hash('sha512', $d['password']. $random_salt);
    	  		$fecha = date('Y-m-j H:i:s');
    	  		
    	  		$login = new ModeloLogin();
    	  		$login->setIdUsuario($this->getIdUsuario());
    	  		$login->setUserName($d['email']);
    	  		$login->setIdRol(3);//alumno
    	  		$login->setSemilla($random_salt);
    	  		$login->setPassword($passwordSalt);
    	  		$login->setEstatusLoginActivo();
    	  		$login->setFechaRegistro($fecha);
    	  		
    	  		$login->Guardar();
    	  		if($login->getError()){
    	  			$this->transaccionRollback();
    	  		return json_encode(array("status" => "error", "message" => $login->getStrError()));
    	  			return $r;
    	  		}
    	  		$this->transaccionCommit();
    	  		///////////
    	  		global $key;
    	  		$issued_at = time();
    	  		$expiration_time = $issued_at + (60 * 60); // valid for 1 hour
    	  		$issuer = "http://localhost:81/rest/api/";
    	  		$token = array(
    	  				"iat" => $issued_at,
    	  				"exp" => $expiration_time,
    	  				"iss" => $issuer,
    	  				"data" => array(
    	  						"id" => $this->getIdUsuario(),
    	  						"firstname" => $d['nombre'],
    	  						"lastname" => $d['apellidop']  . $d['apellidom'],
    	  						"email" => $d['email']
    	  				)
    	  		);
    	  		// set response code
    	  		http_response_code(200);
    	  		// generate jwt
    	  		$jwt = JWT::encode($token, $key);
    	  		
    	  		
    	  		
    	  		return json_encode(array("status" => "ok", "message" => "Usuario registrado con exito.", "token" => $jwt));;
    	  	}
      	}else{
      		return json_encode(array("status" => "error", "message" => "parametros faltantes.", "codigo"=> "401"));
    	}
    

  	
  }
  }

?>
