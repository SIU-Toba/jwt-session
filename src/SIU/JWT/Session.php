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
     * @return Session
     */
    public static function app()
    {
        if (!isset(self::$instancia)){
            throw new Exception ('Primero debe generar una instancia de Session');
        }

        return self::$instancia;
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
        $this->jwt->setEncoder($this->encoder);

        $token = $this->jwt->encode();

        return $token;
    }
}
