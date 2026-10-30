# La Generación del Mañana

Plataforma de gestión académica para un colegio, construida con Laravel 12 y MySQL 8.

## Taller en Mac

Esta copia ya está preparada con PHP de XAMPP y SQLite. Para iniciarla ejecuta `cd app` y `./iniciar.command`. Consulta la [guía del sistema](app/README.md) y las [cuentas de acceso](app/ACCESO.md).

El procedimiento siguiente es la alternativa con Docker y MySQL.

## Requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) y Git. No se necesita PHP, Composer, Node ni MySQL locales.
- Windows: Docker Desktop con el backend WSL2.
- Mac: Intel y Apple Silicon (M1/M2/M3) compatibles.

## Inicio rápido

Ejecute todo desde la carpeta `app/`.

1. Clone el repositorio y entre a `app/`:
   ```bash
   git clone <url-del-repositorio>
   cd <repositorio>/app
   ```
2. Cree el archivo de entorno (en PowerShell: `copy .env.example .env`):
   ```bash
   cp .env.example .env
   ```
3. Construya y levante los contenedores (la primera vez tarda varios minutos):
   ```bash
   docker compose up -d --build
   ```
4. Instale las dependencias de PHP:
   ```bash
   docker compose exec laravel.test composer install
   ```
5. Genere la clave de la aplicación:
   ```bash
   docker compose exec laravel.test php artisan key:generate
   ```
6. Cree las tablas de la base de datos:
   ```bash
   docker compose exec laravel.test php artisan migrate
   ```
7. Cree el primer administrador (comando interactivo):
   ```bash
   docker compose exec laravel.test php artisan academic:create-admin
   ```
8. Abra <http://localhost:8088/ingresar>.

## Uso diario

| Acción | Comando |
| --- | --- |
| Levantar | `docker compose up -d` |
| Detener | `docker compose down` |
| Detener y borrar la base de datos | `docker compose down -v` |
| Ejecutar Artisan | `docker compose exec laravel.test php artisan ...` |
| Ver logs | `docker compose logs -f` |

En Mac, Linux o WSL, una vez instalado `vendor/`, puede usar el atajo `./vendor/bin/sail up -d` y `./vendor/bin/sail artisan ...`.

## Puertos y credenciales (solo desarrollo)

| Servicio | Puerto en su equipo | Datos |
| --- | --- | --- |
| Aplicación | 8088 | — |
| MySQL | 3307 | usuario `sail`, contraseña `password`, base `colegio` |

Estas credenciales son fijas y exclusivas para desarrollo; no deben usarse en producción.

## Solución de problemas

- **Puerto en uso:** defina `APP_PORT` y/o `FORWARD_DB_PORT` en `.env` (por ejemplo `APP_PORT=8090`) y ejecute `docker compose up -d` de nuevo.
- **Muy lento en Windows** (páginas que tardan decenas de segundos): Docker lee lento los archivos ubicados en `C:\`. Clone el repositorio dentro de WSL (por ejemplo, abra Ubuntu y ejecute `cd ~ && git clone <url-del-repositorio>`) y trabaje desde ahí. En VS Code use la extensión «WSL» para abrir esa carpeta.
- **Error de saltos de línea en `start-container`** (por ejemplo `exec format error` o `\r: command not found`): ejecute `git config --global core.autocrlf false` y vuelva a clonar el repositorio.

## Más información

Para la instalación basada en Laragon y la guía de uso funcional, consulte [INICIO.md](INICIO.md).
