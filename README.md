<p align="center"><a href="https://bloomingtec.mx" target="_blank"><img src="https://bloomingtec.mx/assets/img/im/Icono_Blooming_Tec-01.png" width="200" alt="Bloomingtec Logo"></a></p>

## Bloomingtec TODO App

Este repositorio contiene la solución de una prueba técnica para Backend Developer (Laravel). La aplicación expone una API REST para gestionar usuarios y tareas (TODOs) con autenticación mediante JWT.

### Resumen de requisitos implementados
- Framework: [Laravel](https://laravel.com/)
- Base de datos: [MySQL/MariaDB](https://www.mysql.com/) (driver configurable)
- API REST: Endpoints CRUD para Tasks y operaciones básicas sobre Users
- Autenticación: JWT (guard `api` con driver `jwt`)
- Manejo de estados y borrado lógico (Soft Deletes) en `users` y `tasks`
- Manejo de errores: Respuestas JSON estandarizadas usando códigos de estado de `Symfony\Component\HttpFoundation\Response`
- Generación acelerada: [Laravel Blueprint](https://blueprint.laravelshift.com/)
- Validación: Form Requests generadas y adaptadas
- Pruebas: Planificadas (pendientes de integración completa con JWT)
- Documentación: Este README estructurado por secciones (`##`)

## Requisitos
- PHP >= 8.4
- Composer.
- MySQL (o MariaDB) en ejecución.

Variables clave en `.env` ya configuradas: `DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. Aún pendiente agregar `JWT_SECRET` cuando se integre JWT.

## Instalación

### 1. Clonar y dependencias
```bash
git clone https://github.com/rastherdev/bloomingtec.git
cd bloomingtec
composer install
```

### 2. Archivo de entorno
```bash
cp .env.example .env        # Linux / macOS
# PowerShell (Windows):
Copy-Item .env.example .env
```

Generar clave de aplicación si falta:
```bash
php artisan key:generate
```

### 3. Configurar `.env`
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bloomingtec
DB_USERNAME=usuario
DB_PASSWORD=secret
SESSION_DRIVER=file
```

### 4. Migraciones limpias
```bash
php artisan migrate:fresh
```

### 5. (Opcional) Regenerar scaffolding con Blueprint
```bash
php artisan blueprint:build
```

### 6. Instalar / configurar JWT
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

---

## 1. Stack Tecnológico
- PHP 8.2+
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

La generación inicial vía `php artisan blueprint:build` creó migraciones, modelos, factories y controladores que luego se ajustaron manualmente para:
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
- Form Requests aseguran estructura y tipos.
- Respuestas de error usan códigos HTTP estandar (`Response::HTTP_UNPROCESSABLE_ENTITY`, etc.).
- Estructura actual (ejemplo): `{ "message": "Invalid credentials" }`.
- Autorización de recursos de tareas: chequeo explícito de propiedad (puede migrar a Policies).
- Pendiente: formato uniforme con envoltura (`success`, `data`, `errors`, `meta`).

## 7. Flujo de Uso Rápido (Ejemplos cURL)
```bash
# Registro
curl -X POST http://localhost:8000/api/auth/register \
	-H "Content-Type: application/json" \
	-d '{"first_name":"John","last_name":"Doe","email":"john@example.com","password":"secret123","password_confirmation":"secret123"}'

# Login
curl -X POST http://localhost:8000/api/auth/login \
	-H "Content-Type: application/json" \
	-d '{"email":"john@example.com","password":"secret123"}'

# Usar TOKEN (exportar en shell)
export TOKEN=eyJ0eXAiOiJKV1QiLCJhbGciOi... # token devuelto

# Crear Task
curl -X POST http://localhost:8000/api/tasks \
	-H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
	-d '{"title":"Primera tarea","description":"Demo","start_date":"2025-08-26"}'

# Listar Tasks
curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/tasks

# Refresh Token
curl -X POST http://localhost:8000/api/auth/refresh -H "Authorization: Bearer $TOKEN"

# Logout
curl -X POST http://localhost:8000/api/auth/logout -H "Authorization: Bearer $TOKEN"
```

## 8. Variables de Entorno Clave
| Variable | Descripción |
|----------|-------------|
| APP_ENV / APP_DEBUG | Entorno y modo debug |
| DB_CONNECTION / DB_* | Config DB |
| JWT_SECRET | Clave firma tokens (generada) |
| JWT_TTL | Minutos de vigencia del token (ej. 60) |
| JWT_REFRESH_TTL | Ventana total refresh (ej. 20160 = 14 días) |
| JWT_BLACKLIST_ENABLED | Controla blacklist (true recomendado) |
