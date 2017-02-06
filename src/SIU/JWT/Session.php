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

    private function configurarEncoder($data)
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

    public function setAutenticador(callable $callback)
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
     * @param string $usuario     dato de usuario colocar en el token
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
     * Genera el token según el encoder utilizado.
     *
     * @return string el token codificado
     *
     * @throws \Exception si no se seteó un encoder
     */
    public function autenticar()
    {
        //TODO: aca hay que llamar a $this->autenticador...

        $this->jwt->setEncoder($this->encoder);

        $token = $this->jwt->encode();

        return $token;
    }
}
