<?php

namespace App\Http\Controllers;

use App\Enums\ImportStatus;
use App\Models\DiplomaBlankImport;
use App\Models\DiplomaBlankType;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;

class DiplomaBlankImportController extends Controller
{
    /**
     * Hiển thị danh sách imports với tìm kiếm và lọc
     */
    public function index(Request $request): View
    {
        $query = DiplomaBlankImport::with(['diplomaBlankType']);

        // Lọc theo số văn bản
        if ($request->filled('document_reference')) {
            $query->where('document_reference', 'LIKE', '%' . $request->document_reference . '%');
        }

        // Lọc theo loại phôi
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $status = (int) $request->status;
            $query->where('status', $status);
        }

        // Lọc theo ngày nhập
        if ($request->filled('import_date_from')) {
            $query->whereDate('import_date', '>=', $request->import_date_from);
        }

        if ($request->filled('import_date_to')) {
            $query->whereDate('import_date', '<=', $request->import_date_to);
        }

        // Lọc theo ngày ban hành
        if ($request->filled('issue_date_from')) {
            $query->whereDate('issue_date', '>=', $request->issue_date_from);
        }

        if ($request->filled('issue_date_to')) {
            $query->whereDate('issue_date', '<=', $request->issue_date_to);
        }

        // Sắp xếp theo thời gian tạo mới nhất
        $query->orderBy('created_at', 'desc');

        // Phân trang với hỗ trợ per_page từ request
        $perPage = $request->get('per_page', 15); // Default 15 items per page
        $perPage = in_array($perPage, [5, 10, 15, 25, 50]) ? $perPage : 15; // Validate per_page value
        $diplomaBlankImports = $query->paginate($perPage)->appends($request->query());

        // Lấy danh sách loại phôi để hiển thị trong dropdown
        $diplomaBlankTypes = DiplomaBlankType::orderBy('type_name')->get();

        return view('components.diploma-blank-imports.management', compact('diplomaBlankImports', 'diplomaBlankTypes'));
    }

    /**
     * Hiển thị form tạo import mới
     */
    public function create(): View
    {
        $diplomaBlankTypes = DiplomaBlankType::orderBy('type_name')->get();

        return view('components.diploma-blank-imports.import', compact('diplomaBlankTypes'));
    }
    /**
     * Lưu import mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'type_id' => 'required|exists:diploma_blank_types,type_id',
            'document_reference' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'prefix' => 'nullable|string|max:10',
            'from_number' => 'required|string|min:1|max:10',
            'to_number' => 'required|string|min:1|max:10',
            'suffix' => 'nullable|string|max:10',
        ]);

        // Validate từ số <= đến số
        $fromNum = (int) $request->from_number;
        $toNum = (int) $request->to_number;

        if ($fromNum >= $toNum) {
            return back()->withErrors(['to_number' => 'Số kết thúc phải lớn hơn số bắt đầu.'])->withInput();
        }

        // Tính tổng số lượng
        $totalQuantity = $toNum - $fromNum + 1;

        // Kiểm tra trùng lặp serial trong cùng loại phôi
        $existingImport = DiplomaBlankImport::where('type_id', $request->type_id)
            ->where(function ($query) use ($request, $fromNum, $toNum) {
                $query->where('prefix', $request->prefix ?? '')
                    ->where('suffix', $request->suffix ?? '')
                    ->where(function ($rangeQuery) use ($fromNum, $toNum) {
                        $rangeQuery->whereBetween('from_number', [$fromNum, $toNum])
                            ->orWhereBetween('to_number', [$fromNum, $toNum])
                            ->orWhere(function ($overlapQuery) use ($fromNum, $toNum) {
                                $overlapQuery->where('from_number', '<=', $fromNum)
                                    ->where('to_number', '>=', $toNum);
                            });
                    });
            })
            ->exists();

        if ($existingImport) {
            return back()->withErrors(['from_number' => 'Dải số serial này đã tồn tại trong hệ thống.'])->withInput();
        }

        // Tạo bản ghi import
        $import = DiplomaBlankImport::create([
            'type_id' => $request->type_id,
            'document_reference' => $request->document_reference,
            'issue_date' => $request->issue_date,
            'import_date' => now(),
            'total_quantity' => $totalQuantity,
            'prefix' => $request->prefix,
            'suffix' => $request->suffix,
            'from_number' => (string)$fromNum,
            'to_number' => (string)$toNum,
            'status' => ImportStatus::PENDING->value,
            'processed_count' => 0,
        ]);

        // Note: Import sẽ được xử lý tự động bởi scheduled job (chạy mỗi phút)
        // Job ProcessDiplomaBlankImportJob sẽ tạo các DiplomaBlank records

        return redirect()->route('diploma-blank-management')
            ->with('success', "Đã tạo lệnh nhập phôi thành công. ID: {$import->id}, Số lượng: {$totalQuantity} phôi. Hệ thống sẽ tự động xử lý trong vài phút.");
    }

    /**
     * Xem chi tiết import - chuyển hướng đến trang hiển thị DiplomaBlank thuộc import này
     */
    public function show(DiplomaBlankImport $import)
    {
        // Chuyển hướng đến trang diploma-blanks-list với import ID
        return redirect()->route('diploma-blanks.list-by-import', $import->id);
    }

    /**
     * Bắt đầu xử lý import (PENDING -> PROCESSING)
     */
    public function start(DiplomaBlankImport $import): JsonResponse
    {
        if ($import->status !== ImportStatus::PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể bắt đầu xử lý import đang ở trạng thái "Chờ xử lý".'
            ], 400);
        }

        $import->update([
            'status' => ImportStatus::PROCESSING->value,
            'started_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã bắt đầu xử lý import. Job sẽ được schedule xử lý trong background.',
            'data' => [
                'status' => $import->status->getLabel(),
                'status_class' => $this->getStatusClass($import->status),
            ]
        ]);
    }

    /**
     * Tạm dừng xử lý import (PROCESSING -> PENDING)
     */
    public function pause(DiplomaBlankImport $import): JsonResponse
    {
        if ($import->status !== ImportStatus::PROCESSING) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể tạm dừng import đang ở trạng thái "Đang xử lý".'
            ], 400);
        }

        $import->update([
            'status' => ImportStatus::PENDING->value,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã tạm dừng xử lý import.',
            'data' => [
                'status' => $import->status->getLabel(),
                'status_class' => $this->getStatusClass($import->status),
            ]
        ]);
    }

    /**
     * Thử lại import bị lỗi (FAILED -> PENDING)
     */
    public function retry(DiplomaBlankImport $import): JsonResponse
    {
        if ($import->status !== ImportStatus::FAILED) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể thử lại import đang ở trạng thái "Lỗi".'
            ], 400);
        }

        $import->update([
            'status' => ImportStatus::PENDING->value,
            'error_message' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã đặt lại import để thử lại.',
            'data' => [
                'status' => $import->status->getLabel(),
                'status_class' => $this->getStatusClass($import->status),
            ]
        ]);
    }

    /**
     * Xóa import
     */
    public function destroy(DiplomaBlankImport $import): JsonResponse
    {
        if ($import->status === ImportStatus::PROCESSING) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa import đang được xử lý. Vui lòng tạm dừng trước.'
            ], 400);
        }

        $import->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa import thành công.'
        ]);
    }

    /**
     * Lấy thống kê tổng quan
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total' => DiplomaBlankImport::count(),
            'pending' => DiplomaBlankImport::where('status', ImportStatus::PENDING->value)->count(),
            'processing' => DiplomaBlankImport::where('status', ImportStatus::PROCESSING->value)->count(),
            'completed' => DiplomaBlankImport::where('status', ImportStatus::COMPLETED->value)->count(),
            'failed' => DiplomaBlankImport::where('status', ImportStatus::FAILED->value)->count(),
            'total_quantity' => DiplomaBlankImport::sum('total_quantity'),
            'processed_quantity' => DiplomaBlankImport::sum('processed_count'),
        ];

        return response()->json($stats);
    }

    /**
     * Đồng bộ trạng thái imports từ hệ thống background jobs
     */
    public function sync(): JsonResponse
    {
        // TODO: Implement sync logic with background job system

        return response()->json([
            'success' => true,
            'message' => 'Đã đồng bộ trạng thái imports.',
            'synced_count' => 0
        ]);
    }

    /**
     * Cập nhật thông tin import - đặt về PROCESSING cho schedule xử lý
     */
    public function updateImport(Request $request, DiplomaBlankImport $import)
    {
        // Validate request
        $validated = $request->validate([
            'prefix' => 'nullable|string|max:10',
            'suffix' => 'nullable|string|max:10',
            'from_number' => 'required|string|max:20',
            'to_number' => 'required|string|max:20',
        ]);

        try {
            // Chỉ cho phép update import đã hoàn thành
            if ($import->status !== ImportStatus::COMPLETED) {
                return back()->with('error', 'Chỉ có thể cập nhật import đã hoàn thành!');
            }

            // Kiểm tra xem có thay đổi gì không
            $hasChanges = (
                ($import->prefix ?? '') !== ($validated['prefix'] ?? '') ||
                ($import->suffix ?? '') !== ($validated['suffix'] ?? '') ||
                $import->from_number !== $validated['from_number'] ||
                $import->to_number !== $validated['to_number']
            );

            if (!$hasChanges) {
                return back()->with('error', 'Không có thay đổi nào để cập nhật!');
            }

            // Lưu thông tin update vào import và đặt trạng thái về PROCESSING
            // Schedule sẽ tự động phát hiện và dispatch UpdateDiplomaBlankImportJob
            $import->update([
                'prefix' => $validated['prefix'],
                'suffix' => $validated['suffix'],
                'from_number' => $validated['from_number'],
                'to_number' => $validated['to_number'],
                'status' => ImportStatus::PROCESSING->value,
                'started_at' => now(),
                'error_message' => null, // Clear previous errors
            ]);

            return back()->with('success', 'Đã cập nhật thông tin Import #' . $import->id . ' và đặt trạng thái PROCESSING. Schedule sẽ tự động xử lý cập nhật trong vài phút tới.');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Kiểm tra trạng thái cập nhật import
     */
    public function checkUpdateStatus(DiplomaBlankImport $import): JsonResponse
    {
        return response()->json([
            'success' => true,
            'import' => [
                'id' => $import->id,
                'status' => $import->status->value,
                'status_label' => $import->getStatusText(),
                'status_class' => $import->getStatusBadgeClass(),
                'processed_count' => $import->processed_count,
                'total_quantity' => $import->total_quantity,
                'completion_percentage' => $import->getCompletionPercentage(),
                'last_processed_serial' => $import->last_processed_serial,
                'error_message' => $import->error_message,
                'updated_at' => $import->updated_at?->format('d/m/Y H:i:s')
            ]
        ]);
    }



    /**
     * Helper method để lấy CSS class cho status
     */
    private function getStatusClass(ImportStatus $status): string
    {
        return match ($status) {
            ImportStatus::PENDING => 'status-pending',
            ImportStatus::PROCESSING => 'status-processing',
            ImportStatus::COMPLETED => 'status-completed',
            ImportStatus::FAILED => 'status-failed',
        };
    }
}