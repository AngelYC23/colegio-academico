# Docker y publicación en GitHub personal

Objetivo: que los compañeros (Windows, Mac Intel/Apple Silicon, Linux) levanten el proyecto solo con Docker Desktop y Git, y publicar el repositorio en la cuenta personal de GitHub del usuario (no la del trabajo).

Problema: hoy el arranque depende de Laragon (PHP 8.3 + MySQL 8) instalado localmente.

Alcance autorizado: archivos Docker basados en Laravel Sail dentro de `app/`, documentación de arranque, `.gitattributes`/`.gitignore` raíz, inicialización de Git, commits y push a la cuenta personal.

Restricciones:
- No leer ni modificar `app/.env` ni `app/.env.example` (regla de denegación); el `.env` local de Laragon debe seguir funcionando.
- Pruebas: prohibidas por instrucción previa del usuario. TDD: desactivado (fuente: instrucción del usuario en `odd/tasks/plataforma-gestion-academica.md`). Verificación funcional: levantar los contenedores.
- La instrucción previa de "no inicializar Git" queda reemplazada por el pedido explícito del 2026-10-22 de subir el proyecto.
- Identidad Git y cuenta: personales, configuradas solo en este repositorio; la global (`resttouch`) no se toca. Push solo tras confirmar la cuenta.
- Sin `Co-Authored-By` ni atribución de IA en commits (instrucción global del usuario).

## Tareas

- [ ] T1. Docker (Sail publicado, PHP 8.3, MySQL 8): `app/compose.yaml`, `app/docker/8.3/`. Ruta: delegada (writer; 2+ archivos no triviales). Aceptación: `docker compose config` válido; contenedores arriba; `migrate` aplica; `GET /up` = 200 en `http://localhost:8080`.
- [x] T2. Documentación: `README.md` raíz con arranque Docker paso a paso (Windows/Mac), `.gitattributes` raíz (LF), `.gitignore` raíz. Ruta: delegada junto con T1.
- [x] T3. Git: init en la raíz, identidad personal local, revisión de archivos rastreados (sin `.env`, `vendor`, `node_modules`, logs), commits convencionales. Ruta: inline (estado Git). Bloqueada: cuenta/correo personal pendiente de confirmar.
- [x] T4. Push a repositorio privado en la cuenta personal. Ruta: inline. Bloqueada por T3.

## Progreso

- 2026-10-22: exploración. Laravel 12 en `app/`, `laravel/sail` en require-dev, `app/.gitattributes` ya fuerza LF, primer administrador vía `php artisan academic:create-admin`. Docker 24.0.7 disponible.
- 2026-10-22: T1 parcial. `docker compose config` válido; build OK; MySQL healthy; `migrate` aplicó las 4 migraciones. Puerto 8080 reservado por Windows en este equipo → predeterminado cambiado a 8088 (`compose.yaml`, `README.md`). `/up`, `/ingresar` responden pero tardan de 29 s a 7 min: el cuello de botella es la lectura de archivos montados desde NTFS (`artisan --version` 13,8 s reales con 0,35 s de CPU). Prueba de `vendor/` en volumen Docker no se ejecutó (falló la ruta del override y luego se detuvo por falta de memoria del sistema). Pendiente: elegir solución de rendimiento; confirmar cuenta GitHub AngelYC23.
- 2026-10-22: T2–T4 hechas. Commits 3d889c2, dad123a. Repo privado https://github.com/AngelYC23/colegio-academico. T1 sigue abierta: rendimiento en Windows sin resolver (README recomienda clonar dentro de WSL).
