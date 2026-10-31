# Ciclos escolares y búsqueda

## Encontrar personas y cursos

En Estudiantes, Profesores, Encargados y los demás listados hay un buscador. Admite palabras del nombre, apellidos, código, correo y los otros campos del registro. Las palabras se combinan: `sofia garcia` encuentra el perfil aunque se escriba `Sofía García`. No distingue mayúsculas ni las tildes habituales del español. Los símbolos `%` y `_` se buscan literalmente.

En los módulos relacionados con el año hay un selector de **Ciclo escolar**. Selecciona 2025, 2026 o 2027 y pulsa **Buscar**. La paginación conserva los filtros. **Limpiar** vuelve a mostrar todo.

- **Estudiantes → Cursos** abre la inscripción del ciclo filtrado; el selector de la ficha permite cambiar a otro año. Despliega **Datos de contacto y del perfil** para ver el resto de la información.
- **Profesores → Cursos y alumnos** conserva el ciclo seleccionado. Allí también se puede buscar por curso, grado o sección.
- **Calificaciones** permite filtrar los cursos por ciclo y texto. Los ciclos cerrados se consultan sin editar notas.
- Las listas de alumnos de cada curso tienen su propio buscador.
- Usuarios y parentescos son registros generales: se buscan por texto, sin un ciclo propio.

## Datos opcionales de ejemplo para el taller

Desde `app/`, después de actualizar el código y aplicar las migraciones:

```sh
php artisan db:seed --class=AdditionalSchoolYearsSeeder
php artisan optimize:clear
```

El seeder solo admite `APP_ENV=local`. Crea, sin reemplazar lo existente:

- **2025**, cerrado, con cuatro períodos cerrados e historial de ejemplo.
- **2027**, activo para planificar, con cuatro períodos futuros cerrados y sin notas inventadas para el futuro.

Si existen los datos de presentación de 2026, añade ofertas docentes e inscripciones únicamente para los 48 perfiles de ejemplo `EST-001` a `EST-048`. No promueve ni inscribe automáticamente a alumnos reales o perfiles nuevos. Un alumno creado manualmente se inscribe en el nuevo ciclo desde el aplicativo.

La progresión ficticia es: Sexto Primaria → Primero Básico → Segundo Básico, o Primero → Segundo → Tercero Básico, o Segundo → Tercero Básico → Cuarto Diversificado, según el grupo original. El historial 2025 recibe calificaciones de ejemplo; para 2027 se usan selecciones individuales de cuatro a seis cursos, sin calificaciones.

Repetir el seeder no duplica inscripciones ni restablece selecciones de cursos, notas o datos editados. Los estados, fechas y períodos de ciclos ya configurados manualmente se conservan. Si un ciclo existente no tiene períodos, se crean cuatro dentro de sus fechas, sin modificar el ciclo. Si todavía no hay datos de presentación en una instalación local y se desean ejemplos completos, se pueden cargar primero con `php artisan db:seed --class=AcademicDataSeeder`; sus cuentas de práctica se documentan en ACCESO.md.

No uses `migrate:fresh` ni borres la base para actualizar. Las fechas de los ejemplos no son el calendario oficial de una institución. Cuando comience 2027, un administrador deberá abrir los períodos en las fechas que correspondan para permitir registrar notas.
