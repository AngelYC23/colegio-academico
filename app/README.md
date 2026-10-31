# La Generación del Mañana

Aplicación Laravel para administrar estudiantes, profesores, encargados, estructura académica, inscripciones, asignaciones y calificaciones. Incluye informes y acceso por rol. La configuración del Mac usa SQLite y PHP de XAMPP; Docker y MySQL siguen disponibles como alternativa en el README de la raíz.

## Trabajar en este Mac

Desde esta carpeta (`app/`):

```sh
./iniciar.command
```

Abre http://127.0.0.1:8088/ingresar. Mantén la terminal abierta; detén con Control + C. Si el puerto ya está ocupado por esta aplicación, usa la instancia que está abierta. El script no elige otro puerto automáticamente.

La base local está en `database/database.sqlite`. El código se edita en `app/Http/Controllers`, `resources/views` y `public/css/academic.css`. El logo está en `public/images/colegio-logo.png`. Las modificaciones se ven al recargar; el diseño principal no necesita ejecutar Vite.

## Preparar otra instalación

Requisitos: PHP 8.3, Composer y las extensiones requeridas por Laravel, incluido PDO SQLite; se recomienda PHP 8.3 porque una herramienta de desarrollo del archivo de dependencias lo exige. PHP 8.2 permite ejecutar la aplicación con `composer install --no-dev`.

```sh
composer install
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
php artisan academic:create-admin
```

La configuración de ejemplo usa español, almacenamiento de fechas en UTC y fechas escolares en `America/Guatemala`. Los correos se guardan en el registro local: la recuperación por correo requiere configurar un servicio de envío real.

Para cargar opcionalmente los datos institucionales de presentación y las cuentas de acceso, consulta [ACCESO.md](ACCESO.md). El seeder no reemplaza registros existentes y solo funciona en entorno local. La base de datos y `.env` no se incluyen en Git.

## Recorrido para presentar

1. Entrar con la cuenta administradora y mostrar el resumen.
2. Consultar estudiantes, profesores y encargados.
3. Revisar ciclos, grados, secciones, asignaturas y períodos en Configuración.
4. Mostrar inscripciones y asignaciones docentes.
5. Abrir un grupo de calificaciones y consultar los informes filtrados.
6. Usar las cuentas de práctica de profesor, alumno y encargado para ver el acceso de cada rol.

Los períodos cerrados conservan sus notas. El conjunto de ejemplo corresponde al ciclo 2026; para registrar notas en otra fecha, configura un ciclo y un período vigentes. No se permite alterar el grupo de una inscripción o asignación que ya tenga notas.

El alcance definitivo del taller debe cotejarse con el enunciado del docente. La implementación actual no incluye pagos ni módulos administrativos ajenos a la gestión académica.

## Cursos por estudiante

Consulta [CURSOS.md](CURSOS.md) para la selección individual de cursos, las listas de alumnos de cada profesor y la actualización de la base de datos.

## Ciclos 2025/2027 y buscador

Consulta [CICLOS_Y_BUSQUEDA.md](CICLOS_Y_BUSQUEDA.md) para usar los filtros y cargar opcionalmente los nuevos ciclos de ejemplo sin borrar datos existentes.
