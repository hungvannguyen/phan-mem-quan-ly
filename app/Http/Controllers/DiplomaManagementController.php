<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Models\Student;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiplomaManagementController extends Controller
{
	public function index(Request $request)
	{
		$students = Student::orderBy('created_at', 'desc')->paginate(10);

		$trainings = Training::all();

		if ($request->ajax()) {
			return view('components.students.table', compact('students', 'trainings'))->render();
		}

		return view('diploma-management', [
				'students' => $students,
				'trainings' => $trainings,
		]);
	}

	public function create()
	{
		$trainings = Training::all();

		return view('student-create', compact('trainings'));
	}

	public function save(StudentRequest $request)
	{
		Student::create($request->validated());
		$students = Student::orderBy('created_at', 'desc')->paginate(10);
		return view('diploma-management', compact('students'));
	}

	public function student(Student $student)
	{
		$trainings = Training::all();

		return view('student-edit', compact('student', 'trainings'));
	}

	public function update(StudentRequest $request, Student $student)
	{
		$student->update($request->validated());
		$students = Student::orderBy('created_at', 'desc')->paginate(10);
		return view('diploma-management', compact('student', 'students'));
	}
}
