Este es un proyecto basado en [Laravel](https://laravel.com/), un framework PHP para el desarrollo de aplicaciones web. A continuación se describen los pasos para la instalación y configuración inicial del proyecto.
## Requisitos previos

Antes de instalar Laravel, asegúrate de tener los siguientes requisitos:

- **PHP >= 8.1** (con las siguientes extensiones habilitadas: `BCMath`, `Ctype`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `Tokenizer`, `XML`)
- **Composer** (para gestionar dependencias de PHP)
- **MySQL/MariaDB/PostgreSQL** (o cualquier otro sistema de gestión de bases de datos compatible)
- **Servidor Web** (Apache, Nginx, etc.)
- **Node.js** y **npm/yarn** (para gestionar paquetes de frontend si se usan)

## Instalación

Sigue los pasos para clonar el proyecto e instalar las dependencias:

### 1. Clonar el repositorio

### 2. Instalar dependencias

Instala las dependencias necesarias para el proyecto:
```bash
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
```

## Configuración

Sigue los pasos para configurar el proyecto:

### 1. Corre las migraciones
```bash
php artisan migrate
```

### 2. Crea la cuenta de administrador de FilamentPHP
```bash
php artisan make:filament-user
```

