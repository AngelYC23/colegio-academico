<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::table('marks as m')->join('enrollments as e', 'e.id', '=', 'm.enrollment_id')
            ->join('assignments as a', 'a.id', '=', 'm.assignment_id')->where(function ($query) {
                $query->whereColumn('e.school_year_id', '<>', 'a.school_year_id')
                    ->orWhereColumn('e.grade_id', '<>', 'a.grade_id')->orWhereColumn('e.section_id', '<>', 'a.section_id');
            })->exists()) {
            throw new RuntimeException('Hay notas de grupos incompatibles. Revise su integridad antes de asignar cursos individuales.');
        }
        // Existing students previously inherited the courses of their group.
        // Preserve those courses explicitly before enabling individual selection.
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->restrictOnDelete();
            $table->foreignId('assignment_id')->constrained()->restrictOnDelete();
            $table->unique(['enrollment_id', 'assignment_id']);
            $table->index('assignment_id');
            $table->timestamps();
        });
        DB::table('enrollments as e')->join('assignments as a', function ($join) {
            $join->on('a.school_year_id', '=', 'e.school_year_id')
                ->on('a.grade_id', '=', 'e.grade_id')->on('a.section_id', '=', 'e.section_id');
        })->select('e.id as enrollment_id', 'a.id as assignment_id')->orderBy('e.id')->orderBy('a.id')
            ->chunk(500, function ($rows) {
                $now = now();
                DB::table('course_enrollments')->insert($rows->map(fn ($row) => [
                    'enrollment_id' => $row->enrollment_id, 'assignment_id' => $row->assignment_id,
                    'created_at' => $now, 'updated_at' => $now,
                ])->all());
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
    }
};
