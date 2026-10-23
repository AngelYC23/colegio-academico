<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique();
            $table->enum('role', ['admin', 'teacher', 'student', 'guardian'])->index();
            $table->boolean('active')->default(true);
            $table->timestamp('last_login_at')->nullable();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('sex', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('relationships', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        Schema::create('student_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('guardian_id')->constrained()->restrictOnDelete();
            $table->foreignId('relationship_id')->constrained()->restrictOnDelete();
            $table->boolean('primary')->default(false);
            $table->unique(['student_id', 'guardian_id']);
            $table->timestamps();
        });
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('level');
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->unique(['name', 'level']);
            $table->timestamps();
        });
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('capacity');
            $table->boolean('active')->default(true);
            $table->unique(['grade_id', 'name']);
            $table->unique(['id', 'grade_id']);
            $table->timestamps();
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('open')->default(true);
            $table->unique(['school_year_id', 'name']);
            $table->unique(['id', 'school_year_id']);
            $table->timestamps();
        });
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('school_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('grade_id')->constrained()->restrictOnDelete();
            $table->foreignId('section_id');
            $table->foreign(['section_id', 'grade_id'])->references(['id', 'grade_id'])->on('sections')->restrictOnDelete();
            $table->date('enrolled_at');
            $table->boolean('active')->default(true);
            $table->unique(['student_id', 'school_year_id']);
            $table->index(['school_year_id', 'grade_id', 'section_id']);
            $table->timestamps();
        });
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('school_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('grade_id')->constrained()->restrictOnDelete();
            $table->foreignId('section_id');
            $table->foreign(['section_id', 'grade_id'])->references(['id', 'grade_id'])->on('sections')->restrictOnDelete();
            $table->boolean('active')->default(true);
            $table->unique(['subject_id', 'school_year_id', 'section_id']);
            $table->timestamps();
        });
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->restrictOnDelete();
            $table->foreignId('assignment_id')->constrained()->restrictOnDelete();
            $table->foreignId('period_id')->constrained()->restrictOnDelete();
            $table->decimal('score', 5, 2);
            $table->string('observation')->nullable();
            $table->unique(['enrollment_id', 'assignment_id', 'period_id']);
            $table->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('entity');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->json('changes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        foreach (['audit_logs', 'marks', 'assignments', 'enrollments', 'periods', 'subjects', 'sections', 'grades', 'school_years', 'student_guardians', 'relationships', 'guardians', 'teachers', 'students'] as $name) {
            Schema::dropIfExists($name);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['username', 'role', 'active', 'last_login_at']));
    }
};
