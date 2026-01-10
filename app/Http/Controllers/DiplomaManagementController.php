<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Models\Student;
use App\Models\Major;
use App\Models\Degree;
use App\Models\DegreeReissue;
use App\Models\ChangeLog;
use App\Models\DiplomaBlankType;
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

        $students = $query->orderBy('created_at', 'desc')
            ->orderBy('student_id', 'asc')
            ->paginate($perPage)
            ->appends($request->all());
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

        // Get degrees issued to this student with all relationships including change logs
        $degrees = $student->degrees()->with([
            'major', 
            'diplomaBlank.type', 
            'changeLogs.changedBy', 
            'reissues.oldDiplomaBlank.type',
            'reissues.newDiplomaBlank.type'
        ])->get();

        return view('components.students.edit', compact('student', 'majors', 'diplomaBlankTypes', 'degrees'));
    }

    public function update(StudentRequest $request, Student $student)
    {
        $student->update($request->validated());

        // After updating, redirect back to the student's edit page
        return redirect()->route('student.show', ['student' => $student->student_id])
            ->with('success', 'Cập nhật thông tin sinh viên thành công!');
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
            'training_start_date' => 'nullable|date',
            'training_end_date' => 'nullable|date|after_or_equal:training_start_date',
            'ranking' => 'nullable|string|max:100',
            'decision_number' => 'nullable|string|max:255',
            'council_decision_number' => 'nullable|string|max:255',
            'council_decision_date' => 'nullable|date',
            'graduation_decision_number' => 'nullable|string|max:255',
            'graduation_decision_date' => 'nullable|date',
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
                    ->where('status', DiplomaBlankStatus::IN_STOCK->value)
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
            return redirect()->route('student.show', ['student' => $validated['student_id']])
                ->with('success', 'Thêm văn bằng thành công!');
        } catch (\Exception $e) {
            // Redirect back to student page with error message
            return redirect()->route('student.show', ['student' => $validated['student_id']])
                ->with('error', 'Có lỗi xảy ra khi thêm văn bằng: ' . $e->getMessage());
        }
    }

    public function getAvailableDiplomaBlanks($typeId)
    {
        try {
            // Get the oldest available diploma blank for the specified type
            $oldestBlank = DiplomaBlank::where('type_id', $typeId)
                ->where('status', DiplomaBlankStatus::IN_STOCK->value)
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
            'training_start_date' => 'nullable|date',
            'training_end_date' => 'nullable|date|after_or_equal:training_start_date',
            'ranking' => 'nullable|string|max:100',
            'decision_number' => 'nullable|string|max:255',
            'council_decision_number' => 'nullable|string|max:255',
            'council_decision_date' => 'nullable|date',
            'graduation_decision_number' => 'nullable|string|max:255',
            'graduation_decision_date' => 'nullable|date',
            'major_id' => 'nullable|exists:majors,major_id',
            'notes' => 'nullable|string',
        ]);

        try {
            // Check if student exists
            $student = Student::findOrFail($validated['student_id']);

            // Only check graduation status for bachelor, master, doctor degrees
            // Certificates can be issued to students regardless of graduation status
            if (in_array($validated['degree_type'], ['bachelor', 'master', 'doctor']) && $student->status->value !== 1) {
                return redirect()->route('student.show', ['student' => $validated['student_id']])
                    ->with('error', 'Chỉ có thể cập nhật văn bằng Cử nhân/Thạc sĩ/Tiến sĩ cho sinh viên đã tốt nghiệp!');
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
            return redirect()->route('student.show', ['student' => $validated['student_id']])
                ->with('success', 'Cập nhật văn bằng thành công!');
        } catch (\Exception $e) {
            // Redirect back to student page with error message
            return redirect()->route('student.show', ['student' => $validated['student_id']])
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
            return redirect()->route('student.show', ['student' => $student->student_id])
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
            return redirect()->route('student.show', ['student' => $degree->student_id])
                ->with('success', 'Xóa văn bằng thành công! Phôi văn bằng đã được trả về kho.');
        } catch (\Exception $e) {
            // Redirect back to student page with error message
            return redirect()->route('student.show', ['student' => $degree->student_id])
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
        return $this->handleExport('diploma-verification', $student);
    }

    /**
     * Export bachelor confirmation document for a student
     *
     * @param Student $student
     * @return BinaryFileResponse|RedirectResponse
     */
    public function exportBachelorConfirmation(Student $student)
    {
        return $this->handleExport('bachelor-confirmation', $student);
    }

    /**
     * Handle export using factory pattern
     *
     * @param string $type Export type key from config
     * @param Student $student Student instance
     * @return BinaryFileResponse|RedirectResponse
     */
    protected function handleExport(string $type, Student $student)
    {
        try {
            // Get service class from config
            $serviceClass = config("export.services.{$type}");

            if (!$serviceClass) {
                throw new \Exception("Export type '{$type}' không được hỗ trợ");
            }

            // Resolve service from container
            $service = app($serviceClass);

            // Call export method with data
            return $service->export([
                'student' => $student,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xuất file: ' . $e->getMessage());
        }
    }

    /**
     * Store a new degree adjustment
     */
    public function storeAdjustment(Request $request, Degree $degree)
    {
        $validated = $request->validate([
            'adjusted_field' => 'required|string',
            'old_value' => 'nullable|string|max:500',
            'new_value' => 'required|string|max:500',
            'adjustment_content' => 'nullable|string|max:1000', // Không bắt buộc
            'decision_number' => 'nullable|string|max:100',
            'decision_date' => 'nullable|date',
        ]);

        try {
            $fieldName = $validated['adjusted_field'];
            $oldValue = $validated['old_value'];
            $newValue = $validated['new_value'];

            // Kiểm tra unique constraint cho registration_number
            if ($fieldName === 'registration_number') {
                $existingDegree = Degree::where('registration_number', $newValue)
                    ->where('degree_id', '!=', $degree->degree_id)
                    ->first();

                if ($existingDegree) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Số đăng ký '{$newValue}' đã tồn tại. Vui lòng sử dụng số đăng ký khác.");
                }
            }

            // Kiểm tra có nội dung điều chỉnh tùy chỉnh không
            $hasCustomContent = !empty($validated['adjustment_content']) &&
                trim($validated['adjustment_content']) !== '';

            if ($hasCustomContent) {
                // Nếu có nội dung điều chỉnh từ form, tạo log thủ công với thông tin đầy đủ
                ChangeLog::logChange(
                    entityType: 'Degree',
                    entityId: $degree->degree_id,
                    changeDescription: $validated['adjustment_content'],
                    changedField: $fieldName,
                    oldValue: $oldValue,
                    newValue: $newValue,
                    decisionNumber: $validated['decision_number'] ?? null,
                    decisionDate: $validated['decision_date'] ?? null,
                    changedBy: auth()->user()->user_id,
                    actionType: 'update'
                );

                // Tắt auto-logging để tránh tạo log trùng
                $degree->disableLogging();
            }
            // Nếu không có nội dung tùy chỉnh, để trait tự động log với description mặc định

            // Update the actual field value in degrees table
            // Check if the field exists in the fillable array to prevent mass assignment issues
            if (in_array($fieldName, $degree->getFillable())) {
                $degree->update([
                    $fieldName => $newValue
                ]);
            }

            if ($hasCustomContent) {
                // Bật lại logging
                $degree->enableLogging();
            }

            return redirect()->route('student.show', ['student' => $degree->student_id])
                ->with('success', 'Đã điều chỉnh thông tin văn bằng thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi điều chỉnh: ' . $e->getMessage());
        }
    }

    /**
     * Get adjustments for a degree
     */
    public function getAdjustments(Degree $degree)
    {
        try {
            // Use changeLogs relationship which returns hasMany filtered by entity_type
            $adjustments = $degree->changeLogs()->with('changedBy')->get();

            return response()->json([
                'success' => true,
                'adjustments' => $adjustments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải lịch sử điều chỉnh'
            ], 500);
        }
    }

    /**
     * Store a new degree reissue
     */
    public function storeReissue(Request $request, Degree $degree)
    {
        try {
            \Log::info('Reissue request received', [
                'all_data' => $request->all(),
                'degree_id' => $degree->degree_id
            ]);

            $validated = $request->validate([
                'new_diploma_blank_id' => 'required|exists:diploma_blanks,diploma_blank_id',
                'edit_content' => 'required|string',
                'recall_decision' => 'required|string|max:100',
                'decision_date' => 'required|date',
                'old_blank_status' => 'required|in:recalled,destroyed,not_recalled',
                'notes' => 'nullable|string',
            ]);

            \Log::info('Validation passed', ['validated' => $validated]);

            DB::beginTransaction();

            // Get old and new diploma blanks
            $oldBlank = $degree->diplomaBlank;
            
            \Log::info('Looking for blank', ['blank_id' => $validated['new_diploma_blank_id']]);
            
            $newBlank = DiplomaBlank::findOrFail($validated['new_diploma_blank_id']);

            \Log::info('Found new blank', [
                'blank_id' => $newBlank->diploma_blank_id,
                'status' => $newBlank->status,
                'expected_status' => DiplomaBlankStatus::IN_STOCK
            ]);

            // Validate new blank is in stock
            if ($newBlank->status !== DiplomaBlankStatus::IN_STOCK) {
                DB::rollBack();
                \Log::warning('Blank not in stock', ['status' => $newBlank->status]);
                return redirect()->back()
                    ->with('error', 'Phôi văn bằng đã chọn không còn trong kho')
                    ->withInput();
            }

            // Create reissue record
            $validated['degree_id'] = $degree->degree_id;
            $validated['old_diploma_blank_id'] = $oldBlank?->diploma_blank_id;
            
            // Handle old blank status based on radio button value
            $oldBlankStatus = $request->input('old_blank_status', 'not_recalled');
            $validated['is_recalled'] = ($oldBlankStatus === 'recalled');
            $validated['is_destroyed'] = ($oldBlankStatus === 'destroyed');

            \Log::info('About to create reissue', ['validated_data' => $validated]);

            $reissue = DegreeReissue::create($validated);

            \Log::info('Reissue created', ['reissue_id' => $reissue->reissue_id]);

            // Update old blank status based on selection
            if ($oldBlank) {
                if ($oldBlankStatus === 'destroyed') {
                    $oldBlank->update(['status' => DiplomaBlankStatus::DESTROYED]);
                } elseif ($oldBlankStatus === 'recalled') {
                    $oldBlank->update(['status' => DiplomaBlankStatus::RECALLED]);
                }
                // If 'not_recalled', keep the old blank status unchanged
            }

            // Update new blank status to issued
            $newBlank->update(['status' => DiplomaBlankStatus::ISSUED]);

            // Update degree's diploma blank to new one
            $degree->update([
                'diploma_blank_id' => $newBlank->diploma_blank_id
            ]);

            DB::commit();

            \Log::info('Reissue created successfully');

            return redirect()->back()
                ->with('success', 'Đã lưu lịch sử cấp lại văn bằng thành công');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in storeReissue', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error storing degree reissue: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi lưu lịch sử cấp lại: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a degree reissue
     */
    public function deleteReissue($reissueId)
    {
        try {
            $reissue = \App\Models\DegreeReissue::findOrFail($reissueId);
            $reissue->delete();

            return redirect()->back()
                ->with('success', 'Đã xóa lịch sử cấp lại thành công');
        } catch (\Exception $e) {
            \Log::error('Error deleting degree reissue: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi xóa lịch sử cấp lại');
        }
    }

    /**
     * Get available diploma blanks by type
     */
    public function getAvailableBlanks(Request $request)
    {
        try {
            $typeId = $request->input('type_id');
            
            if (!$typeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Type ID is required'
                ], 400);
            }

            $blanks = DiplomaBlank::with('type')
                ->where('type_id', $typeId)
                ->where('status', DiplomaBlankStatus::IN_STOCK)
                ->orderBy('serial_number')
                ->get()
                ->map(function ($blank) {
                    return [
                        'diploma_blank_id' => $blank->diploma_blank_id,
                        'serial_number' => $blank->serial_number,
                        'type_name' => $blank->type?->type_name,
                    ];
                });

            return response()->json([
                'success' => true,
                'blanks' => $blanks
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting available blanks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách phôi'
            ], 500);
        }
    }
}