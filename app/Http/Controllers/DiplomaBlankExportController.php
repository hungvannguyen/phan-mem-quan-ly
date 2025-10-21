<?php

namespace App\Http\Controllers;

use App\Models\DiplomaBlank;
use App\Models\DiplomaBlankExport;
use App\Models\DiplomaBlankType;
use App\Enums\DiplomaBlankStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        // ✅ OPTIMIZED: Use indexes to quickly get status counts
        // Use raw SQL to avoid model casting enum issues
        $statusCounts = DB::select(
            'SELECT status, COUNT(*) as count FROM diploma_blanks WHERE type_id = ? GROUP BY status',
            [$typeId]
        );

        // Convert to associative array
        $statusCountsArray = [];
        foreach ($statusCounts as $row) {
            $statusCountsArray[$row->status] = $row->count;
        }

        $availableCount = $statusCountsArray['InStock'] ?? 0;

        if ($availableCount < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "Không đủ phôi trong kho. Yêu cầu: {$quantity}, Có sẵn: " . $availableCount,
                'available_count' => $availableCount,
                'status_summary' => [
                    'total' => array_sum($statusCountsArray),
                    'available' => $availableCount,
                    'issued_count' => $statusCountsArray['Issued'] ?? 0,
                    'damaged_count' => $statusCountsArray['Damaged'] ?? 0,
                    'recalled_count' => $statusCountsArray['Recalled'] ?? 0,
                ]
            ]);
        }

        // ✅ OPTIMIZED: Only fetch available blanks, ordered by serial
        $availableBlanks = DiplomaBlank::where('type_id', $typeId)
            ->where('status', DiplomaBlankStatus::IN_STOCK->value)
            ->select('serial_number')
            ->get()
            ->pluck('serial_number')
            ->toArray();

        // Sort by serial number logic
        usort($availableBlanks, function ($a, $b) {
            return $this->compareSerials($a, $b);
        });

        // Take only the requested quantity
        $taken = array_slice($availableBlanks, 0, $quantity);

        // Generate continuous ranges from taken serials
        $ranges = $this->rangesFromSerialList($taken);

        return response()->json([
            'success' => true,
            'ranges' => $ranges,
            'total_quantity' => $quantity,
            'available_count' => $availableCount,
            'status_summary' => [
                'total' => array_sum($statusCountsArray),
                'available' => $availableCount,
                'issued_count' => $statusCountsArray['Issued'] ?? 0,
                'damaged_count' => $statusCountsArray['Damaged'] ?? 0,
                'recalled_count' => $statusCountsArray['Recalled'] ?? 0,
            ]
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
            ->where('status', DiplomaBlankStatus::IN_STOCK->value)
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
        $fromNumberStr = isset($matches1[2]) ? $matches1[2] : '0';
        $toNumberStr = isset($matches2[2]) ? $matches2[2] : '0';
        $suffix = isset($matches1[3]) ? $matches1[3] : '';

        // Convert to integers for range iteration
        $fromNumber = intval($fromNumberStr);
        $toNumber = intval($toNumberStr);

        // Determine the zero-padding width from the original format
        $paddingWidth = strlen($fromNumberStr);

        $serials = [];
        for ($i = $fromNumber; $i <= $toNumber; $i++) {
            // ✅ Preserve leading zeros with proper padding
            $formattedNumber = str_pad($i, $paddingWidth, '0', STR_PAD_LEFT);
            $serials[] = $prefix . $formattedNumber . $suffix;
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

            // Batch check availability với SELECT FOR UPDATE để lock records
            $availableRecords = DiplomaBlank::where('type_id', $request->type_id)
                ->whereIn('serial_number', $allSerials)
                ->where('status', DiplomaBlankStatus::IN_STOCK->value)
                ->lockForUpdate() // Lock để tránh race condition
                ->get(['diploma_blank_id', 'serial_number', 'status']);

            if ($availableRecords->count() !== count($allSerials)) {
                DB::rollback();

                // Detailed error info for debugging
                $availableSerials = $availableRecords->pluck('serial_number')->toArray();
                $missingSerials = array_diff($allSerials, $availableSerials);

                Log::warning('DiplomaBlank export failed - unavailable serials', [
                    'requested_serials' => $allSerials,
                    'available_count' => $availableRecords->count(),
                    'missing_serials' => array_slice($missingSerials, 0, 10), // Log first 10 only
                    'user_id' => Auth::id(),
                    'type_id' => $request->type_id
                ]);

                return redirect()->back()->with('error', 'Một số phôi đã không còn khả dụng. Vui lòng thử lại.');
            }

            // Tạo bản ghi xuất phôi trước khi cập nhật blanks
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

            // ✅ BATCH UPDATE sử dụng locked records IDs để đảm bảo consistency
            $lockedIds = $availableRecords->pluck('diploma_blank_id')->toArray();
            $actualUpdated = 0;
            $chunkSize = 500; // Chunk size để tránh quá tải database
            $chunks = array_chunk($lockedIds, $chunkSize);

            foreach ($chunks as $chunk) {
                $updated = DiplomaBlank::whereIn('diploma_blank_id', $chunk)
                    ->update([
                        'status' => DiplomaBlankStatus::ISSUED->value,
                        'export_id' => $export->export_id,
                        'issue_date' => $request->issue_date,
                        'issue_reason' => "Xuất phôi theo QĐ {$request->decision_number}",
                        'updated_at' => now()
                    ]);

                $actualUpdated += $updated;

                // Nhỏ delay giữa các batch để không overwhelm database với large exports
                if (count($chunks) > 1) {
                    usleep(1000); // 1ms delay between chunks
                }
            }

            // Verify actual updated count matches expected
            if ($actualUpdated !== count($lockedIds)) {
                Log::error('DiplomaBlank batch update count mismatch', [
                    'expected' => count($lockedIds),
                    'actual_updated' => $actualUpdated,
                    'export_id' => $export->export_id
                ]);
            }

            $export->update(['quantity_exported' => $actualUpdated]);

            DB::commit();

            return redirect()->route('diploma-blank-exports.show', $export->export_id)
                ->with('success', "Xuất phôi thành công! Đã xuất {$actualUpdated} phôi văn bằng.");
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
