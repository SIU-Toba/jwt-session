<?php

namespace SIU\JWT;

use SIU\JWT\Util;
use SIU\JWT\Encoder\SimetricEncoder;
use SIU\JWT\Encoder\AsimetricEncoder;

class Session
{
    private $jwt;
    private $encoder;
    private $autenticador;

    protected static $instancia;

    /**
     * Retorna una instancia de Session
     *
     * Nota: crear con Session::getInstance() y configur con Session::setConfigJWT()
     *
     * @return Session
     */
    public static function app()
    {
        if (!isset(self::$instancia)){
            throw new \Exception ('Primero debe generar una instancia de Session');
        }

        return self::$instancia;
    }

    /**
     * Genera una instancia de Session
     *
     * @return Session
     */
    public static function getInstance()
    {
        $session = new Session();

        self::$instancia = $session;

        return $session;
    }

    /**
     * Retorna la ruta al directorio donde está el controlador rest
     *
     * @return string ruta al directorio de controladores
     */
    public static function getPathControlador()
    {
        return dirname(__FILE__). '/rest';
    }

    public static function getDefaultSettings()
    {
        return array(
            'tipo' => 'simetrico',
            'algoritmo' => 'HS512',
            'usuario_id' => 'uid',
            'key_encoder' => '',
        );
    }

    public function __construct()
    {
        $this->jwt = new Util();
    }

    private function configurarEncoder()
    {
        $tipo = $this->settings['tipo'];
        $algoritmo = $this->settings['algoritmo'];
        $key = $this->settings['key_encoder'];

        if ($tipo == 'simetrico') {
            $encoder = new SimetricEncoder ($algoritmo, $key, null);
        } elseif ($tipo == 'asimetrico') {
            $encoder = new AsimetricEncoder($algoritmo, $key, null);
        } else {
            throw new Exception('Se debe configurar un decoder (simetrico|asimetrico) para jwt.');
        }

        $this->encoder = $encoder;
    }

    /**
     * Setea un callback compatible con la interfaz toba_autenticable
     *
     * @param type $callback
     */
    public function setCallbackAutenticador($callback)
    {
        $this->autenticador = $callback;
    }

    /**
     * Permite configurar el encoder JWT para luego generar el token
     *
     * @param array $settings la configuración del encoder JWT
     */
    public function setConfigJWT($settings)
    {
        $this->settings = array_merge(static::getDefaultSettings(), $settings);

        $this->configurarEncoder();
    }

    /**
     * Permite setear los datos que se incluirán en el token a generarse luego
     *
     * @param string $usuario     dato de usuario a colocar en el token
     * @param string $descripcion opcional, dato descriptivo a colocar en el token
     */
    public function setDatosToken($usuario, $descripcion = null)
    {
        $datos[$this->settings['usuario_id']] = $usuario;

        if (isset($descripcion)){
            $datos['desc'] = $descripcion;
        }

        $this->encoder->setToken($datos);
    }

    /**
     * Genera el token segun el encoder utilizado.
     *
     * @param  string el usuario que se autentica
     * @param  string la clave del usuario
     * @return string el token codificado
     *
     * @throws \Exception si no se setea un encoder
     */
    public function autenticar($usuario, $clave)
    {
        $auth = $this->doAutenticacion($usuario, $clave);

        // solo si no pudo validar
        if ($auth !== 1){
            return $auth;
        }

        $this->jwt->setEncoder($this->encoder);

        $token = $this->jwt->encode();

        return $token;
    }

    protected function doAutenticacion($usuario, $clave)
    {
        $objeto = $this->autenticador[0];
        $metodo = $this->autenticador[1];

        if (!method_exists($objeto, $metodo)) {
            return false;
        }

        try {
            $parametros = [$usuario, $clave];
            $result = call_user_func_array(array($objeto, $metodo), $parametros);
        } catch (\Exception $exc) {
            //TODO: al logger
        }
        
        return $result ? 1 : -1;
    }
}
