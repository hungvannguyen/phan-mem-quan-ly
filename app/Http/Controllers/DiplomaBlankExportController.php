<?php

namespace App\Http\Controllers;

use App\Models\DiplomaBlank;
use App\Models\DiplomaBlankExport;
use App\Models\DiplomaBlankType;
use App\Enums\DiplomaBlankStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DiplomaBlankExportController extends Controller
{
    public function create()
    {
        $diplomaBlankTypes = DiplomaBlankType::all();
        return view('components.diploma-blank-exports.create', compact('diplomaBlankTypes'));
    }

    public function getSuggestedRanges(Request $request)
    {
        $request->validate([
            'type_id' => 'required|integer|exists:diploma_blank_types,type_id',
            'quantity' => 'required|integer|min:1',
            'course' => 'nullable|string',
            'graduation_year' => 'nullable|integer',
        ]);

        $typeId = $request->type_id;
        $quantity = $request->quantity;

        // Lấy toàn bộ phôi của loại (để sắp xếp chính xác theo số trong serial)
        $allBlanks = DiplomaBlank::where('type_id', $typeId)
            ->get()
            ->toArray();

        // Sắp xếp theo logic: prefix -> numeric value -> suffix
        usort($allBlanks, function ($a, $b) {
            $serialA = isset($a['serial_number']) ? $a['serial_number'] : '';
            $serialB = isset($b['serial_number']) ? $b['serial_number'] : '';
            return $this->compareSerials($serialA, $serialB);
        });

        // Đếm tổng phôi khả dụng (InStock)
        $availableCount = collect($allBlanks)->where('status', DiplomaBlankStatus::IN_STOCK->value)->count();

        if ($availableCount < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "Không đủ phôi trong kho. Yêu cầu: {$quantity}, Có sẵn: " . $availableCount,
                'available_count' => $availableCount
            ]);
        }

        // Lấy danh sách N phôi hợp lệ, chỉ lấy phôi IN_STOCK
        $taken = [];
        $damaged = [];
        $issued = [];
        $recalled = [];

        foreach ($allBlanks as $row) {
            if (count($taken) >= $quantity) break;

            if ($row['status'] === DiplomaBlankStatus::IN_STOCK->value) {
                $taken[] = $row['serial_number'];
            } else {
                // Ghi nhận các phôi không khả dụng để thông báo
                switch ($row['status']) {
                    case DiplomaBlankStatus::DAMAGED->value:
                        $damaged[] = $row['serial_number'];
                        break;
                    case DiplomaBlankStatus::ISSUED->value:
                        $issued[] = $row['serial_number'];
                        break;
                    case DiplomaBlankStatus::RECALLED->value:
                        $recalled[] = $row['serial_number'];
                        break;
                }
            }
        }

        // Nếu chưa đủ (dù đã kiểm tra trước) xử lý an toàn
        if (count($taken) < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Không đủ phôi khả dụng sau khi bỏ qua phôi hỏng.',
                'available_count' => $availableCount
            ]);
        }

        // Gom các phôi đã lấy thành dải liên tục (tách khi gặp khoảng không liên tục)
        $ranges = $this->rangesFromSerialList($taken);

        return response()->json([
            'success' => true,
            'ranges' => $ranges,
            'damaged_serials' => $damaged,
            'issued_serials' => $issued,
            'recalled_serials' => $recalled,
            'total_quantity' => $quantity,
            'available_count' => $availableCount
        ]);
    }

    private function generateContinuousRanges($blanks)
    {
        $ranges = [];
        $currentRange = null;
        $previousSerial = null;

        foreach ($blanks as $blank) {
            $currentSerial = $blank->serial_number;

            if ($currentRange === null) {
                // Bắt đầu dải mới
                $currentRange = [
                    'from_serial' => $currentSerial,
                    'to_serial' => $currentSerial,
                    'count' => 1
                ];
            } else {
                // Kiểm tra xem serial hiện tại có liên tục với serial trước không
                if ($this->isConsecutive($previousSerial, $currentSerial)) {
                    // Mở rộng dải hiện tại
                    $currentRange['to_serial'] = $currentSerial;
                    $currentRange['count']++;
                } else {
                    // Kết thúc dải hiện tại và bắt đầu dải mới
                    $ranges[] = $currentRange;
                    $currentRange = [
                        'from_serial' => $currentSerial,
                        'to_serial' => $currentSerial,
                        'count' => 1
                    ];
                }
            }

            $previousSerial = $currentSerial;
        }

        // Thêm dải cuối cùng
        if ($currentRange !== null) {
            $ranges[] = $currentRange;
        }

        return $ranges;
    }

    private function isConsecutive($serial1, $serial2)
    {
        // Safety checks
        if (empty($serial1) || empty($serial2)) {
            return false;
        }

        // Hỗ trợ định dạng: prefixletters + digits (có thể có leading zeros) + suffixletters
        preg_match('/^([A-Za-z]*)(0*\d+)([A-Za-z]*)$/', $serial1, $m1);
        preg_match('/^([A-Za-z]*)(0*\d+)([A-Za-z]*)$/', $serial2, $m2);

        if (count($m1) < 4 || count($m2) < 4) {
            return false;
        }

        $prefix1 = isset($m1[1]) ? $m1[1] : '';
        $num1 = isset($m1[2]) ? intval($m1[2]) : 0;
        $suffix1 = isset($m1[3]) ? $m1[3] : '';

        $prefix2 = isset($m2[1]) ? $m2[1] : '';
        $num2 = isset($m2[2]) ? intval($m2[2]) : 0;
        $suffix2 = isset($m2[3]) ? $m2[3] : '';

        return $prefix1 === $prefix2 && $suffix1 === $suffix2 && ($num2 === $num1 + 1);
    }

    private function compareSerials(string $a, string $b): int
    {
        // Safety checks
        if (empty($a) && empty($b)) return 0;
        if (empty($a)) return -1;
        if (empty($b)) return 1;
        if ($a === $b) return 0;

        preg_match('/^([A-Za-z]*)(0*\d+)([A-Za-z]*)$/', $a, $ma);
        preg_match('/^([A-Za-z]*)(0*\d+)([A-Za-z]*)$/', $b, $mb);

        // Nếu không parse được, fallback to string compare
        if (count($ma) < 4 || count($mb) < 4) {
            return strcmp($a, $b);
        }

        // Compare prefix - với kiểm tra tồn tại key
        $prefix1 = isset($ma[1]) ? $ma[1] : '';
        $prefix2 = isset($mb[1]) ? $mb[1] : '';
        if ($prefix1 !== $prefix2) {
            return strcmp($prefix1, $prefix2);
        }

        // Compare numeric value - với kiểm tra tồn tại key
        $num1 = isset($ma[2]) ? intval($ma[2]) : 0;
        $num2 = isset($mb[2]) ? intval($mb[2]) : 0;
        if ($num1 !== $num2) {
            return $num1 < $num2 ? -1 : 1;
        }

        // If numeric equal, shorter leading-zero length comes first
        $len1 = isset($ma[2]) ? strlen($ma[2]) : 0;
        $len2 = isset($mb[2]) ? strlen($mb[2]) : 0;
        if ($len1 !== $len2) {
            return $len1 < $len2 ? -1 : 1;
        }

        // Compare suffix - với kiểm tra tồn tại key
        $suffix1 = isset($ma[3]) ? $ma[3] : '';
        $suffix2 = isset($mb[3]) ? $mb[3] : '';
        if ($suffix1 !== $suffix2) {
            return strcmp($suffix1, $suffix2);
        }

        return 0;
    }

    /**
     * Gom danh sách serial (đã sắp xếp) thành các dải liên tục
     */
    private function rangesFromSerialList(array $serials): array
    {
        $ranges = [];
        $current = null;
        $prev = null;

        foreach ($serials as $s) {
            if ($current === null) {
                $current = ['from_serial' => $s, 'to_serial' => $s, 'count' => 1];
            } else {
                if ($this->isConsecutive($prev, $s)) {
                    $current['to_serial'] = $s;
                    $current['count']++;
                } else {
                    $ranges[] = $current;
                    $current = ['from_serial' => $s, 'to_serial' => $s, 'count' => 1];
                }
            }

            $prev = $s;
        }

        if ($current !== null) {
            $ranges[] = $current;
        }

        return $ranges;
    }

    public function validateCustomRange(Request $request)
    {
        $request->validate([
            'type_id' => 'required|integer|exists:diploma_blank_types,type_id',
            'from_serial' => 'required|string',
            'to_serial' => 'required|string',
        ]);

        $typeId = $request->type_id;
        $fromSerial = $request->from_serial;
        $toSerial = $request->to_serial;

        // Kiểm tra định dạng serial
        if (!$this->validateSerialFormat($fromSerial) || !$this->validateSerialFormat($toSerial)) {
            return response()->json([
                'success' => false,
                'message' => 'Định dạng serial không hợp lệ'
            ]);
        }

        // Kiểm tra từ serial <= đến serial
        if (!$this->isSerialRangeValid($fromSerial, $toSerial)) {
            return response()->json([
                'success' => false,
                'message' => 'Serial bắt đầu phải nhỏ hơn hoặc bằng serial kết thúc'
            ]);
        }

        // Lấy danh sách serial trong khoảng
        $serials = $this->getSerialRange($fromSerial, $toSerial);

        // Kiểm tra tính khả dụng của các serial
        $availableSerials = DiplomaBlank::where('type_id', $typeId)
            ->whereIn('serial_number', $serials)
            ->where('status', DiplomaBlankStatus::IN_STOCK)
            ->pluck('serial_number')
            ->toArray();

        $unavailableSerials = array_diff($serials, $availableSerials);

        if (!empty($unavailableSerials)) {
            return response()->json([
                'success' => false,
                'message' => 'Một số serial không khả dụng: ' . implode(', ', $unavailableSerials),
                'unavailable_serials' => $unavailableSerials
            ]);
        }

        return response()->json([
            'success' => true,
            'serials' => $serials,
            'count' => count($serials)
        ]);
    }

    private function validateSerialFormat($serial)
    {
        return preg_match('/^[A-Za-z]*\d+$/', $serial);
    }

    private function isSerialRangeValid($fromSerial, $toSerial)
    {
        preg_match('/^([A-Za-z]*)(0*\d+)([A-Za-z]*)$/', $fromSerial, $matches1);
        preg_match('/^([A-Za-z]*)(0*\d+)([A-Za-z]*)$/', $toSerial, $matches2);

        if (count($matches1) < 4 || count($matches2) < 4) {
            return false;
        }

        $prefix1 = isset($matches1[1]) ? $matches1[1] : '';
        $number1 = isset($matches1[2]) ? intval($matches1[2]) : 0;
        $suffix1 = isset($matches1[3]) ? $matches1[3] : '';
        $prefix2 = isset($matches2[1]) ? $matches2[1] : '';
        $number2 = isset($matches2[2]) ? intval($matches2[2]) : 0;
        $suffix2 = isset($matches2[3]) ? $matches2[3] : '';

        return $prefix1 === $prefix2 && $suffix1 === $suffix2 && $number1 <= $number2;
    }

    private function getSerialRange($fromSerial, $toSerial)
    {
        preg_match('/^([A-Za-z]*)(0*\d+)([A-Za-z]*)$/', $fromSerial, $matches1);
        preg_match('/^([A-Za-z]*)(0*\d+)([A-Za-z]*)$/', $toSerial, $matches2);

        // Safety checks
        if (count($matches1) < 4 || count($matches2) < 4) {
            return [];
        }

        $prefix = isset($matches1[1]) ? $matches1[1] : '';
        $fromNumber = isset($matches1[2]) ? intval($matches1[2]) : 0;
        $toNumber = isset($matches2[2]) ? intval($matches2[2]) : 0;
        $suffix = isset($matches1[3]) ? $matches1[3] : '';

        $serials = [];
        for ($i = $fromNumber; $i <= $toNumber; $i++) {
            $serials[] = $prefix . $i . $suffix;
        }

        return $serials;
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_id' => 'required|integer|exists:diploma_blank_types,type_id',
            'course' => 'nullable|string|max:255',
            'graduation_year' => 'required|integer|min:2000|max:2100',
            'decision_number' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'ranges' => 'required|string', // JSON string from hidden input
            'notes' => 'nullable|string|max:1000',
        ]);

        // Parse ranges from JSON string
        $ranges = json_decode($request->ranges, true);
        if (!$ranges || !is_array($ranges)) {
            return redirect()->back()->with('error', 'Dữ liệu dải serial không hợp lệ.');
        }

        DB::beginTransaction();

        try {
            // Thu thập tất cả serial cần xuất
            $allSerials = [];
            foreach ($ranges as $range) {
                $rangeSerials = $this->getSerialRange($range['from_serial'], $range['to_serial']);
                $allSerials = array_merge($allSerials, $rangeSerials);
            }

            // Kiểm tra lại tính khả dụng
            $availableBlanks = DiplomaBlank::where('type_id', $request->type_id)
                ->whereIn('serial_number', $allSerials)
                ->where('status', DiplomaBlankStatus::IN_STOCK->value)
                ->lockForUpdate()
                ->get();

            if ($availableBlanks->count() !== count($allSerials)) {
                DB::rollback();
                return redirect()->back()->with('error', 'Một số phôi đã không còn khả dụng. Vui lòng thử lại.');
            }

            // Tạo bản ghi xuất phôi
            $export = DiplomaBlankExport::create([
                'type_id' => $request->type_id,
                'course' => $request->course,
                'graduation_year' => $request->graduation_year,
                'decision_number' => $request->decision_number,
                'issue_date' => $request->issue_date,
                'quantity_requested' => $request->quantity,
                'quantity_exported' => count($allSerials),
                'export_date' => now(),
                'export_ranges' => $ranges,
                'notes' => $request->notes,
                'exported_by' => Auth::user()->user_id,
            ]);

            // Cập nhật trạng thái các phôi
            foreach ($availableBlanks as $blank) {
                $blank->update([
                    'status' => DiplomaBlankStatus::ISSUED->value,
                    'export_id' => $export->export_id,
                    'issue_date' => $request->issue_date,
                    'issue_reason' => "Xuất phôi theo QĐ {$request->decision_number}"
                ]);
            }

            DB::commit();

            return redirect()->route('diploma-blank-exports.show', $export->export_id)
                ->with('success', "Xuất phôi thành công! Đã xuất {$export->quantity_exported} phôi.");
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $exports = DiplomaBlankExport::with(['type', 'exportedBy'])
            ->orderBy('export_date', 'desc')
            ->paginate(20);

        return view('components.diploma-blank-exports.index', compact('exports'));
    }

    public function show($exportId)
    {
        $export = DiplomaBlankExport::with(['type', 'exportedBy', 'diplomaBlanks'])
            ->findOrFail($exportId);

        return view('components.diploma-blank-exports.show', compact('export'));
    }
}
