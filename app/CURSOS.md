# Cursos individuales y profesores

## Recorrido del administrador

1. En **Estudiantes**, pulsa **Cursos** junto al alumno.
2. Selecciona su inscripción/ciclo y pulsa **Consultar**.
3. Marca las asignaturas que llevará y pulsa **Guardar cursos del estudiante**. Dos alumnos del mismo grado/sección pueden llevar cursos distintos.
4. Se ofrecen las asignaciones docentes activas del ciclo, grado y sección del alumno. Para ofrecer una asignatura, usa **Registrar una nueva oferta de curso** y selecciona el profesor.
5. En **Profesores → Cursos y alumnos**, consulta las asignaturas, ciclos, grupos y número de alumnos. **Asignar curso al profesor** abre el formulario con el profesor seleccionado; **Ver alumnos** muestra solo sus inscritos.

Crear una inscripción dirige inmediatamente a la selección de cursos. Los alumnos nuevos no reciben cursos automáticamente. Un curso sin notas se puede retirar desmarcándolo; uno con notas se conserva para mantener su historial.

## Otros roles

- El profesor ve **Mis cursos y alumnos** y solo sus propias listas y planillas.
- El estudiante ve sus cursos y docentes en **Mi expediente**, aunque todavía no tenga calificaciones.
- El encargado ve los cursos de los estudiantes vinculados a su cuenta.
- La planilla y el guardado de notas exigen inscripción individual en el curso.

## Actualizar una instalación existente

Desde `app/`, después de actualizar el código:

```sh
php artisan migrate
php artisan optimize:clear
```

La nueva tabla `course_enrollments` relaciona cada inscripción con sus cursos. La migración conserva explícitamente las asignaturas que antes se heredaban del grupo y mantiene todas las notas. No uses `migrate:fresh`: no hace falta borrar datos.

Los cambios de selección quedan en la auditoría. Ejecutar de nuevo `AcademicDataSeeder` no restablece los cursos retirados. Las reglas del servidor rechazan cursos de otro ciclo/grado/sección, duplicados, ofertas inactivas y modificaciones no autorizadas.

## Confirmación de contraseñas

Alta de cuentas, cambio y restablecimiento exigen coincidencia exacta entre contraseña y confirmación. El navegador muestra un aviso inmediato y el servidor verifica la igualdad aunque JavaScript esté desactivado. En la edición de una cuenta, dejar ambos campos vacíos conserva la contraseña existente; rellenar solo la confirmación se rechaza.
