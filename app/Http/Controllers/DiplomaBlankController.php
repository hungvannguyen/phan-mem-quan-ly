<?php

namespace App\Http\Controllers;

use App\Models\DiplomaBlank;
use App\Models\DiplomaBlankType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiplomaBlankController extends Controller
{
    /**
     * Display a listing of the diploma blanks.
     */
    public function index(Request $request)
    {
        $query = DiplomaBlank::with(['type']);

        // Tìm kiếm theo số seri - chỉ khi có input
        if ($request->filled('serial_number')) {
            $query->where('serial_number', 'like', '%' . $request->serial_number . '%');
        }

        // Lọc theo loại phôi - chỉ khi có input
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }

        // Lọc theo trạng thái - chỉ khi có input
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo ngày nhập - từ ngày
        if ($request->filled('import_date_from')) {
            $query->whereDate('import_date', '>=', $request->import_date_from);
        }

        // Lọc theo ngày nhập - đến ngày
        if ($request->filled('import_date_to')) {
            $query->whereDate('import_date', '<=', $request->import_date_to);
        }

        // Lọc theo ngày cấp - từ ngày
        if ($request->filled('issue_date_from')) {
            $query->whereDate('issue_date', '>=', $request->issue_date_from);
        }

        // Lọc theo import_id (để hiển thị DiplomaBlank thuộc về một import cụ thể)
        if ($request->filled('import_id')) {
            // Join với bảng diploma_blank_import để lọc theo serial number range
            $importId = $request->input('import_id');
            $import = \App\Models\DiplomaBlankImport::find($importId);

            if ($import) {
                $prefix = $import->prefix ?? '';
                $suffix = $import->suffix ?? '';

                // Filter records có serial number trong range của import này
                $query->where(function ($q) use ($prefix, $suffix, $import) {
                    if (!empty($prefix) && !empty($suffix)) {
                        $q->where('serial_number', 'like', $prefix . '%' . $suffix);
                    } elseif (!empty($prefix)) {
                        $q->where('serial_number', 'like', $prefix . '%');
                    } elseif (!empty($suffix)) {
                        $q->where('serial_number', 'like', '%' . $suffix);
                    }

                    // Additional filter by type_id
                    $q->where('type_id', $import->type_id);

                    // Filter by import_date
                    $q->whereDate('import_date', '=', $import->import_date);
                });
            }
        }

        // Get per_page from request, default to 15
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [5, 10, 15, 25, 50]) ? $perPage : 15;

        $diplomaBlanks = $query->orderBy('created_at', 'desc')->paginate($perPage);
        $diplomaBlankTypes = DiplomaBlankType::orderBy('type_name')->get();

        // Lấy thông tin import nếu có filter theo import_id
        $currentImport = null;
        if ($request->filled('import_id')) {
            $currentImport = \App\Models\DiplomaBlankImport::with('diplomaBlankType')->find($request->input('import_id'));
        }

        if ($request->ajax()) {
            return view('components.diploma-blanks.table', compact('diplomaBlanks'))->render();
        }

        return view('diploma-blanks-list', [
            'diplomaBlanks' => $diplomaBlanks,
            'diplomaBlankTypes' => $diplomaBlankTypes,
            'currentImport' => $currentImport,
        ]);
    }

    /**
     * Show the form for importing diploma blanks.
     */
    public function import()
    {
        $diplomaBlankTypes = DiplomaBlankType::orderBy('type_name')->get();
        return view('diploma-blanks.import', compact('diplomaBlankTypes'));
    }

    /**
     * Process the import of diploma blanks.
     */
    public function processImport(Request $request)
    {
        // Validate input
        $request->validate([
            'type_id' => 'required|exists:diploma_blank_types,type_id',
            'import_date' => 'required|date',
            'document_request' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1|max:10000',
            'from_prefix' => 'nullable|string|max:50',
            'from_number' => 'required|string|max:50',
            'from_suffix' => 'nullable|string|max:50',
            'to_prefix' => 'nullable|string|max:50',
            'to_number' => 'required|string|max:50',
            'to_suffix' => 'nullable|string|max:50',
        ]);

        // Validate fixed fields match
        if ($request->from_prefix !== $request->to_prefix) {
            return redirect()->back()
                ->withErrors(['from_prefix' => 'Trường cố định 1 của "Từ Serial" và "Đến Serial" phải giống nhau.'])
                ->withInput();
        }

        if ($request->from_suffix !== $request->to_suffix) {
            return redirect()->back()
                ->withErrors(['from_suffix' => 'Trường cố định 2 của "Từ Serial" và "Đến Serial" phải giống nhau.'])
                ->withInput();
        }

        // Validate number range matches quantity
        $fromNumber = (int) $request->from_number;
        $toNumber = (int) $request->to_number;
        $expectedQuantity = $toNumber - $fromNumber + 1;

        if ($expectedQuantity !== (int) $request->quantity) {
            return redirect()->back()
                ->withErrors(['quantity' => "Số lượng phôi ({$request->quantity}) không khớp với khoảng Serial ({$expectedQuantity})."])
                ->withInput();
        }

        // Generate serial numbers and check for duplicates
        $serialNumbers = [];
        $existingSerials = [];

        for ($i = $fromNumber; $i <= $toNumber; $i++) {
            $paddedNumber = str_pad($i, strlen($request->from_number), '0', STR_PAD_LEFT);
            $serial = $request->from_prefix . $paddedNumber . $request->from_suffix;
            $serialNumbers[] = $serial;
        }

        // Check for existing serials
        $existing = DiplomaBlank::whereIn('serial_number', $serialNumbers)->pluck('serial_number')->toArray();

        if (!empty($existing)) {
            return redirect()->back()
                ->withErrors(['serial_duplicate' => 'Các Serial sau đã tồn tại trong hệ thống: ' . implode(', ', $existing)])
                ->withInput();
        }

        // Create diploma blanks
        $diplomaBlanks = [];
        foreach ($serialNumbers as $serial) {
            $diplomaBlanks[] = [
                'serial_number' => $serial,
                'type_id' => $request->type_id,
                'status' => 'InStock',
                'import_date' => $request->import_date,
                'issue_reason' => 'Nhập từ văn bản: ' . $request->document_request,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Batch insert
        DiplomaBlank::insert($diplomaBlanks);

        return redirect()->route('diploma-blank-management')
            ->with('success', "Đã nhập thành công {$request->quantity} phôi văn bằng vào hệ thống.");
    }

    /**
     * AJAX endpoint to check for duplicate serials
     */
    public function checkDuplicates(Request $request)
    {
        $request->validate([
            'from_prefix' => 'nullable|string|max:50',
            'from_number' => 'required|string|max:50',
            'from_suffix' => 'nullable|string|max:50',
            'to_number' => 'required|string|max:50',
        ]);

        $fromNumber = (int) $request->from_number;
        $toNumber = (int) $request->to_number;
        $serialNumbers = [];

        for ($i = $fromNumber; $i <= $toNumber; $i++) {
            $paddedNumber = str_pad($i, strlen($request->from_number), '0', STR_PAD_LEFT);
            $serial = $request->from_prefix . $paddedNumber . $request->from_suffix;
            $serialNumbers[] = $serial;
        }

        $existing = DiplomaBlank::whereIn('serial_number', $serialNumbers)->pluck('serial_number')->toArray();

        return response()->json([
            'hasDuplicates' => !empty($existing),
            'duplicates' => $existing,
            'total_checked' => count($serialNumbers),
        ]);
    }

    /**
     * Show the form for importing diploma blanks.
     */
    public function showImportForm()
    {
        $diplomaBlankTypes = DiplomaBlankType::orderBy('type_name')->get();
        return view('diploma-blank-import', compact('diplomaBlankTypes'));
    }

    /**
     * Store a newly created diploma blank in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string|max:255|unique:diploma_blanks',
            'type_id' => 'required|exists:diploma_blank_types,type_id',
            'import_date' => 'required|date',
            'status' => 'required|in:InStock,Issued,Damaged,Recalled',
        ]);

        DiplomaBlank::create($request->all());

        return redirect()->route('diploma-blank-management')
            ->with('success', 'Phôi văn bằng đã được thêm thành công.');
    }

    /**
     * Display the specified diploma blank.
     */
    public function show(DiplomaBlank $diplomaBlank)
    {
        $diplomaBlank->load(['type']);
        return view('diploma-blanks.show', compact('diplomaBlank'));
    }

    /**
     * Show the form for editing the specified diploma blank.
     */
    public function edit(DiplomaBlank $diplomaBlank)
    {
        $diplomaBlankTypes = DiplomaBlankType::orderBy('type_name')->get();
        return view('diploma-blanks.edit', compact('diplomaBlank', 'diplomaBlankTypes'));
    }

    /**
     * Update the specified diploma blank in storage.
     */
    public function update(Request $request, DiplomaBlank $diplomaBlank)
    {
        $request->validate([
            'serial_number' => 'required|string|max:255|unique:diploma_blanks,serial_number,' . $diplomaBlank->blank_id . ',blank_id',
            'type_id' => 'required|exists:diploma_blank_types,type_id',
            'import_date' => 'required|date',
            'status' => 'required|in:InStock,Issued,Damaged,Recalled',
        ]);

        $diplomaBlank->update($request->all());

        return redirect()->route('diploma-blank-management')
            ->with('success', 'Thông tin phôi văn bằng đã được cập nhật.');
    }

    /**
     * Remove the specified diploma blank from storage.
     */
    public function destroy(DiplomaBlank $diplomaBlank)
    {
        $diplomaBlank->delete();

        return redirect()->route('diploma-blank-management')
            ->with('success', 'Phôi văn bằng đã được xóa thành công.');
    }

    /**
     * Issue a diploma blank.
     */
    public function issue(Request $request, DiplomaBlank $diplomaBlank)
    {
        $request->validate([
            'issue_reason' => 'required|string',
        ]);

        $diplomaBlank->update([
            'status' => 'Issued',
            'issue_date' => now(),
            'issue_reason' => $request->issue_reason,
        ]);

        return redirect()->route('diploma-blank-management')
            ->with('success', 'Phôi văn bằng đã được cấp thành công.');
    }

    /**
     * Recall a diploma blank.
     */
    public function recall(Request $request, DiplomaBlank $diplomaBlank)
    {
        $request->validate([
            'recall_reason' => 'required|string',
        ]);

        $diplomaBlank->update([
            'status' => 'Recalled',
            'recall_date' => now(),
            'recall_reason' => $request->recall_reason,
        ]);

        return redirect()->route('diploma-blank-management')
            ->with('success', 'Phôi văn bằng đã được thu hồi thành công.');
    }

    /**
     * Mark a diploma blank as damaged.
     */
    public function markDamaged(Request $request, DiplomaBlank $diplomaBlank)
    {
        $request->validate([
            'recall_reason' => 'required|string',
        ]);

        $diplomaBlank->update([
            'status' => 'Damaged',
            'recall_date' => now(),
            'recall_reason' => $request->recall_reason,
        ]);

        return redirect()->route('diploma-blank-management')
            ->with('success', 'Phôi văn bằng đã được đánh dấu là hư hỏng.');
    }

    /**
     * Store imported diploma blanks
     */
    public function storeImport(Request $request)
    {
        dd('ok');
    }
}