<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Models\Student;
use App\Models\Major;
use App\Models\Degree;
use App\Models\DiplomaBlankType;
use App\Exports\DiplomaVerificationExport;
use App\Exports\BachelorConfirmationExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DiplomaBlank;
use App\Enums\DiplomaBlankStatus;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DiplomaManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['major', 'degrees']);

        // Tìm kiếm theo tên - chỉ khi có input
        if ($request->filled('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
        }

        // Tìm kiếm theo mã sinh viên - chỉ khi có input
        if ($request->filled('student_code')) {
            $query->where('student_code', 'like', '%' . $request->student_code . '%');
        }

        // Tìm kiếm theo lớp - chỉ khi có input
        if ($request->filled('class_name')) {
            $query->where('class_name', 'like', '%' . $request->class_name . '%');
        }

        // Tìm kiếm theo ngày sinh - chỉ khi có input (flexible search)
        if ($request->filled('date_of_birth')) {
            $dateOfBirth = $request->date_of_birth;

            // Parse flexible date search format
            if (strpos($dateOfBirth, 'ngay:') === 0) {
                $day = substr($dateOfBirth, 5);
                $query->whereDay('date_of_birth', $day);
            } elseif (strpos($dateOfBirth, 'thang:') === 0) {
                $month = substr($dateOfBirth, 6);
                $query->whereMonth('date_of_birth', $month);
            } elseif (strpos($dateOfBirth, 'nam:') === 0) {
                $year = substr($dateOfBirth, 4);
                $query->whereYear('date_of_birth', $year);
            } elseif (strpos($dateOfBirth, 'thang_nam:') === 0) {
                $monthYear = substr($dateOfBirth, 10);
                [$month, $year] = explode('/', $monthYear);
                $query->whereMonth('date_of_birth', $month)
                    ->whereYear('date_of_birth', $year);
            } elseif (strpos($dateOfBirth, 'ngay_thang:') === 0) {
                $dayMonth = substr($dateOfBirth, 11);
                [$day, $month] = explode('/', $dayMonth);
                $query->whereDay('date_of_birth', $day)
                    ->whereMonth('date_of_birth', $month);
            } elseif (strpos($dateOfBirth, 'ngay_cu_the:') === 0) {
                $fullDate = substr($dateOfBirth, 12);
                [$day, $month, $year] = explode('/', $fullDate);
                $query->whereDate('date_of_birth', sprintf('%04d-%02d-%02d', $year, $month, $day));
            } else {
                // Fallback: try to parse as regular date
                try {
                    $query->whereDate('date_of_birth', $dateOfBirth);
                } catch (\Exception $e) {
                    // Invalid date format, ignore this filter
                }
            }
        }

        // Tìm kiếm theo khóa - chỉ khi có input
        if ($request->filled('course')) {
            $query->where('course', 'like', '%' . $request->course . '%');
        }

        // Tìm kiếm theo niên khóa - chỉ khi có input
        if ($request->filled('academic_year')) {
            $query->where('academic_year', 'like', '%' . $request->academic_year . '%');
        }

        // Tìm kiếm theo ngành - chỉ khi có input
        if ($request->filled('major_id')) {
            $query->where('major_id', $request->major_id);
        }

        // Lọc theo loại văn bằng - chỉ khi có input
        if ($request->filled('diploma_blank_type_id')) {
            $query->whereHas('degrees.diplomaBlank', function ($q) use ($request) {
                $q->where('type_id', $request->diploma_blank_type_id);
            });
        }

        // Get per_page from request, default to 15
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [5, 10, 15, 25, 50]) ? $perPage : 15;

        $students = $query->orderBy('created_at', 'desc')->paginate($perPage);
        $majors = Major::orderBy('major_name')->get();
        $diplomaBlankTypes = DiplomaBlankType::orderBy('type_name')->get();

        if ($request->ajax()) {
            return view('components.students.table', compact('students'))->render();
        }

        return view('components.diplomas.management', [
            'students' => $students,
            'majors' => $majors,
            'diplomaBlankTypes' => $diplomaBlankTypes,
        ]);
    }

    public function create()
    {
        $majors = Major::orderBy('major_name')->get();

        return view('components.students.create', compact('majors'));
    }

    public function save(StudentRequest $request)
    {
        Student::create($request->validated());

        return redirect()->route('diploma-management')->with('success', 'Thêm sinh viên thành công!');
    }

    public function student(Student $student)
    {
        // Load relationships
        $student->load(['major', 'degrees']);

        // Get all majors for dropdown
        $majors = Major::orderBy('major_name')->get();

        // Get all diploma blank types for dropdown
        $diplomaBlankTypes = DiplomaBlankType::orderBy('type_name')->get();

        // Get degrees issued to this student with all relationships
        $degrees = $student->degrees()->with(['major', 'diplomaBlank.type'])->get();

        return view('components.students.edit', compact('student', 'majors', 'diplomaBlankTypes', 'degrees'));
    }

    public function update(StudentRequest $request, Student $student)
    {
        $student->update($request->validated());

        return redirect()->route('diploma-management')->with('success', 'Cập nhật thông tin sinh viên thành công!');
    }

    public function storeDegree(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'degree_type' => 'required|string|in:bachelor,master,doctor,certificate',
            'diploma_blank_id' => 'required|exists:diploma_blanks,diploma_blank_id',
            'registration_number' => 'required|string|max:255|unique:degrees,registration_number',
            'graduation_year' => 'required|integer|min:1990|max:' . date('Y'),
            'granting_date' => 'required|date|before_or_equal:today',
            'ranking' => 'nullable|string|max:100',
            'decision_number' => 'nullable|string|max:255',
            'major_id' => 'nullable|exists:majors,major_id',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Check if student exists and has graduated status
                $student = Student::findOrFail($validated['student_id']);

                if ($student->status->value !== 1) {
                    throw new \Exception('Chỉ có thể cấp văn bằng cho sinh viên đã tốt nghiệp!');
                }

                // Check diploma blank availability and lock it
                $diplomaBlank = DiplomaBlank::where('diploma_blank_id', $validated['diploma_blank_id'])
                    ->where('status', DiplomaBlankStatus::IN_STOCK)
                    ->whereDoesntHave('degree') // Not already assigned
                    ->lockForUpdate() // Lock for atomic update
                    ->first();

                if (!$diplomaBlank) {
                    throw new \Exception('Phôi văn bằng không khả dụng hoặc đã được sử dụng!');
                }

                // If major_id is provided, get major_name from Major model
                if (!empty($validated['major_id'])) {
                    $major = Major::find($validated['major_id']);
                    if ($major) {
                        $validated['major_name'] = $major->major_name;
                    }
                }

                // Create the degree
                $degree = Degree::create($validated);

                // Update diploma blank status to ISSUED
                $diplomaBlank->update([
                    'status' => DiplomaBlankStatus::ISSUED,
                    'issue_date' => $validated['granting_date'],
                    'issue_reason' => "Cấp văn bằng cho sinh viên: {$student->full_name}"
                ]);
            });

            // Redirect back to student page with success message
            return redirect()->route('student', ['student' => $validated['student_id']])
                ->with('success', 'Thêm văn bằng thành công!');
        } catch (\Exception $e) {
            // Redirect back to student page with error message
            return redirect()->route('student', ['student' => $validated['student_id']])
                ->with('error', 'Có lỗi xảy ra khi thêm văn bằng: ' . $e->getMessage());
        }
    }

    public function getAvailableDiplomaBlanks($typeId)
    {
        try {
            // Get the oldest available diploma blank for the specified type
            $oldestBlank = DiplomaBlank::where('type_id', $typeId)
                ->where('status', DiplomaBlankStatus::IN_STOCK)
                ->whereDoesntHave('degree') // Not assigned to any degree
                ->orderBy('import_date', 'asc') // Oldest first by import date
                ->orderBy('serial_number', 'asc') // Then by serial number
                ->first(['diploma_blank_id', 'serial_number', 'import_date']);

            if ($oldestBlank) {
                return response()->json([
                    'success' => true,
                    'blank' => [
                        'diploma_blank_id' => $oldestBlank->diploma_blank_id,
                        'serial_number' => $oldestBlank->serial_number,
                        'import_date' => $oldestBlank->import_date ? $oldestBlank->import_date->format('d/m/Y') : 'N/A'
                    ],
                    'message' => "Sẽ sử dụng phôi {$oldestBlank->serial_number} (cũ nhất khả dụng)"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có phôi khả dụng cho loại này'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải danh sách phôi'
            ], 500);
        }
    }

    public function updateDegree(Request $request, Degree $degree)
    {
        // Validate the request
        $validated = $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'degree_type' => 'required|string|in:bachelor,master,doctor,certificate',
            'registration_number' => 'required|string|max:255|unique:degrees,registration_number,' . $degree->degree_id . ',degree_id',
            'graduation_year' => 'required|integer|min:1990|max:' . date('Y'),
            'granting_date' => 'required|date|before_or_equal:today',
            'ranking' => 'nullable|string|max:100',
            'decision_number' => 'nullable|string|max:255',
            'major_id' => 'nullable|exists:majors,major_id',
            'notes' => 'nullable|string',
        ]);

        try {
            // Check if student exists and has graduated status
            $student = Student::findOrFail($validated['student_id']);

            if ($student->status->value !== 1) {
                return redirect()->route('student', ['student' => $validated['student_id']])
                    ->with('error', 'Chỉ có thể cập nhật văn bằng cho sinh viên đã tốt nghiệp!');
            }

            // If major_id is provided, get major_name from Major model
            if (!empty($validated['major_id'])) {
                $major = Major::find($validated['major_id']);
                if ($major) {
                    $validated['major_name'] = $major->major_name;
                }
            }

            // Update the degree (không thay đổi diploma_blank_id)
            $degree->update($validated);

            // Redirect back to student page with success message
            return redirect()->route('student', ['student' => $validated['student_id']])
                ->with('success', 'Cập nhật văn bằng thành công!');
        } catch (\Exception $e) {
            // Redirect back to student page with error message
            return redirect()->route('student', ['student' => $validated['student_id']])
                ->with('error', 'Có lỗi xảy ra khi cập nhật văn bằng: ' . $e->getMessage());
        }
    }

    public function deleteStudent(Student $student)
    {
        try {
            // Get all degrees issued to this student
            $degrees = $student->degrees()->get();

            // Soft delete all degrees first and revert diploma blanks to IN_STOCK
            foreach ($degrees as $degree) {
                $degree->delete();

                if ($degree->diploma_blank_id) {
                    $diplomaBlank = DiplomaBlank::find($degree->diploma_blank_id);
                    if ($diplomaBlank) {
                        $diplomaBlank->update([
                            'status' => DiplomaBlankStatus::IN_STOCK->value,
                            'issue_date' => null,
                            'issue_reason' => null
                        ]);
                    }
                }
            }

            // Soft delete the student
            $student->delete();

            // Redirect back to diploma management with success message
            return redirect()->route('diploma-management')
                ->with('success', 'Xóa sinh viên thành công! Tất cả văn bằng và phôi đã được trả về kho.');
        } catch (\Exception $e) {
            // Redirect back to student page with error message
            return redirect()->route('student', ['student' => $student->student_id])
                ->with('error', 'Có lỗi xảy ra khi xóa sinh viên: ' . $e->getMessage());
        }
    }

    public function deleteDegree(Degree $degree)
    {
        try {
            $student = Student::findOrFail($degree->student_id);

            // Soft delete the degree
            $degree->delete();

            // If degree had a diploma blank, revert its status to IN_STOCK
            if ($degree->diploma_blank_id) {
                $diplomaBlank = DiplomaBlank::find($degree->diploma_blank_id);
                if ($diplomaBlank) {
                    $diplomaBlank->update([
                        'status' => DiplomaBlankStatus::IN_STOCK->value,
                        'issue_date' => null,
                        'issue_reason' => null
                    ]);
                }
            }

            // Redirect back to student page with success message
            return redirect()->route('student', ['student' => $degree->student_id])
                ->with('success', 'Xóa văn bằng thành công! Phôi văn bằng đã được trả về kho.');
        } catch (\Exception $e) {
            // Redirect back to student page with error message
            return redirect()->route('student', ['student' => $degree->student_id])
                ->with('error', 'Có lỗi xảy ra khi xóa văn bằng: ' . $e->getMessage());
        }
    }

    /**
     * Export diploma verification document for a student
     *
     * @param Student $student
     * @return BinaryFileResponse|RedirectResponse
     */
    public function exportDiplomaVerification(Student $student)
    {
        try {
            if ($student->degrees->count() === 0) {
                return redirect()->back()->with('error', 'Sinh viên chưa được cấp văn bằng nào!');
            }

            $export = new DiplomaVerificationExport($student);
            $filePath = $export->generate();

            return response()->download($filePath, basename($filePath), [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xuất văn bản xác minh: ' . $e->getMessage());
        }
    }

    /**
     * Export bachelor confirmation document for a student
     *
     * @param Student $student
     * @return BinaryFileResponse|RedirectResponse
     */
    public function exportBachelorConfirmation(Student $student)
    {
        try {
            if ($student->degrees->count() === 0) {
                return redirect()->back()->with('error', 'Sinh viên chưa được cấp văn bằng nào!');
            }

            $export = new BachelorConfirmationExport($student);
            $filePath = $export->generate();

            return response()->download($filePath, basename($filePath), [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xuất giấy xác nhận: ' . $e->getMessage());
        }
    }
}
