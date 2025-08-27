<p align="center"><a href="https://bloomingtec.mx" target="_blank"><img src="https://bloomingtec.mx/assets/img/im/Icono_Blooming_Tec-01.png" width="200" alt="Bloomingtec Logo"></a></p>

## Bloomingtec TODO App

Este repositorio se trata sobre la prueba tecnica que recibí para poder trabajar como Backend Developer en Bloomingtec.mx, los requisitos fueron los siguientes:

- Tenologías: Lenguaje o Framework, en mi caso [Laravel](https://laravel.com/).
- Bases de datos, para el guardado de la informacion, usé [MySQL](https://www.mysql.com/).
- Realizar Operaciones mediante API tipo REST, POST, GET, PUT, DELETE, etc. 
- Autenticación basica. Utilicé [JWT Auth](https://www.jwt.io/). Es método de autenticación de usuarios que utiliza un formato estándar JSON Web Token.
- Manejo de errores. Las respuestas de las peticiones se manejan a través de la clase [`Illuminate\Http\Client\Response`](https://api.laravel.com/docs/12.x/Illuminate/Http/Client/Response.html).
- Documentacion. En el mismo archivo README.MD, se encontrará la explicación del uso y funcionamiento general de la app.
- Pruebas unitarias. Casi al final, incluyo pruebas unitarias para validar el correcto funcionamiento de la API.
- Extra. Utilicé [Laravel Blueprint](https://blueprint.laravelshift.com/) para acelerar el desarrollo, esta herramienta automatiza la generación de múltiples componentes de una aplicación, como modelos, controladores, migraciones, factories y más, a partir de una única definición en formato YAML.

## Requisitos
- PHP >= 8.4
- Composer.
- MySQL (o MariaDB) en ejecución.

Variables clave en `.env` ya configuradas: `DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. Aún pendiente agregar `JWT_SECRET` cuando se integre JWT.

## Instalación
Resumen de pasos ejecutados hasta ahora:

1. Clonar el repositorio y copiar `.env.example` a `.env`
2. Configurar credenciales de base de datos en `.env`.
3. Definir el `SESSION_DRIVER=file` (API stateless, JWT pendiente).
4. Definir el modelo y controladores con Blueprint en `draft.yaml` y ejecutar `php artisan blueprint:build`.
5. Limpiar migraciones por defecto y ejecutar `php artisan migrate:fresh` para crear tablas `users` y `tasks`.
