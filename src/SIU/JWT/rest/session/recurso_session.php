<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\rest_validador;


class recurso_session implements SIUToba\rest\lib\modelable{

	public static function _get_modelos()
	{
		$auth = array(
			'usuario' => array('type' => 'string', 'required' => true, '_validar' => array(rest_validador::OBLIGATORIO)),
			'clave'   => array('type' => 'string', 'required' => true, '_validar' => array(rest_validador::OBLIGATORIO)),
		);

		return ['Autenticar'=> $auth];
	}

	/**
	 * Se consume en POST /session
	 *
	 * @summary Crea un token JWT
	 * @param_body $auth Autenticar [required] los datos de autenticacion
     *
	 * @responses 201 {"token": "string"}
	 */
	function post_list()
	{
		$datos = $this->procesar_datos();

        $token = $this->autenticar($datos);

		if($token === -1 || $token === false){
			return rest::response()->error_negocio(['El usuario y/o clave es incorrecto'], 500);
		}

        rest::response()->post(array($token));
	}

    protected function procesar_datos()
    {
		$datos = rest::request()->get_body_json();

        rest_validador::validar($datos, $this->_get_modelos()['Autenticar'], false);

        return $datos;
    }

    protected function autenticar($datos)
    {
        $session = SIU\JWT\Session::app();

        $session->setDatosToken($datos['usuario']);

        $token = $session->autenticar();

        return $token;
    }
}
