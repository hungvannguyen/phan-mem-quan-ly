<?php

namespace App\Http\Controllers;

use App\Models\DiplomaBlank;
use App\Models\DiplomaBlankType;
use App\Enums\DiplomaBlankStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class DiplomaBlankRecallController extends Controller
{
    /**
     * Hiển thị trang thu hồi phôi
     */
    public function index(): View
    {
        return view('components.diploma-blank-recalls.index');
    }

    /**
     * Hiển thị danh sách phôi đã thu hồi
     */
    public function recalledList(Request $request): View
    {
        $query = DiplomaBlank::with(['type', 'degree', 'degree.major'])
            ->where('status', DiplomaBlankStatus::RECALLED);

        // Lọc theo serial number
        if ($request->filled('serial_number')) {
            $query->where('serial_number', 'LIKE', '%' . $request->serial_number . '%');
        }

        // Lọc theo loại phôi
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }

        // Lọc theo ngày thu hồi
        if ($request->filled('recall_date_from')) {
            $query->whereDate('recall_date', '>=', $request->recall_date_from);
        }

        if ($request->filled('recall_date_to')) {
            $query->whereDate('recall_date', '<=', $request->recall_date_to);
        }

        // Lọc theo lý do thu hồi
        if ($request->filled('recall_reason')) {
            $query->where('recall_reason', 'LIKE', '%' . $request->recall_reason . '%');
        }

        // Sắp xếp theo ngày thu hồi mới nhất
        $query->orderBy('recall_date', 'desc');

        // Phân trang
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [5, 10, 15, 25, 50]) ? $perPage : 15;
        $recalledBlanks = $query->paginate($perPage)->appends($request->query());

        // Lấy danh sách loại phôi để hiển thị trong dropdown
        $diplomaBlankTypes = DiplomaBlankType::orderBy('type_name')->get();

        // Thống kê
        $statistics = [
            'total_recalled' => DiplomaBlank::where('status', DiplomaBlankStatus::RECALLED)->count(),
            'recalled_today' => DiplomaBlank::where('status', DiplomaBlankStatus::RECALLED)
                ->whereDate('recall_date', today())->count(),
            'recalled_this_month' => DiplomaBlank::where('status', DiplomaBlankStatus::RECALLED)
                ->whereMonth('recall_date', now()->month)
                ->whereYear('recall_date', now()->year)->count(),
        ];

        return view('components.diploma-blank-recalls.management', compact(
            'recalledBlanks',
            'diplomaBlankTypes',
            'statistics'
        ));
    }

    /**
     * Kiểm tra phôi theo serial number
     */
    public function checkSerial(Request $request): JsonResponse
    {
        $request->validate([
            'serial_number' => 'required|string',
        ]);

        $diplombBlank = DiplomaBlank::with(['type', 'degree', 'degree.major'])
            ->where('serial_number', $request->serial_number)
            ->first();

        if (!$diplombBlank) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phôi với số serial này trong hệ thống.'
            ], 404);
        }

        if ($diplombBlank->status !== DiplomaBlankStatus::ISSUED) {
            return response()->json([
                'success' => false,
                'message' => 'Phôi này không thể thu hồi. Chỉ có thể thu hồi các phôi đã cấp.',
                'current_status' => $diplombBlank->status->getLabel()
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tìm thấy phôi có thể thu hồi.',
            'data' => [
                'diploma_blank_id' => $diplombBlank->diploma_blank_id,
                'serial_number' => $diplombBlank->serial_number,
                'type_name' => $diplombBlank->type->type_name,
                'issue_date' => $diplombBlank->issue_date?->format('d/m/Y'),
                'status' => $diplombBlank->status->getLabel(),
                'degree_info' => $diplombBlank->degree ? [
                    'student_name' => $diplombBlank->degree->student_name,
                    'major_name' => $diplombBlank->degree->major->major_name ?? 'N/A',
                    'graduation_year' => $diplombBlank->degree->graduation_year,
                ] : null
            ]
        ]);
    }

    /**
     * Thực hiện thu hồi phôi
     */
    public function recall(Request $request): JsonResponse
    {
        $request->validate([
            'serial_number' => 'required|string',
            'recall_reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $diplomaBlank = DiplomaBlank::where('serial_number', $request->serial_number)
                ->where('status', DiplomaBlankStatus::ISSUED)
                ->lockForUpdate()
                ->first();

            if (!$diplomaBlank) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy phôi có thể thu hồi với số serial này.'
                ], 404);
            }

            // Cập nhật trạng thái và thông tin thu hồi
            $diplomaBlank->update([
                'status' => DiplomaBlankStatus::RECALLED,
                'recall_date' => now(),
                'recall_reason' => $request->recall_reason,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thu hồi phôi thành công.',
                'data' => [
                    'diploma_blank_id' => $diplomaBlank->diploma_blank_id,
                    'serial_number' => $diplomaBlank->serial_number,
                    'recall_date' => $diplomaBlank->recall_date->format('d/m/Y H:i:s'),
                    'recall_reason' => $diplomaBlank->recall_reason,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thu hồi phôi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Thống kê thu hồi phôi
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_recalled' => DiplomaBlank::where('status', DiplomaBlankStatus::RECALLED)->count(),
            'recalled_today' => DiplomaBlank::where('status', DiplomaBlankStatus::RECALLED)
                ->whereDate('recall_date', today())->count(),
            'recalled_this_week' => DiplomaBlank::where('status', DiplomaBlankStatus::RECALLED)
                ->whereBetween('recall_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'recalled_this_month' => DiplomaBlank::where('status', DiplomaBlankStatus::RECALLED)
                ->whereMonth('recall_date', now()->month)
                ->whereYear('recall_date', now()->year)->count(),
            'recalled_by_type' => DiplomaBlank::join('diploma_blank_types', 'diploma_blanks.type_id', '=', 'diploma_blank_types.type_id')
                ->where('diploma_blanks.status', DiplomaBlankStatus::RECALLED)
                ->selectRaw('diploma_blank_types.type_name, COUNT(*) as count')
                ->groupBy('diploma_blank_types.type_id', 'diploma_blank_types.type_name')
                ->get()
        ];

        return response()->json($stats);
    }
}
