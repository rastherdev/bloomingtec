<p align="center"><a href="https://bloomingtec.mx" target="_blank"><img src="https://bloomingtec.mx/assets/img/im/Icono_Blooming_Tec-01.png" width="200" alt="Bloomingtec Logo"></a></p>

## Bloomingtec TODO App

Este repositorio contiene la solución de una prueba técnica para Backend Developer (Laravel). La aplicación funciona como una API REST para gestionar usuarios y tareas (TODOs) con autenticación mediante JWT.

### Resumen de requisitos implementados
- Framework: [Laravel](https://laravel.com/)
- Base de datos: [MySQL/MariaDB](https://www.mysql.com/)
- API REST: Endpoints CRUD para Tasks y operaciones básicas sobre Users
- Autenticación: [JWT](https://github.com/tymondesigns/jwt-auth) teniendo en cuenta guard `api` con driver `jwt`.
- Manejo de estados y borrado lógico (Soft Deletes) en `users` y `tasks`
- Manejo de errores: Respuestas JSON estandarizadas usando códigos de estado de `Symfony\Component\HttpFoundation\Response`
- Fast Coding: Implementacion de scaffolding con [Laravel Blueprint](https://blueprint.laravelshift.com/)
- Validación: Form Requests generadas y adaptadas
- Pruebas: Utilizando Pest
- Documentación: Este README estructurado por secciones (`##`)

## Requisitos
- PHP >= 8.4
- Composer.
- MySQL (o MariaDB) en ejecución.

Variables clave en `.env` a configurar: `DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `JWT_SECRET`.

## Instalación

### 1. Clonar y dependencias
```bash
git clone https://github.com/rastherdev/bloomingtec.git
cd bloomingtec
composer install
```

### 2. Archivo de entorno
```bash
Copy-Item .env.example .env
```

### 3. Configurar `.env`
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bloomingtec
DB_USERNAME=<user>
DB_PASSWORD=<password>
SESSION_DRIVER=file
```

### 4. Migraciones iniciales
```bash
php artisan migrate:fresh
```

### 5. Instalar / configurar JWT
```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\\JWTAuth\\Providers\\LaravelServiceProvider"
php artisan jwt:secret
```

---

## Índice
1. Stack Tecnológico
2. Arquitectura y Generación (Blueprint)
3. Modelos y Campos
4. Autenticación JWT (Flujo)
5. Endpoints
6. Validación y Manejo de Errores
7. Flujo de Uso Rápido (Ejemplos cURL)
8. Variables de Entorno Clave
9. Testing (Pruebas unitarias)

---

## 1. Stack Tecnológico
- PHP 8.4+
- Laravel 12
- MySQL / MariaDB (adaptable a SQLite para tests)
- JWT Auth (paquete `tymon/jwt-auth`)
- Blueprint para scaffolding inicial
- Pest (framework de pruebas) + PHPUnit

## 2. Arquitectura y Generación (Blueprint)
Se definió un `draft.yaml` con:
- Modelo `User` (campos de perfil + autenticación + soft deletes)
- Modelo `Task` (asociada a `User`, estado, fechas, soft deletes)
- Controladores API (`AuthController`, `UserController`, `TaskController`)
- Requests de validación y pruebas base

La generación inicial vía `php artisan blueprint:build` creó migraciones, modelos, factories y controladores que luego ajusté.
- Integrar JWT (guard api)
- Respuestas JSON consistentes
- Reemplazar códigos numéricos por constantes HTTP para mejor entendimiento
- Añadir verificación de propiedad en tareas (autorización básica)

## 3. Modelos y Campos
### User
Campos clave: `first_name`, `last_name`, `email` (único), `slug` (identificador URL), `phone`, `password` (hasheado automáticamente), soft deletes (`deleted_at`). Relación: `hasMany(Task)`.

### Task
Campos: `user_id`, `title`, `description` (nullable), `start_date`, `end_date` (nullable), `status` (enum: `incomplete|complete`), soft deletes. Relación: `belongsTo(User)`.

Indices relevantes: email único; `user_id` index + foreign key.

## 4. Autenticación JWT (Flujo)
1. Registro (`POST /api/auth/register`) crea usuario y devuelve token.
2. Login (`POST /api/auth/login`) valida credenciales y devuelve token JWT.
3. Cliente envía `Authorization: Bearer <token>` en cada petición protegida.
4. Middleware `auth:api` valida firma, establece una expiración y blacklist.
5. Refresh (`POST /api/auth/refresh`) entrega nuevo token (rotación segura).
6. Logout (`POST /api/auth/logout`) invalida el token (se marca en blacklist si está habilitada).
7. Endpoint `me` retorna el usuario autenticado.

TTL y refresh configurables vía `.env` (`JWT_TTL`, `JWT_REFRESH_TTL`).

## 5. Endpoints
Base: `/api`

### Auth
| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | /auth/register | Registrar usuario y devolver token |
| POST | /auth/login | Autenticación y emisión de token |
| POST | /auth/logout | Invalida token actual (protegido) |
| POST | /auth/refresh | Refresca token (protegido) |
| GET | /auth/me | Datos del usuario autenticado |

### Users (protegidos)
| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | /users | Crear usuario (uso administrativo) |
| PUT | /users/{user} | Actualizar usuario |
| DELETE | /users/{user} | Borrado lógico |

### Tasks (protegidos)
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | /tasks | Listar tareas del usuario autenticado |
| POST | /tasks | Crear tarea |
| GET | /tasks/{task} | Ver tarea propia |
| PUT | /tasks/{task} | Actualizar tarea propia |
| DELETE | /tasks/{task} | Eliminar (soft delete) |

## 6. Validación y Manejo de Errores
- Form Requests que sirven para asegurar estructura y tipos.
- Respuestas de error usan códigos HTTP estandar (`Response::HTTP_UNPROCESSABLE_ENTITY`, etc.).
- Estructura actual (ejemplo): `{ "message": "Invalid credentials" }`.

## 7. Flujo de Uso Rápido (Ejemplos Postman)

A continuación se describe cómo probar la API usando [Postman](https://www.postman.com/):

1. **Registro de usuario**
	- Método: `POST`
	- URL: `http://localhost:8000/api/auth/register`
	- Body: `raw` y `JSON`:
	  ```json
	  {
		 "first_name": "John",
		 "last_name": "Doe",
		 "email": "john@example.com",
		 "password": "secret123",
		 "password_confirmation": "secret123"
	  }
	  ```

2. **Login**
	- Método: `POST`
	- URL: `http://localhost:8000/api/auth/login`
	- Body: `raw` y `JSON`:
	  ```json
	  {
		 "email": "john@example.com",
		 "password": "secret123"
	  }
	  ```
	- El token JWT se mostrará en la respuesta.

3. **Usar el TOKEN**
	- Copiar el valor del token JWT recibido.
	- En las siguientes peticiones, `Headers`:
	  ```
	  Key: Authorization
	  Value: Bearer <TOKEN>
	  ```

4. **Crear Task**
	- Método: `POST`
	- URL: `http://localhost:8000/api/tasks`
	- Headers: Incluye el header `Authorization` como arriba.
	- Body: `raw` y `JSON`:
	  ```json
	  {
		 "title": "Primera tarea",
		 "description": "Demo",
		 "start_date": "2025-08-26"
	  }
	  ```

5. **Listar Tasks**
	- Método: `GET`
	- URL: `http://localhost:8000/api/tasks`
	- Headers: `Authorization: Bearer <TOKEN>`

6. **Refresh Token**
	- Método: `POST`
	- URL: `http://localhost:8000/api/auth/refresh`
	- Headers: `Authorization: Bearer <TOKEN>`

7. **Logout**
	- Método: `POST`
	- URL: `http://localhost:8000/api/auth/logout`
	- Headers: `Authorization: Bearer <TOKEN>`

## 8. Variables de Entorno Clave
| Variable | Descripción |
|----------|-------------|
| APP_ENV / APP_DEBUG | Entorno y modo debug |
| DB_CONNECTION / DB_* | Config DB |
| JWT_SECRET | Clave firma tokens (generada) |


## 9. Testing (Pruebas Unitarias)

Usé pruebas de tipo Feature con Pest para validar el flujo de la API.

### Suites y Casos Cubiertos
**AuthControllerTest**
- register devuelve token y usuario (201)
- register con email duplicado retorna 422 (valida regla unique)
- login exitoso y me (autenticación + endpoint protegido)
- me sin token retorna no autorizado (401)
- refresh y logout (rotación + invalidación de token)

**TaskControllerTest**
- index retorna solo tareas del usuario autenticado (scoping por usuario)
- store crea tarea para usuario autenticado (201 y propiedad automática)
- show prohibido para otro usuario (403 diferenciando propiedad)
- show no encontrado retorna 404 (ID inexistente)
- show id inválido retorna 422 (id no numérico: letras/símbolos)
- update modifica tarea (PUT con validaciones)
- destroy elimina lógicamente (borrado lógico)

**UserControllerTest**
- store crea usuario (201 + slug autogenerado)
- update modifica usuario (email único ignorando el mismo id)
- destroy elimina lógicamente usuario

**Ejemplo de Tests (plantilla)**
- Unit: assert true
- Feature: endpoint /api/test responde 200

### Estructura de Respuesta Validada
Dentro, se verifican estructuras mínimas (`assertJsonStructure`) en autenticación y paths específicos (`assertJsonPath`) para cambios puntuales (`title`, `first_name`, etc.).

### Errores y Códigos Cubiertos
- 200/201/204: Operaciones exitosas
- 401: Falta/invalid token
- 403: Recurso existente pero sin propiedad
- 404: Recurso inexistente
- 422: Validación (campos faltantes, email duplicado, id inválido, estado inválido)

### Ejecución
```
php artisan test
```
Filtrar por clase o caso:
```
php artisan test --filter=TaskControllerTest
php artisan test --filter=AuthControllerTest::test_login_success_and_me
```