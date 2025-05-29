<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Training;
use Illuminate\Http\Request;

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

	public function create(Request $request)
	{
		$validated = $request->validate([
				'name' => 'required|string|max:255',
				'training_id' => 'required|integer',
				'date_of_birth' => 'required|date',
				'place_of_birth' => 'required|string|max:255',
				'gender' => 'required|integer',
				'nation' => 'required|string|max:255',
				'nationality' => 'required|string|max:255',
				'number_in_the_book' => 'required|string|max:255',
				'status' => 'required|integer',
		]);

		$student = Student::create($validated);

		if ($request->ajax()) {
			return response()->json([
					'message' => 'Thêm sinh viên thành công!',
					'student' => $student,
			]);
		}

		return back()->with('success', 'Thêm sinh viên thành công!');
	}

}
