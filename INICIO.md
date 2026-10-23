# Plataforma de gestión académica

Aplicación Laravel 12 para un colegio, con interfaz Bootstrap y persistencia relacional MySQL 8.x. El código de la aplicación está en `app/`; el registro de trabajo está en `odd/tasks/`. No hay usuario o contraseña predeterminados.

## Puesta en marcha local

1. Utilice PHP 8.3 de Laragon (`C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`) y MySQL 8.x. Coloque el PHP de Laragon primero en `PATH` antes de llamar a Composer o Artisan.
2. Use una base exclusiva con una cuenta MySQL propia que tenga permisos sobre esa base. No use una base ajena. La conexión MySQL configurada por el usuario ya permitió aplicar las migraciones. Conserve la contraseña solamente en el `.env` local; no la publique.
3. En `app/.env`, configure la dirección local, la conexión MySQL y la clave de la aplicación según su entorno, sin publicar sus valores ni credenciales.
4. Las cuatro migraciones ya figuran como aplicadas en MySQL y una consulta de solo lectura confirmó **una cuenta administradora activa**. No es necesario crear la primera cuenta de nuevo ni generar usuarios de demostración.
5. Para esta instancia, visite `http://127.0.0.1:8001/ingresar`. Si el servidor local dejó de ejecutarse al terminar la sesión, inícielo desde `app/` con PHP 8.3 de Laragon mediante `php artisan serve --host=127.0.0.1 --port=8001`. El puerto 8000 corresponde a otra aplicación y devolvió 404 para `/ingresar`. También puede configurar un sitio Laragon cuyo directorio web sea `app/public` (no la raíz del proyecto).

> El comando `php` de los pasos anteriores debe apuntar a Laragon PHP 8.3, no a XAMPP PHP 7.4. Para Composer, si falta la extensión ZIP, ejecute con el binario de Laragon y `-d extension=zip` sobre `C:\laragon\bin\composer\composer.phar`.

## Uso

En la página de ingreso, escriba usted mismo las credenciales de su cuenta administradora; no las comparta ni las incluya en diagnósticos. El inicio de sesión de esa cuenta todavía no se ha observado.

El administrador registra cuentas y perfiles, parentescos, ciclos, grados, secciones, asignaturas, inscripciones, asignaciones y períodos. Para asociar una cuenta a una persona, cree primero la cuenta con el rol correspondiente. Para registrar notas se necesita asignación activa, estudiante inscrito en el mismo ciclo/grado/sección y período abierto dentro de sus fechas. Los profesores solamente pueden abrir sus propios grupos; estudiantes y encargados únicamente ven expedientes vinculados a su usuario.

Los informes admiten filtros por identificadores de ciclo, grado, sección, alumno o asignatura, según corresponda. La escala de calificaciones se configura mediante `GRADE_MIN` y `GRADE_MAX` (por defecto 0–100). No hay eliminación física de registros académicos: el campo «Activo» sirve para conservar el historial. Las operaciones de escritura y los accesos quedan en `audit_logs`.

El cambio de contraseña funciona con sesión iniciada. La recuperación por correo necesita transporte real (`MAIL_MAILER` y sus parámetros); el transporte de registro no envía mensajes al usuario. Configure copias de seguridad periódicas de MySQL fuera de esta aplicación.

## Verificación sin pruebas

Desde `app/`: `php artisan route:list`, `php artisan view:cache`, `php artisan migrate:status` y `php -l` sobre los archivos PHP relevantes. `migrate:status` confirma las cuatro migraciones aplicadas en MySQL; las rutas y vistas compilan. En esta instancia, `GET /ingresar` y `GET /up` respondieron 200; `GET /panel` sin autenticación respondió 302 hacia `/ingresar`. El primer intento de `/ingresar` agotó el tiempo durante el arranque inicial, pero el siguiente respondió 200. El inicio de sesión y las operaciones de la interfaz siguen sin verificación funcional. Por instrucción del usuario no se ejecutan ni se crean pruebas y no se inicializa Git.
