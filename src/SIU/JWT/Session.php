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

    public function setCallbackAutenticador(callable $callback)
    {
        $this->autenticador = $autenticador;
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


        $this->buildDatosToken($datos['usuario']);
    }

    /**
     * Genera los datos que se incluiran en el token
     *
     * @param string $usuario     dato de usuario colocar en el token
     * @param string $descripcion opcional, dato descriptivo a colocar en el token
     */
    protected function buildDatosToken($usuario, $descripcion = null)
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
     * @return string el token codificado
     *
     * @throws \Exception si no se setea un encoder
     */
    public function autenticar()
    {
        $auth = $this->doAutenticacion();

        // solo si no pudo validar
        if ($auth !== 1){
            return $auth;
        }

        $this->jwt->setEncoder($this->encoder);

        $token = $this->jwt->encode();

        return $token;
    }

    protected function doAutenticacion()
    {
        $objeto = $this->autenticador[0];
        $metodo = $this->autenticador[1];


        if (!method_exists($objeto, $metodo)) {
            return false;
        }

        try {
            // call_user_func..
            $result = $objeto->$metodo($this->datos['usuario'], $this->datos['clave']);
        } catch (\Exception $exc) {
        }

        return $result ? 1 : -1;
    }
}
