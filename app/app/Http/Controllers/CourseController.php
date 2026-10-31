<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use App\Support\RecordSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    public function student(Request $request, int $student)
    {
        $this->admin();
        $person = DB::table('students')->find($student);
        abort_unless($person, 404);
        $enrollments = DB::table('enrollments as e')->join('school_years as y', 'y.id', '=', 'e.school_year_id')
            ->join('grades as g', 'g.id', '=', 'e.grade_id')->join('sections as s', 's.id', '=', 'e.section_id')
            ->where('e.student_id', $student)->orderByDesc('y.year')
            ->select('e.*', 'y.year', 'y.active as cycle_active', 'g.name as grade', 'g.level', 's.name as section', 'g.active as grade_active', 's.active as section_active')->get();
        $request->validate(['enrollment' => 'nullable|integer|min:1', 'cycle' => 'nullable|integer|min:1|exists:school_years,id']);
        $enrollment = $request->filled('enrollment')
            ? $enrollments->firstWhere('id', $request->integer('enrollment'))
            : ($request->filled('cycle') ? $enrollments->firstWhere('school_year_id', $request->integer('cycle')) : $enrollments->first());
        if ($request->filled('enrollment') || $request->filled('cycle')) abort_unless($enrollment, 404);
        if ($request->filled('cycle') && $enrollment) abort_unless((int) $enrollment->school_year_id === $request->integer('cycle'), 404);
        $selected = $enrollment ? DB::table('course_enrollments')->where('enrollment_id', $enrollment->id)->pluck('assignment_id')->all() : [];
        $locked = $enrollment ? DB::table('marks')->where('enrollment_id', $enrollment->id)->distinct()->pluck('assignment_id')->all() : [];
        $courses = $enrollment ? $this->offerings($enrollment)->where(function ($q) use ($selected) {
            $q->where(function ($available) {
                $available->where('a.active', true)->where('t.active', true)->where('s.active', true);
            })->orWhereIn('a.id', $selected);
        })->orderBy('s.name')->get() : collect();
        $canEdit = $enrollment && $person->active && $enrollment->active && $enrollment->cycle_active
            && $enrollment->grade_active && $enrollment->section_active;
        return view('courses.student', compact('person', 'enrollments', 'enrollment', 'selected', 'locked', 'courses', 'canEdit'));
    }

    public function saveStudent(Request $request, int $student)
    {
        $this->admin();
        abort_unless(DB::table('students')->where('id', $student)->exists(), 404);
        $data = $request->validate([
            'enrollment_id' => 'required|integer|min:1|exists:enrollments,id',
            'assignment_ids' => 'sometimes|array|max:200',
            'assignment_ids.*' => 'required|integer|min:1|distinct|exists:assignments,id',
        ]);
        $selected = array_map('intval', $data['assignment_ids'] ?? []);
        DB::transaction(function () use ($data, $selected, $student, $request) {
            // Grade saving and course selection serialize on this same row.
            $enrollment = DB::table('enrollments')->where('id', $data['enrollment_id'])->where('student_id', $student)->lockForUpdate()->first();
            abort_unless($enrollment, 404);
            if (!$enrollment->active || !DB::table('students')->where('id', $student)->where('active', true)->exists()
                || !DB::table('school_years')->where('id', $enrollment->school_year_id)->where('active', true)->exists()
                || !DB::table('grades')->where('id', $enrollment->grade_id)->where('active', true)->exists()
                || !DB::table('sections')->where('id', $enrollment->section_id)->where('active', true)->exists()) {
                throw ValidationException::withMessages(['assignment_ids' => 'La inscripción, el estudiante y su ciclo, grado y sección deben estar activos.']);
            }
            $previous = DB::table('course_enrollments')->where('enrollment_id', $enrollment->id)->pluck('assignment_id')->map(fn ($id) => (int) $id)->all();
            $added = array_values(array_diff($selected, $previous));
            $removed = array_values(array_diff($previous, $selected));
            DB::table('assignments')->whereIn('id', $added)->orderBy('id')->lockForUpdate()->get();
            $eligible = $this->offerings($enrollment)->where('a.active', true)->where('t.active', true)->where('s.active', true)
                ->whereIn('a.id', $added)->pluck('a.id')->map(fn ($id) => (int) $id)->all();
            if (array_diff($added, $eligible)) {
                throw ValidationException::withMessages(['assignment_ids' => 'Seleccione cursos activos del ciclo, grado y sección de este estudiante.']);
            }
            if (DB::table('marks')->where('enrollment_id', $enrollment->id)->whereIn('assignment_id', $removed)->exists()) {
                throw ValidationException::withMessages(['assignment_ids' => 'No se puede retirar un curso que ya tiene calificaciones.']);
            }
            if ($removed) DB::table('course_enrollments')->where('enrollment_id', $enrollment->id)->whereIn('assignment_id', $removed)->delete();
            foreach ($added as $assignment) {
                DB::table('course_enrollments')->insert(['enrollment_id' => $enrollment->id, 'assignment_id' => $assignment, 'created_at' => now(), 'updated_at' => now()]);
            }
            if ($added || $removed) DB::table('audit_logs')->insert([
                'user_id' => Auth::id(), 'action' => 'courses_updated', 'entity' => 'enrollments', 'entity_id' => $enrollment->id,
                'ip_address' => $request->ip(), 'changes' => json_encode(['added' => $added, 'removed' => $removed]), 'created_at' => now(),
            ]);
        });
        return redirect()->route('courses.student', ['student' => $student, 'enrollment' => $data['enrollment_id']])
            ->with('status', 'Cursos del estudiante guardados correctamente.');
    }

    public function teacher(?int $teacher = null)
    {
        if ($teacher === null) {
            abort_unless(Auth::user()->role === 'teacher', 403);
            $teacher = DB::table('teachers')->where('user_id', Auth::id())->value('id');
        }
        $person = DB::table('teachers')->find($teacher);
        abort_unless($person, 404);
        abort_unless(Auth::user()->role === 'admin' || (Auth::user()->role === 'teacher' && (int) $person->user_id === (int) Auth::id()), 403);
        [$searchQuery, $cycleId] = RecordSearch::filters(request());
        $cycles = RecordSearch::cycles();
        $query = DB::table('assignments as a')->join('subjects as s', 's.id', '=', 'a.subject_id')
            ->join('school_years as y', 'y.id', '=', 'a.school_year_id')->join('grades as g', 'g.id', '=', 'a.grade_id')
            ->join('sections as sec', 'sec.id', '=', 'a.section_id')->where('a.teacher_id', $teacher)
            ->select('a.*', 's.name as subject', 's.code', 'y.year', 'y.active as cycle_active', 'g.name as grade', 'g.level', 'sec.name as section')
            ->selectSub(DB::table('course_enrollments as ce')->join('enrollments as e', 'e.id', '=', 'ce.enrollment_id')
                ->join('students as st', 'st.id', '=', 'e.student_id')->whereColumn('ce.assignment_id', 'a.id')->where('e.active', true)->where('st.active', true)
                ->selectRaw('COUNT(*)'), 'student_count')->orderByDesc('y.year')->orderBy('s.name');
        if ($cycleId) $query->where('a.school_year_id', $cycleId);
        RecordSearch::apply($query, ['s.name', 's.code', 'g.name', 'g.level', 'sec.name', 'y.year'], $searchQuery);
        $courses = $query->get();
        return view('courses.teacher', compact('person', 'courses', 'searchQuery', 'cycleId', 'cycles'));
    }

    public function roster(int $assignment)
    {
        $course = DB::table('assignments as a')->join('subjects as s', 's.id', '=', 'a.subject_id')
            ->join('teachers as t', 't.id', '=', 'a.teacher_id')->join('school_years as y', 'y.id', '=', 'a.school_year_id')
            ->join('grades as g', 'g.id', '=', 'a.grade_id')->join('sections as sec', 'sec.id', '=', 'a.section_id')
            ->where('a.id', $assignment)->select('a.*', 's.name as subject', 't.user_id as teacher_user_id', 't.first_name', 't.last_name', 'y.year', 'g.name as grade', 'g.level', 'sec.name as section')->first();
        abort_unless($course, 404);
        abort_unless(Auth::user()->role === 'admin' || (Auth::user()->role === 'teacher' && (int) $course->teacher_user_id === (int) Auth::id()), 403);
        [$searchQuery] = RecordSearch::filters(request());
        $query = DB::table('course_enrollments as ce')->join('enrollments as e', 'e.id', '=', 'ce.enrollment_id')
            ->join('students as st', 'st.id', '=', 'e.student_id')->where('ce.assignment_id', $assignment)
            ->where('e.active', true)->where('st.active', true)->orderBy('st.last_name')->orderBy('st.first_name')
            ->select('st.id', 'st.code', 'st.first_name', 'st.last_name');
        RecordSearch::apply($query, ['st.code', 'st.first_name', 'st.last_name'], $searchQuery);
        $students = $query->get();
        return view('courses.roster', compact('course', 'students', 'searchQuery'));
    }

    private function offerings(object $enrollment): Builder
    {
        return DB::table('assignments as a')->join('subjects as s', 's.id', '=', 'a.subject_id')->join('teachers as t', 't.id', '=', 'a.teacher_id')
            ->where('a.school_year_id', $enrollment->school_year_id)->where('a.grade_id', $enrollment->grade_id)->where('a.section_id', $enrollment->section_id)
            ->select('a.id', 'a.active', 's.name as subject', 's.code', 's.active as subject_active', 't.first_name', 't.last_name', 't.active as teacher_active');
    }

    private function admin(): void
    {
        abort_unless(Auth::user()?->active && Auth::user()->role === 'admin', 403);
    }
}
