<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\rest_filtro_sql;
use SIUToba\rest\lib\rest_hidratador;
use SIUToba\rest\lib\rest_validador;


class recurso_session implements SIUToba\rest\lib\modelable{

	public static function _get_modelos()
	{
		$auth = array(
			'usuario' => array('type' => 'string', 'required' => true, '_validar' => array(rest_validador::OBLIGATORIO)),
			'clave'   => array('type' => 'string', 'required' => true, '_validar' => array(rest_validador::OBLIGATORIO)),
		);

		return $auth;
	}

	/**
	 * Se consume en POST /session	 *
	 *
	 * @summary Crea un token JWT
	 * @param_body $auth Autenticar [required] los datos de autenticación
     *
	 * @responses 201 {"id": "integer"}
	 */
	function post_list()
	{
		$datos = $this->procesar_datos();

        $token = $this->autenticar($datos);

		if(!$token){
			return rest::response()->error_negocio('El usuario y/o clave es incorrecto', 500);
		}

        rest::response()->post(array($token));
	}

    protected function procesar_datos()
    {
		$datos = rest::request()->get_body_json();

		rest_validador::validar($datos, $this->_get_modelos(), false);

        return $datos;
    }

    protected function autenticar($datos)
    {
        $session = SIU\JWT\Session::app();

        $session->setDatos($datos);

        $token = $session->autenticar();

        return $token;
    }
}
