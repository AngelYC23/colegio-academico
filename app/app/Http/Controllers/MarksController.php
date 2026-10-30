<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarksController extends Controller
{
    public function index()
    {
        abort_unless(in_array(Auth::user()->role, ['admin', 'teacher']), 403);
        $assignments = DB::table('assignments')->join('subjects', 'subjects.id', '=', 'assignments.subject_id')->join('sections', 'sections.id', '=', 'assignments.section_id')->join('grades', 'grades.id', '=', 'assignments.grade_id')->join('school_years', 'school_years.id', '=', 'assignments.school_year_id')->select('assignments.*', 'subjects.name as subject', 'sections.name as section', 'grades.name as grade', 'grades.level', 'school_years.year')->where('assignments.active', true)->when(Auth::user()->role === 'teacher', fn ($q) => $q->whereIn('assignments.teacher_id', DB::table('teachers')->where('user_id', Auth::id())->select('id')))->get();
        return view('marks.index', compact('assignments'));
    }

    public function group(int $assignment)
    {
        $group = $this->assignment($assignment);
        $periods = DB::table('periods')->where('school_year_id', $group->school_year_id)->get();
        $students = DB::table('enrollments')->join('students', 'students.id', '=', 'enrollments.student_id')->where('enrollments.school_year_id', $group->school_year_id)->where('enrollments.grade_id', $group->grade_id)->where('enrollments.section_id', $group->section_id)->where('enrollments.active', true)->select('enrollments.id', 'students.first_name', 'students.last_name', 'students.code')->get();
        $marks = DB::table('marks')->where('assignment_id', $assignment)->get()->keyBy(fn ($mark) => $mark->enrollment_id . '-' . $mark->period_id);
        return view('marks.group', compact('group', 'periods', 'students', 'marks'));
    }

    public function save(Request $request, int $assignment)
    {
        $group = $this->assignment($assignment);
        $data = $request->validate(['enrollment_id' => 'required|integer|exists:enrollments,id', 'period_id' => 'required|integer|exists:periods,id', 'score' => ['required', 'numeric', 'between:' . config('academic.min_score', 0) . ',' . config('academic.max_score', 100)], 'observation' => 'nullable|string|max:255']);
        $period = DB::table('periods')->where('id', $data['period_id'])->where('school_year_id', $group->school_year_id)->where('open', true)->whereDate('start_date', '<=', now(config('academic.timezone'))->toDateString())->whereDate('end_date', '>=', now(config('academic.timezone'))->toDateString())->first();
        if (!$period || !DB::table('school_years')->where('id', $group->school_year_id)->where('active', true)->exists()) throw ValidationException::withMessages(['period_id' => 'El período no está abierto para este ciclo.']);
        if (!DB::table('enrollments')->where('id', $data['enrollment_id'])->where('school_year_id', $group->school_year_id)->where('grade_id', $group->grade_id)->where('section_id', $group->section_id)->where('active', true)->exists()) throw ValidationException::withMessages(['enrollment_id' => 'El estudiante no está inscrito en este grupo.']);
        DB::transaction(function () use ($request, $assignment, $data) {
            DB::table('marks')->upsert([['enrollment_id' => $data['enrollment_id'], 'assignment_id' => $assignment, 'period_id' => $data['period_id'], 'score' => $data['score'], 'observation' => $data['observation'] ?? null, 'updated_at' => now(), 'created_at' => now()]], ['enrollment_id', 'assignment_id', 'period_id'], ['score', 'observation', 'updated_at']);
            DB::table('audit_logs')->insert(['user_id' => Auth::id(), 'action' => 'mark_saved', 'entity' => 'marks', 'entity_id' => $data['enrollment_id'], 'ip_address' => $request->ip(), 'changes' => json_encode(['assignment_id' => $assignment, 'period_id' => $data['period_id'], 'score' => $data['score']]), 'created_at' => now()]);
        });
        return back()->with('status', 'Calificación guardada.');
    }

    private function assignment(int $id): object
    {
        abort_unless(in_array(Auth::user()->role, ['admin', 'teacher']), 403);
        $group = DB::table('assignments')->find($id);
        abort_unless($group && $group->active, 404);
        if (Auth::user()->role === 'teacher') abort_unless(DB::table('teachers')->where('id', $group->teacher_id)->where('user_id', Auth::id())->exists(), 403);
        return $group;
    }
}
