# api-client.ps1
# Cliente PowerShell para consumir la API

# ===========================
# CONFIGURACIÓN
# ===========================
$BaseUrl = "http://bloomingtec.test/api"

# ===========================
# REGISTRO (opcional)
# ===========================
function Register-User {
    param(
        [string]$FirstName,
        [string]$LastName,
        [string]$Email,
        [string]$Password
    )

    $body = @{
        first_name = $FirstName
        last_name  = $LastName
        email      = $Email
        password   = $Password
        password_confirmation = $Password
    } | ConvertTo-Json

    $response = Invoke-RestMethod -Uri "$BaseUrl/auth/register" `
        -Method POST `
        -Headers @{ "Content-Type" = "application/json" } `
        -Body $body

    return $response
}

# ===========================
# LOGIN
# ===========================
function Login-User {
    param(
        [string]$Email,
        [string]$Password
    )

    $body = @{
        email    = $Email
        password = $Password
    } | ConvertTo-Json

    $response = Invoke-RestMethod -Uri "$BaseUrl/auth/login" `
        -Method POST `
        -Headers @{ "Content-Type" = "application/json" } `
        -Body $body

    return $response
}

# ===========================
# CREAR TASK
# ===========================
function Create-Task {
    param(
        [string]$Title,
        [string]$Description,
        [string]$StartDate,
        [string]$Token
    )

    $body = @{
        title       = $Title
        description = $Description
        start_date  = $StartDate
    } | ConvertTo-Json

    $response = Invoke-RestMethod -Uri "$BaseUrl/tasks" `
        -Method POST `
        -Headers @{
            "Authorization" = "Bearer $Token"
            "Content-Type"  = "application/json"
        } `
        -Body $body

    return $response
}

# ===========================
# LISTAR TASKS
# ===========================
function Get-Tasks {
    param([string]$Token)

    $response = Invoke-RestMethod -Uri "$BaseUrl/tasks" `
        -Headers @{ "Authorization" = "Bearer $Token" }

    return $response
}

# ===========================
# REFRESH TOKEN
# ===========================
function Refresh-Token {
    param([string]$Token)

    $response = Invoke-RestMethod -Uri "$BaseUrl/auth/refresh" `
        -Method POST `
        -Headers @{ "Authorization" = "Bearer $Token" }

    return $response
}

# ===========================
# LOGOUT
# ===========================
function Logout-User {
    param([string]$Token)

    $response = Invoke-RestMethod -Uri "$BaseUrl/auth/logout" `
        -Method POST `
        -Headers @{ "Authorization" = "Bearer $Token" }

    return $response
}

# ===========================
# FLUJO PRINCIPAL
# ===========================

Write-Host "=== CLIENTE API ===" -ForegroundColor Cyan

# Pedir credenciales
$Email = Read-Host "Correo"
$Password = Read-Host "Contraseña"

# Login
$login = Login-User -Email $Email -Password $Password
$TOKEN = $login.access_token

Write-Host "Login correcto. Token obtenido." -ForegroundColor Green

# Crear una tarea de ejemplo
$task = Create-Task -Title "Primera tarea" -Description "Demo desde PowerShell" -StartDate "2025-08-26" -Token $TOKEN
Write-Host "Tarea creada: $($task.title)" -ForegroundColor Yellow

# Listar tasks
$tasks = Get-Tasks -Token $TOKEN
Write-Host "Listado de tareas:" -ForegroundColor Magenta
$tasks

# Refresh Token
$refresh = Refresh-Token -Token $TOKEN
$TOKEN = $refresh.access_token
Write-Host "Token refrescado." -ForegroundColor Green

# Logout
$logout = Logout-User -Token $TOKEN
Write-Host "Sesión cerrada." -ForegroundColor Red
