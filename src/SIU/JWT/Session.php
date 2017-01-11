<?php

namespace SIU\JWT;

use SIU\JWT\Util;
use SIU\JWT\Encoder\AbstractEncoder;

class Session
{
    private $jwt;
    private $encoder;
    private $autenticador;

    protected static $instancia;

    /**
     * Retorna una instancia de Session
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
     * Retorna la ruta al directorio donde está el controlador rest
     *
     * @return string ruta al directorio de controladores
     */
    public static function get_path_controlador()
    {
        return dirname(__FILE__). '/rest';
    }

    public function __construct()
    {
        $this->jwt = new Util();

        self::$instancia = $this;
    }

    public function setEncoder(AbstractEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    public function setAutenticador(callable $callback)
    {
        $this->autenticador = $autenticador;
    }

    /**
     * Permite setear los datos que se incluirán en el token a generarse luego
     *
     * @param array $datos los datos a mandar en el token
     */
    public function setDatos($datos)
    {
        $this->encoder->setToken($datos);
    }

    /**
     * Codifica el $token según el encoder utilizado.
     *
     * @return string el $token codificado
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
