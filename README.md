# jwt-session

Esta librería permite autenticar usuarios vía servicios web REST y generar tokens tipo 
JWT (https://jwt.io/). Permite generar y validar los tokens, utilizando para ello 
claves simétricas y/o asimétricas. 

Requiere actualmente la librería [siu-toba/jwt-util](https://github.com/SIU-Toba/jwt-util) para manipular los tokens y [siu-toba/rest](https://github.com/SIU-Toba/rest) para generar la autenticación vía REST.

## Instalación

Usar composer para manejar las dependencias y descargar jwt-util:

```bash
composer require siu-toba/jwt-session
```

Además, en un proyecto standalone, instalar y configurar [siu-toba/rest](https://github.com/SIU-Toba/rest). En un proyecto hecho con SIU-Toba, ya está integrada.

## Integración en una aplicación hecha con SIU-Toba

Para un proyecto hecho con el framework SIU-Toba, agregar en la clase `php/extension_toba/<proyecto>_contexto_ejecucion.php` el siguiente método:

```  php
function conf__rest(SIUToba\rest\rest $rest)
{
        // obtener el toba_modelo_proyecto
        $catalogo = toba_modelo_catalogo::instanciacion();
        $id_instancia = toba::instancia()->get_id();
        $id_proyecto = toba::proyecto()->get_id();
        $modelo_proyecto = $catalogo->get_proyecto($id_instancia, $id_proyecto);

        // leer la config de JWT, desde servidor.ini
        $ini = toba_modelo_rest::get_ini_server($modelo_proyecto);

        $settings = [
            'tipo' => $ini->get('jwt', 'tipo', null, true),
            'algoritmo' => $ini->get('jwt', 'algoritmo', null, true),
            'usuario_id' => $ini->get('jwt', 'usuario_id', null, true),
            'key_encoder' => $ini->get('jwt', 'key_encoder', null, true)
            'exp' => $ini->get('jwt', 'expiracion', null, true)
        ];

        // obtener una instancia del generador de sesiones JWT
        $session = SIU\JWT\Session::getInstance();

        // configurar la librería para generar tokens JWT
        $session->setConfigJWT($settings);

        // configurar un callback para validar el usuario/clave
        $session->setCallbackAutenticador(array(new toba_autenticacion_basica(), 'autenticar'));

        // decir a toba donde encontrar el recurso REST /session de la librería
        $rest->add_path_controlador(SIU\JWT\Session::getPathControlador());
}
```

Una vez configurada la librería en el contexto de ejecución, resta configurar los 
parámetros del servicio REST (vía el archivo `servidor.ini`). Este deberá tener 
una estructura similar a:

```
[jwt]
tipo=simetrico
algoritmo=HS512
usuario_id=uid
key_encoder=test
key_decoder=test
expiracion=+1 Day
```
Valores posibles para los atributos
* `tipo` indica si se desea aplicar una encriptación simétrica o no. Posibles valores: `simetrico`, `asimetrico`.
* `algoritmo` indica el algoritmo de encriptación utilizado. Para mayor detalle de
configuración sobre algoritmos de encriptación soportados, ver opciones disponibles 
en [siu-toba/jwt-util](https://github.com/SIU-Toba/jwt-util).
* `usuario_id` especifica como se llamará el campo en el cual se guarda el usuario, para recuperar luego.
* `key_encoder` especifica la clave para encriptación del token. Si el tipo es
`asimetrico`, se trata de la ruta a una clave privada.
* `key_decoder` especifica la clave para desencriptación del token. Si el tipo es
`asimetrico`, se trata de la ruta a una clave pública.
* `expiracion` define en cuanto tiempo expira el token generado. El formato es el 
que soporta la función [strtotime](http://php.net/manual/en/datetime.formats.php) de PHP.

Se pueden adicionar atributos extra en el método `setConfigJWT`, de acuerdo a lo que 
esté soportado en conjunto con la librería [siu-toba/jwt-util](https://github.com/SIU-Toba/jwt-util).

## Como utilizar

Una vez integrada y configurado los parámetros, para generar tokens JWT es necesario
consumir el recurso REST que estará disponible en http://url-aplicacion/rest/session 
y que aceptará un `usuario` y `clave` mediante POST.

