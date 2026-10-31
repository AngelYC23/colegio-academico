<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Optional fictional history and next-year planning. Existing records are never reset. */
class AdditionalSchoolYearsSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local')) {
            throw new RuntimeException('Los ejemplos de ciclos solo se pueden cargar en un entorno local.');
        }
        DB::transaction(function () {
            $record = function (string $table, array $key, array $values): int {
                return DB::table($table)->where($key)->value('id')
                    ?? DB::table($table)->insertGetId(array_merge($key, $values, ['created_at' => now(), 'updated_at' => now()]));
            };
            $cycles = [];
            foreach ([2025 => false, 2027 => true] as $year => $active) {
                $newCycle = !DB::table('school_years')->where('year', $year)->exists();
                $cycles[$year] = $record('school_years', ['year' => $year], [
                    'start_date' => $year . '-01-12', 'end_date' => $year . '-10-30', 'active' => $active,
                ]);
                if (DB::table('periods')->where('school_year_id', $cycles[$year])->exists()) continue;
                $windows = [['01-12', '03-20'], ['03-23', '05-29'], ['06-01', '08-07'], ['08-10', '10-30']];
                if (!$newCycle) {
                    $existingCycle = DB::table('school_years')->find($cycles[$year]);
                    $begin = \Illuminate\Support\Carbon::parse($existingCycle->start_date)->startOfDay()->toImmutable();
                    $finish = \Illuminate\Support\Carbon::parse($existingCycle->end_date)->startOfDay()->toImmutable();
                    $days = (int) $begin->diffInDays($finish) + 1;
                    if ($days < 4) continue;
                    $windows = [];
                    for ($i = 0; $i < 4; $i++) {
                        $windows[] = [$begin->addDays(intdiv($days * $i, 4))->toDateString(), $begin->addDays(intdiv($days * ($i + 1), 4) - 1)->toDateString()];
                    }
                }
                foreach ($windows as $i => [$start, $end]) {
                    $record('periods', ['school_year_id' => $cycles[$year], 'name' => 'Unidad ' . ($i + 1)], [
                        'start_date' => $newCycle ? $year . '-' . $start : $start, 'end_date' => $newCycle ? $year . '-' . $end : $end,
                        'percentage' => 25, 'open' => false,
                    ]);
                }
            }
            $sourceCycle = DB::table('school_years')->where('year', 2026)->value('id');
            if (!$sourceCycle) return;
            // Promotion is explicit in the fictional cohort, not inferred for real students.
            $paths = [
                'Primero' => [2025 => ['Sexto', 'Primaria'], 2027 => ['Segundo', 'Básico']],
                'Segundo' => [2025 => ['Primero', 'Básico'], 2027 => ['Tercero', 'Básico']],
                'Tercero' => [2025 => ['Segundo', 'Básico'], 2027 => ['Cuarto', 'Diversificado']],
            ];
            foreach ($paths as $sourceGradeName => $destinations) {
                $sourceGrade = DB::table('grades')->where('name', $sourceGradeName)->where('level', 'Básico')->value('id');
                if (!$sourceGrade) continue;
                foreach ($destinations as $year => [$gradeName, $level]) {
                    $grade = $record('grades', ['name' => $gradeName, 'level' => $level], [
                        'description' => 'Grado del recorrido académico de ejemplo.', 'active' => true,
                    ]);
                    foreach (DB::table('sections')->where('grade_id', $sourceGrade)->get() as $sourceSection) {
                        $section = $record('sections', ['grade_id' => $grade, 'name' => $sourceSection->name], ['capacity' => 25, 'active' => true]);
                        $targetAssignments = [];
                        $sourceAssignments = DB::table('assignments')->where('school_year_id', $sourceCycle)
                            ->where('grade_id', $sourceGrade)->where('section_id', $sourceSection->id)->orderBy('subject_id')->get();
                        foreach ($sourceAssignments as $assignment) {
                            $targetAssignments[] = $record('assignments', [
                                'subject_id' => $assignment->subject_id, 'school_year_id' => $cycles[$year], 'section_id' => $section,
                            ], ['teacher_id' => $assignment->teacher_id, 'grade_id' => $grade, 'active' => true]);
                        }
                        $sourceEnrollments = DB::table('enrollments as e')->join('students as st', 'st.id', '=', 'e.student_id')
                            ->where('e.school_year_id', $sourceCycle)->where('e.grade_id', $sourceGrade)->where('e.section_id', $sourceSection->id)
                            ->select('e.*', 'st.code', 'st.birth_date', 'st.active as student_active')->get();
                        foreach ($sourceEnrollments as $source) {
                            // Only the 48 explicitly labelled presentation profiles are extended.
                            if (!preg_match('/\AEST-0(?:0[1-9]|[1-3][0-9]|4[0-8])\z/', $source->code)) continue;
                            if ($year === 2027 && (!$source->active || !$source->student_active)) continue;
                            if (DB::table('enrollments')->where('student_id', $source->student_id)->where('school_year_id', $cycles[$year])->exists()) continue;
                            $date = $year === 2025 ? '2025-01-06' : min(now(config('academic.timezone'))->toDateString(), '2027-01-05');
                            $cycleRow = DB::table('school_years')->find($cycles[$year]);
                            if (($year === 2027 && !$cycleRow->active) || $date > $cycleRow->end_date || ($source->birth_date && $date <= $source->birth_date)) continue;
                            // Do not overfill an existing section or change its configured capacity.
                            $capacity = DB::table('sections')->where('id', $section)->value('capacity');
                            if (DB::table('enrollments')->where('school_year_id', $cycles[$year])->where('section_id', $section)->where('active', true)->count() >= $capacity) continue;
                            $enrollment = $record('enrollments', ['student_id' => $source->student_id, 'school_year_id' => $cycles[$year]], [
                                'grade_id' => $grade, 'section_id' => $section, 'enrolled_at' => $date, 'active' => true,
                            ]);
                            $number = (int) substr($source->code, -3);
                            $chosen = $year === 2025 ? $targetAssignments : array_slice($targetAssignments, 0, 4 + ($number % 3));
                            foreach ($chosen as $assignmentId) {
                                $record('course_enrollments', ['enrollment_id' => $enrollment, 'assignment_id' => $assignmentId], []);
                                if ($year !== 2025) continue;
                                foreach (DB::table('periods')->where('school_year_id', $cycles[$year])->orderBy('id')->get() as $i => $period) {
                                    $record('marks', ['enrollment_id' => $enrollment, 'assignment_id' => $assignmentId, 'period_id' => $period->id], [
                                        'score' => 65 + (($number * 7 + $assignmentId + $i * 3) % 36),
                                        'observation' => 'Calificación ficticia del historial académico de 2025.',
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        });
        $this->command?->info('Ciclos 2025 y 2027 preparados. Los registros y selecciones existentes se conservaron.');
    }
}
