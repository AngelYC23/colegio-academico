# Cuentas de acceso para la presentación

La base local contiene personas y contactos ficticios, con correos del dominio reservado `.test` y teléfonos de ejemplo. Las cuentas de administrador existentes se conservan.

Para cargar este conjunto en una instalación local preparada:

```sh
php artisan db:seed --class=AcademicDataSeeder
```

El seeder se ejecuta en una transacción, solo permite `APP_ENV=local` y conserva los registros existentes al repetirlo. No se incluye en el seeder predeterminado. Las modificaciones manuales de registros existentes también se conservan.

Conjunto: ciclo 2026, cuatro unidades de 25 %, tres grados de básico, seis secciones de capacidad 25, seis asignaturas, seis docentes, 48 estudiantes, 24 encargados, 48 vínculos familiares, 48 inscripciones, 36 asignaciones y 1,152 calificaciones con observaciones. La cuarta unidad está abierta hasta el 30 de octubre de 2026; las tres anteriores están cerradas.

Cuentas para explorar cada rol:

| Rol | Usuario |
| --- | --- |
| Profesor | `docente.1` |
| Estudiante | `alumno.1` |
| Encargado | `encargado.1` |

Contraseña inicial de estas cuentas: `Colegio2026!`. El encargado del ejemplo tiene dos estudiantes vinculados. Las cuentas restantes siguen la numeración de cada grupo.

Estas cuentas están preparadas para recorrer los diferentes permisos del sistema durante la presentación académica.
