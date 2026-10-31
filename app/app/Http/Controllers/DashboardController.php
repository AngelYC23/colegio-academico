<?php

namespace App\Http\Controllers;

use App\Support\RelatedLabels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function home()
    {
        $stats = Auth::user()->role === 'admin' ? [
            'Estudiantes' => DB::table('students')->count(), 'Profesores' => DB::table('teachers')->count(),
            'Inscripciones activas' => DB::table('enrollments')->where('active', true)->count(),
            'Grados' => DB::table('grades')->count(), 'Secciones' => DB::table('sections')->count(),
            'Usuarios activos' => DB::table('users')->where('active', true)->count(),
        ] : [];
        $recent = Auth::user()->role === 'admin' ? DB::table('audit_logs')->where('action', 'login')->orderByDesc('id')->limit(8)->get() : collect();
        $users = RelatedLabels::forIds('users', $recent->pluck('user_id'));
        return view('dashboard', compact('stats', 'recent', 'users'));
    }

    public function personal()
    {
        $role = Auth::user()->role;
        abort_unless(in_array($role, ['student', 'guardian']), 403);
        $studentIds = $role === 'student'
            ? DB::table('students')->where('user_id', Auth::id())->pluck('id')
            : DB::table('student_guardians')->whereIn('guardian_id', DB::table('guardians')->where('user_id', Auth::id())->select('id'))->pluck('student_id');
        $enrollments = DB::table('enrollments')->join('students', 'students.id', '=', 'enrollments.student_id')->join('school_years', 'school_years.id', '=', 'enrollments.school_year_id')->join('grades', 'grades.id', '=', 'enrollments.grade_id')->join('sections', 'sections.id', '=', 'enrollments.section_id')->whereIn('enrollments.student_id', $studentIds)->select('enrollments.id', 'students.first_name', 'students.last_name', 'students.code', 'school_years.year', 'grades.name as grade', 'grades.level', 'sections.name as section')->orderByDesc('school_years.year')->get();
        $marks = DB::table('marks')->join('enrollments', 'enrollments.id', '=', 'marks.enrollment_id')->join('students', 'students.id', '=', 'enrollments.student_id')->join('school_years', 'school_years.id', '=', 'enrollments.school_year_id')->join('assignments', 'assignments.id', '=', 'marks.assignment_id')->join('subjects', 'subjects.id', '=', 'assignments.subject_id')->join('periods', 'periods.id', '=', 'marks.period_id')->whereIn('enrollments.student_id', $studentIds)->select('students.first_name', 'students.last_name', 'students.code', 'school_years.year', 'subjects.name as subject', 'periods.name as period', 'marks.score')->get();
        $courses = DB::table('course_enrollments as ce')->join('enrollments as e', 'e.id', '=', 'ce.enrollment_id')
            ->join('students as st', 'st.id', '=', 'e.student_id')->join('assignments as a', 'a.id', '=', 'ce.assignment_id')
            ->join('subjects as s', 's.id', '=', 'a.subject_id')->join('teachers as t', 't.id', '=', 'a.teacher_id')
            ->join('school_years as y', 'y.id', '=', 'e.school_year_id')->whereIn('e.student_id', $studentIds)
            ->select('st.first_name', 'st.last_name', 'st.code', 'y.year', 's.name as subject', 't.first_name as teacher_first_name', 't.last_name as teacher_last_name', 'a.active')
            ->orderByDesc('y.year')->orderBy('st.last_name')->orderBy('s.name')->get();
        return view('personal', compact('enrollments', 'marks', 'courses'));
    }

    public function report(string $name)
    {
        abort_unless(Auth::user()->role === 'admin', 403);
        abort_unless(in_array($name, ['enrollments', 'teachers', 'assignments', 'marks']), 404);
        $cycle = request()->integer('cycle');
        $grade = request()->integer('grade');
        $section = request()->integer('section');
        $student = request()->integer('student');
        $subject = request()->integer('subject');
        $rows = match ($name) {
            'enrollments' => DB::table('enrollments')->join('students', 'students.id', '=', 'enrollments.student_id')->join('school_years', 'school_years.id', '=', 'enrollments.school_year_id')->join('grades', 'grades.id', '=', 'enrollments.grade_id')->join('sections', 'sections.id', '=', 'enrollments.section_id')->when($cycle, fn ($q) => $q->where('enrollments.school_year_id', $cycle))->when($grade, fn ($q) => $q->where('enrollments.grade_id', $grade))->when($section, fn ($q) => $q->where('enrollments.section_id', $section))->when($student, fn ($q) => $q->where('enrollments.student_id', $student))->select('students.code', 'students.first_name', 'students.last_name', 'school_years.year', 'grades.name as grade', 'grades.level', 'sections.name as section')->paginate(30),
            'teachers' => DB::table('teachers')->select('code', 'first_name', 'last_name', 'email', 'phone')->paginate(30),
            'assignments' => DB::table('assignments')->join('teachers', 'teachers.id', '=', 'assignments.teacher_id')->join('subjects', 'subjects.id', '=', 'assignments.subject_id')->join('school_years', 'school_years.id', '=', 'assignments.school_year_id')->leftJoin('grades', 'grades.id', '=', 'assignments.grade_id')->leftJoin('sections', 'sections.id', '=', 'assignments.section_id')->when($cycle, fn ($q) => $q->where('assignments.school_year_id', $cycle))->when($grade, fn ($q) => $q->where('assignments.grade_id', $grade))->when($section, fn ($q) => $q->where('assignments.section_id', $section))->when($subject, fn ($q) => $q->where('assignments.subject_id', $subject))->select('teachers.code', 'teachers.first_name', 'teachers.last_name', 'subjects.code as subject_code', 'subjects.name as subject', 'school_years.year', 'grades.name as grade', 'grades.level', 'sections.name as section')->paginate(30),
            'marks' => DB::table('marks')->join('enrollments', 'enrollments.id', '=', 'marks.enrollment_id')->join('students', 'students.id', '=', 'enrollments.student_id')->join('assignments', 'assignments.id', '=', 'marks.assignment_id')->join('subjects', 'subjects.id', '=', 'assignments.subject_id')->join('periods', 'periods.id', '=', 'marks.period_id')->join('school_years', 'school_years.id', '=', 'assignments.school_year_id')->when($student, fn ($q) => $q->where('enrollments.student_id', $student))->when($subject, fn ($q) => $q->where('assignments.subject_id', $subject))->when($cycle, fn ($q) => $q->where('assignments.school_year_id', $cycle))->select('students.code', 'students.first_name', 'students.last_name', 'subjects.code as subject_code', 'subjects.name as subject', 'school_years.year', 'periods.name as period', 'marks.score')->paginate(30),
        };
        $filters = [];
        if ($name !== 'teachers') {
            foreach (['cycle' => 'school_years', 'grade' => 'grades', 'section' => 'sections', 'student' => 'students', 'subject' => 'subjects'] as $key => $table) {
                $filters[$key] = RelatedLabels::options($table);
            }
        }
        return view('report', compact('rows', 'name', 'filters'));
    }
}
