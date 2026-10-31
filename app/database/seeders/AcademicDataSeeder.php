<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/** Repeatable institutional presentation data. Never runs through the default seeder. */
class AcademicDataSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local')) {
            throw new RuntimeException('Los datos de presentación solo se permiten en el entorno local.');
        }

        DB::transaction(function () {
            $timestamp = now();
            $password = Hash::make('Colegio2026!');
            // Existing rows are preserved, including passwords and manually edited records.
            $record = function (string $table, array $key, array $values) use ($timestamp): int {
                $existing = DB::table($table)->where($key)->value('id');
                return $existing ?? DB::table($table)->insertGetId(array_merge($key, $values, [
                    'created_at' => $timestamp, 'updated_at' => $timestamp,
                ]));
            };
            $account = fn (string $username, string $name, string $role) => $record('users', ['username' => $username], [
                'name' => $name, 'email' => $username . '@generacionmanana.edu.gt', 'password' => $password,
                'role' => $role, 'active' => true, 'email_verified_at' => $timestamp,
            ]);

            $cycle = $record('school_years', ['year' => 2026], [
                'start_date' => '2026-01-12', 'end_date' => '2026-10-30', 'active' => true,
            ]);
            $periods = [];
            foreach ([['2026-01-12', '2026-03-20'], ['2026-03-23', '2026-05-29'], ['2026-06-01', '2026-08-07'], ['2026-08-10', '2026-10-30']] as $i => [$start, $end]) {
                $periods[] = $record('periods', ['school_year_id' => $cycle, 'name' => 'Unidad ' . ($i + 1)], [
                    'start_date' => $start, 'end_date' => $end, 'percentage' => 25, 'open' => $i === 3,
                ]);
            }
            $relationships = [];
            foreach (['Madre' => 'Madre y contacto responsable del estudiante.', 'Padre' => 'Padre y contacto responsable del estudiante.', 'Tutor legal' => 'Persona responsable de la representación del estudiante.'] as $name => $description) {
                $relationships[] = $record('relationships', ['name' => $name], compact('description'));
            }
            $groups = [];
            foreach (['Primero', 'Segundo', 'Tercero'] as $i => $name) {
                $grade = $record('grades', ['name' => $name, 'level' => 'Básico'], [
                    'description' => 'Formación integral de ' . strtolower($name) . ' básico.', 'active' => true,
                ]);
                foreach (['A', 'B'] as $section) {
                    $groups[] = ['grade' => $grade, 'section' => $record('sections', ['grade_id' => $grade, 'name' => $section], ['capacity' => 25, 'active' => true])];
                }
            }
            $teachers = [
                ['Lucía Isabel', 'Méndez Castillo', 'MAT', 'Matemática', 'Razonamiento lógico, álgebra y geometría.'],
                ['Carlos Eduardo', 'Ramírez Soto', 'COM', 'Comunicación y Lenguaje', 'Lectura crítica, escritura y expresión oral.'],
                ['María Fernanda', 'López Aguilar', 'NAT', 'Ciencias Naturales', 'Biología, ambiente y experimentación científica.'],
                ['Diego Alejandro', 'Morales Díaz', 'SOC', 'Ciencias Sociales', 'Historia, geografía y formación ciudadana.'],
                ['Ana Patricia', 'Herrera Flores', 'ING', 'Inglés', 'Comprensión y comunicación en inglés.'],
                ['José Antonio', 'Castillo Reyes', 'TEC', 'Tecnología', 'Competencias digitales y proyectos tecnológicos.'],
            ];
            $assignments = [];
            foreach ($teachers as $i => [$first, $last, $code, $subject, $description]) {
                $user = $account('docente.' . ($i + 1), "$first $last", 'teacher');
                $teacher = $record('teachers', ['code' => sprintf('DOC-%03d', $i + 1)], [
                    'first_name' => $first, 'last_name' => $last, 'phone' => sprintf('0000-%04d', 1000 + $i),
                    'email' => 'docente.' . ($i + 1) . '@generacionmanana.edu.gt', 'address' => 'Avenida de Ejemplo, casa ' . ($i + 1) . ', Ciudad Modelo', 'user_id' => $user, 'active' => true,
                ]);
                $subjectId = $record('subjects', ['code' => $code], ['name' => $subject, 'description' => $description, 'active' => true]);
                foreach ($groups as $g => $group) {
                    $assignments[$g][] = $record('assignments', ['subject_id' => $subjectId, 'school_year_id' => $cycle, 'section_id' => $group['section']], [
                        'teacher_id' => $teacher, 'grade_id' => $group['grade'], 'active' => true,
                    ]);
                }
            }
            $names = ['Sofía Valentina', 'Mateo Andrés', 'Isabella Lucía', 'Santiago Daniel', 'Camila Alejandra', 'Sebastián David', 'Valeria Isabel', 'Nicolás Gabriel', 'Mariana Fernanda', 'Emiliano José', 'Gabriela Sofía', 'Diego Esteban', 'Daniela Victoria', 'Samuel Alejandro', 'Natalia Andrea', 'Adrián Fernando'];
            $surnames = ['García', 'Hernández', 'Pérez', 'López', 'Martínez', 'González', 'Ramírez', 'Morales', 'Castillo', 'Vásquez', 'Méndez', 'Aguilar', 'Reyes', 'Herrera', 'Flores', 'Soto', 'Cruz', 'Ortiz', 'Díaz', 'Romero', 'Torres', 'Cabrera', 'Ramos', 'Fuentes'];
            $parents = ['Claudia Elena', 'Roberto Carlos', 'Verónica Isabel', 'Luis Fernando', 'Mónica Patricia', 'Jorge Alberto', 'Silvia Beatriz', 'Óscar Eduardo'];
            $guardians = [];
            for ($family = 0; $family < 24; $family++) {
                $first = $parents[$family % count($parents)];
                $last = $surnames[$family] . ' ' . $surnames[($family + 7) % 24];
                $user = $account('encargado.' . ($family + 1), "$first $last", 'guardian');
                $guardians[$family] = $record('guardians', ['user_id' => $user], [
                    'first_name' => $first, 'last_name' => $last, 'phone' => sprintf('0000-%04d', 2000 + $family),
                    'email' => 'encargado.' . ($family + 1) . '@generacionmanana.edu.gt', 'address' => 'Residencial de Ejemplo, casa ' . ($family + 1) . ', Ciudad Modelo', 'active' => true,
                ]);
            }
            for ($i = 0; $i < 48; $i++) {
                $family = $i % 24;
                $g = intdiv($i, 8);
                $first = $names[$i % 16];
                $last = $surnames[$family] . ' ' . $surnames[($family + 7) % 24];
                $user = $account('alumno.' . ($i + 1), "$first $last", 'student');
                $student = $record('students', ['code' => sprintf('EST-%03d', $i + 1)], [
                    'first_name' => $first, 'last_name' => $last,
                    'birth_date' => sprintf('%d-%02d-%02d', 2013 - intdiv($g, 2), 1 + $i % 12, 1 + $i % 27),
                    'sex' => $i % 2 === 0 ? 'Femenino' : 'Masculino',
                    'address' => 'Residencial de Ejemplo, casa ' . ($family + 1) . ', Ciudad Modelo',
                    'phone' => sprintf('0000-%04d', 2000 + $family), 'email' => 'alumno.' . ($i + 1) . '@generacionmanana.edu.gt',
                    'user_id' => $user, 'active' => true,
                ]);
                $record('student_guardians', ['student_id' => $student, 'guardian_id' => $guardians[$family]], [
                    'relationship_id' => $relationships[$family % 3], 'primary' => true,
                ]);
                $hadEnrollment = DB::table('enrollments')->where('student_id', $student)->where('school_year_id', $cycle)->exists();
                $enrollment = $record('enrollments', ['student_id' => $student, 'school_year_id' => $cycle], [
                    'grade_id' => $groups[$g]['grade'], 'section_id' => $groups[$g]['section'],
                    'enrolled_at' => sprintf('2026-01-%02d', 5 + $i % 7), 'active' => true,
                ]);
                foreach ($assignments[$g] as $s => $assignment) {
                    if (!$hadEnrollment) {
                        $record('course_enrollments', ['enrollment_id' => $enrollment, 'assignment_id' => $assignment], []);
                    }
                    // Re-running the example data must preserve individual course choices.
                    if (!DB::table('course_enrollments')->where('enrollment_id', $enrollment)->where('assignment_id', $assignment)->exists()) continue;
                    foreach ($periods as $p => $period) {
                        $score = 62 + (($i * 7 + $s * 11 + $p * 3) % 39);
                        $record('marks', ['enrollment_id' => $enrollment, 'assignment_id' => $assignment, 'period_id' => $period], [
                            'score' => $score, 'observation' => $score >= 90 ? 'Dominio destacado de los contenidos y participación constante.' : ($score >= 75 ? 'Buen desempeño; continuar con la práctica y los proyectos.' : 'Reforzar contenidos con ejercicios y acompañamiento semanal.'),
                        ]);
                    }
                }
            }
        });
        $this->command?->info('Datos institucionales cargados. Contraseña inicial: Colegio2026!');
    }
}
