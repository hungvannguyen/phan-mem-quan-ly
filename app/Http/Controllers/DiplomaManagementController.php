<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Models\Student;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiplomaManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['major', 'degrees']);

        // Tìm kiếm theo tên
        if ($request->filled('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
        }

        // Tìm kiếm theo mã sinh viên
        if ($request->filled('student_code')) {
            $query->where('student_code', 'like', '%' . $request->student_code . '%');
        }

        // Tìm kiếm theo lớp
        if ($request->filled('class_name')) {
            $query->where('class_name', 'like', '%' . $request->class_name . '%');
        }

        // Tìm kiếm theo ngày sinh
        if ($request->filled('date_of_birth')) {
            $query->whereDate('date_of_birth', $request->date_of_birth);
        }

        // Tìm kiếm theo ngành
        if ($request->filled('major_id')) {
            $query->where('major_id', $request->major_id);
        }

        // Lọc theo loại văn bằng (thông qua bảng degrees)
        if ($request->filled('degree_type')) {
            $query->whereHas('degrees', function ($q) use ($request) {
                $q->where('degree_type', $request->degree_type);
            });
        }

        // Get per_page from request, default to 15
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [5, 10, 15, 25, 50]) ? $perPage : 15;

        $students = $query->orderBy('created_at', 'desc')->paginate($perPage);
        $majors = Major::orderBy('major_name')->get();

        if ($request->ajax()) {
            return view('components.students.table', compact('students'))->render();
        }

        return view('diploma-management', [
            'students' => $students,
            'majors' => $majors,
        ]);
    }

    public function create()
    {
        $majors = Major::orderBy('major_name')->get();

        return view('student-create', compact('majors'));
    }

    public function save(StudentRequest $request)
    {
        Student::create($request->validated());

        return redirect()->route('diploma-management')->with('success', 'Thêm sinh viên thành công!');
    }

    public function student(Student $student)
    {
        $majors = Major::orderBy('major_name')->get();

        return view('student-edit', compact('student', 'majors'));
    }

    public function update(StudentRequest $request, Student $student)
    {
        $student->update($request->validated());

        return redirect()->route('diploma-management')->with('success', 'Cập nhật thông tin sinh viên thành công!');
    }
}
