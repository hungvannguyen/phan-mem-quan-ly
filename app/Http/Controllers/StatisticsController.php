<?php

namespace App\Http\Controllers;

use App\Models\DiplomaBlank;
use App\Models\DiplomaBlankType;
use App\Models\Degree;
use App\Enums\DiplomaBlankStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use SaKanjo\EasyMetrics\Metrics\Value;
use SaKanjo\EasyMetrics\Metrics\Doughnut;
use SaKanjo\EasyMetrics\Metrics\Trend;

class StatisticsController extends Controller
{
    public function index()
    {
        // Get data for the dashboard
        $generalStatistics = $this->getGeneralStatistics();
        $statusDistribution = $this->getStatusDistribution();
        $typeDistribution = $this->getTypeDistribution();
        $issuedTrend = $this->getIssuedTrend();
        $recalledTrend = $this->getRecalledTrend();
        $monthlyComparison = $this->getMonthlyComparison();

        // Get majors for filter
        $majors = \App\Models\Major::all();

        return view('home', compact(
            'generalStatistics',
            'statusDistribution',
            'typeDistribution',
            'issuedTrend',
            'recalledTrend',
            'monthlyComparison',
            'majors'
        ));
    }

    /**
     * Get general statistics for dashboard cards
     */
    private function getGeneralStatistics($timeRange = 30)
    {
        // Use manual counts for accurate totals (not filtered by time range)
        $totalBlanks = DiplomaBlank::count();
        $availableBlanks = DiplomaBlank::where('status', DiplomaBlankStatus::IN_STOCK->value)->count();
        $issuedBlanks = DiplomaBlank::where('status', DiplomaBlankStatus::ISSUED->value)->count();
        $recalledBlanks = DiplomaBlank::where('status', DiplomaBlankStatus::RECALLED->value)->count();
        $damagedBlanks = DiplomaBlank::where('status', DiplomaBlankStatus::DAMAGED->value)->count();

        // Calculate growth rates for issued and recalled blanks using Laravel Easy Metrics
        try {
            $issuedBlanksQuery = DiplomaBlank::where('status', DiplomaBlankStatus::ISSUED->value);
            [, $issuedGrowth] = Value::make($issuedBlanksQuery)
                ->range($timeRange)
                ->withGrowthRate()
                ->count();

            $recalledBlanksQuery = DiplomaBlank::where('status', DiplomaBlankStatus::RECALLED->value);
            [, $recalledGrowth] = Value::make($recalledBlanksQuery)
                ->range($timeRange)
                ->withGrowthRate()
                ->count();
        } catch (\Exception $e) {
            // Fallback if growth calculation fails
            $issuedGrowth = "+0%";
            $recalledGrowth = "+0%";
        }

        return [
            'total_blanks' => $totalBlanks,
            'available_blanks' => $availableBlanks,
            'issued_blanks' => $issuedBlanks,
            'recalled_blanks' => $recalledBlanks,
            'damaged_blanks' => $damagedBlanks,
            'issued_growth' => $issuedGrowth,
            'recalled_growth' => $recalledGrowth,
        ];
    }

    /**
     * Get distribution by status for doughnut chart
     */
    private function getStatusDistribution($blankType = null, $graduationYear = null)
    {
        $query = DiplomaBlank::query();

        if ($blankType) {
            $query->where('type_id', $blankType);
        }

        if ($graduationYear) {
            $query->join('degrees', 'degrees.diploma_blank_id', '=', 'diploma_blanks.diploma_blank_id')
                ->where('degrees.graduation_year', $graduationYear);
        }

        // Use manual query to get accurate counts without range filtering
        $statusCounts = $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statusCounts as $statusCount) {
            $statusValue = $statusCount->status instanceof DiplomaBlankStatus ? $statusCount->status->value : $statusCount->status;

            $label = match ($statusValue) {
                'InStock' => 'Trong kho',
                'Issued' => 'Đã cấp',
                'Recalled' => 'Đã thu hồi',
                'Damaged' => 'Hư hỏng',
                'IN_STOCK' => 'Trong kho',  // Fallback for enum values
                'ISSUED' => 'Đã cấp',
                'RECALLED' => 'Đã thu hồi',
                'DAMAGED' => 'Hư hỏng',
                default => $statusValue
            };

            // Assign specific colors for each status
            $color = match ($label) {
                'Trong kho' => '#10B981',     // Green
                'Đã cấp' => '#3B82F6',       // Blue
                'Đã thu hồi' => '#F59E0B',   // Yellow/Orange
                'Hư hỏng' => '#EF4444',      // Red
                default => '#6B7280'         // Gray
            };

            $labels[] = $label;
            $data[] = $statusCount->count;
            $colors[] = $color;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors
        ];
    }

    /**
     * Get distribution by diploma blank type
     */
    private function getTypeDistribution()
    {
        // Use traditional query to avoid Laravel Easy Metrics complications with joins
        $data = DiplomaBlank::select('diploma_blank_types.type_name', DB::raw('count(*) as count'))
            ->join('diploma_blank_types', 'diploma_blanks.type_id', '=', 'diploma_blank_types.type_id')
            ->groupBy('diploma_blank_types.type_name')
            ->get();

        $labels = $data->pluck('type_name')->toArray();
        $counts = $data->pluck('count')->toArray();

        return [
            'labels' => $labels,
            'data' => $counts
        ];
    }

    /**
     * Get trend of issued diploma blanks by month
     */
    private function getIssuedTrend($blankType = null, $graduationYear = null)
    {
        $query = DiplomaBlank::where('status', DiplomaBlankStatus::ISSUED->value);

        if ($blankType) {
            $query->where('type_id', $blankType);
        }

        if ($graduationYear) {
            $query->join('degrees', 'degrees.diploma_blank_id', '=', 'diploma_blanks.diploma_blank_id')
                ->where('degrees.graduation_year', $graduationYear);
        }

        [$labels, $data] = Trend::make($query)
            ->range(365)
            ->countByMonths();

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get trend of recalled diploma blanks by month
     */
    private function getRecalledTrend($blankType = null, $graduationYear = null)
    {
        $query = DiplomaBlank::where('status', DiplomaBlankStatus::RECALLED->value);

        if ($blankType) {
            $query->where('type_id', $blankType);
        }

        if ($graduationYear) {
            $query->join('degrees', 'degrees.diploma_blank_id', '=', 'diploma_blanks.diploma_blank_id')
                ->where('degrees.graduation_year', $graduationYear);
        }

        [$labels, $data] = Trend::make($query)
            ->range(365)
            ->countByMonths();

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get monthly comparison of issued vs recalled blanks
     */
    private function getMonthlyComparison($graduationYear = null)
    {
        $issuedQuery = DiplomaBlank::where('status', DiplomaBlankStatus::ISSUED->value);
        $recalledQuery = DiplomaBlank::where('status', DiplomaBlankStatus::RECALLED->value);

        if ($graduationYear) {
            $issuedQuery->join('degrees', 'degrees.diploma_blank_id', '=', 'diploma_blanks.diploma_blank_id')
                ->where('degrees.graduation_year', $graduationYear);
            $recalledQuery->join('degrees', 'degrees.diploma_blank_id', '=', 'diploma_blanks.diploma_blank_id')
                ->where('degrees.graduation_year', $graduationYear);
        }

        [$issuedLabels, $issuedData] = Trend::make($issuedQuery)
            ->range(365)
            ->countByMonths();

        [$recalledLabels, $recalledData] = Trend::make($recalledQuery)
            ->range(365)
            ->countByMonths();

        return [
            'labels' => $issuedLabels,
            'issued_data' => $issuedData,
            'recalled_data' => $recalledData
        ];
    }

    /**
     * Get recent activities for the dashboard
     */
    private function getRecentActivities()
    {
        $recentIssued = DiplomaBlank::where('status', DiplomaBlankStatus::ISSUED->value)
            ->with(['degree.student', 'type'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        $recentRecalled = DiplomaBlank::where('status', DiplomaBlankStatus::RECALLED->value)
            ->with(['degree.student', 'type'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'recent_issued' => $recentIssued,
            'recent_recalled' => $recentRecalled
        ];
    }

    /**
     * API endpoint for filtered statistics
     */
    public function getFilteredStatistics(Request $request)
    {
        $blankType = $request->get('blank_type');
        $graduationYear = $request->get('graduation_year');
        $timeRange = $request->get('time_range', 30);

        return response()->json([
            'status_distribution' => $this->getStatusDistribution($blankType, $graduationYear),
            'issued_trend' => $this->getIssuedTrend($blankType, $graduationYear),
            'recalled_trend' => $this->getRecalledTrend($blankType, $graduationYear),
            'monthly_comparison' => $this->getMonthlyComparison($graduationYear),
            'general_statistics' => $this->getGeneralStatistics($timeRange)
        ]);
    }

    /**
     * Display statistics page
     */
    public function statisticsPage()
    {
        $majors = \App\Models\Major::all();
        return view('statistics.index', compact('majors'));
    }

    /**
     * Get diploma statistics with filters
     */
    public function getDiplomaStatistics(Request $request)
    {
        $query = Degree::query()->with(['student', 'major']);

        // Apply filters
        if ($request->filled('graduation_year')) {
            $query->where('graduation_year', $request->graduation_year);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('granting_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('granting_date', '<=', $request->end_date);
        }

        if ($request->filled('degree_type')) {
            $query->where('degree_type', $request->degree_type);
        }

        if ($request->filled('major')) {
            $query->where('major_id', $request->major);
        }

        if ($request->filled('gender')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('gender', $request->gender);
            });
        }

        if ($request->filled('ranking')) {
            $query->where('ranking', $request->ranking);
        }

        if ($request->filled('training_type')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('training_type', $request->training_type);
            });
        }

        // Get total count
        $total = $query->count();

        // Get statistics by different criteria
        $byTypeRaw = $this->getStatsByColumn($query, 'degree_type');

        // Translate degree types to Vietnamese
        $byType = [
            'labels' => array_map(function ($type) {
                return $this->translateDegreeType($type);
            }, $byTypeRaw['labels']),
            'values' => $byTypeRaw['values']
        ];

        $byMajor = $this->getStatsByRelation($query, 'major', 'major_name');
        $byRanking = $this->getStatsByColumn($query, 'ranking');
        $byYear = $this->getStatsByColumn($query, 'graduation_year');

        // Gender statistics
        $maleCount = (clone $query)->whereHas('student', function ($q) {
            $q->where('gender', 'Male');
        })->count();
        $femaleCount = (clone $query)->whereHas('student', function ($q) {
            $q->where('gender', 'Female');
        })->count();

        // Ranking counts
        $excellentCount = (clone $query)->where('ranking', 'Xuất sắc')->count();
        $goodCount = (clone $query)->where('ranking', 'Giỏi')->count();

        // Major count
        $majorCount = (clone $query)->distinct('major_id')->count('major_id');

        // Training type statistics
        $byTrainingType = $this->getTrainingTypeStats($query);

        // Detailed breakdown
        $details = [];

        // Add type breakdown
        foreach ($byType['labels'] as $index => $label) {
            $details[] = [
                'criteria' => 'Loại bằng',
                'value' => $this->translateDegreeType($label),
                'count' => $byType['values'][$index],
                'percentage' => $total > 0 ? round(($byType['values'][$index] / $total) * 100, 2) : 0
            ];
        }

        // Add gender breakdown
        $details[] = [
            'criteria' => 'Giới tính',
            'value' => 'Nam',
            'count' => $maleCount,
            'percentage' => $total > 0 ? round(($maleCount / $total) * 100, 2) : 0
        ];
        $details[] = [
            'criteria' => 'Giới tính',
            'value' => 'Nữ',
            'count' => $femaleCount,
            'percentage' => $total > 0 ? round(($femaleCount / $total) * 100, 2) : 0
        ];

        return response()->json([
            'total' => $total,
            'male_count' => $maleCount,
            'female_count' => $femaleCount,
            'excellent_count' => $excellentCount,
            'good_count' => $goodCount,
            'major_count' => $majorCount,
            'by_type' => $byType,
            'by_major' => $byMajor,
            'by_ranking' => $byRanking,
            'by_year' => $byYear,
            'by_training_type' => $byTrainingType,
            'details' => $details
        ]);
    }

    /**
     * Get certificate statistics with filters
     */
    public function getCertificateStatistics(Request $request)
    {
        // Get certificates (degree_type = 'certificate')
        $query = Degree::query()
            ->where('degree_type', 'certificate')
            ->with(['student', 'major']);

        // Apply filters
        if ($request->filled('certificate_type')) {
            // Assuming certificate type is stored in notes or a specific field
            $query->where('notes', 'like', '%' . $request->certificate_type . '%');
        }

        if ($request->filled('start_date')) {
            $query->whereDate('granting_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('granting_date', '<=', $request->end_date);
        }

        $total = $query->count();

        // Count by certificate types (based on notes field)
        $languageCount = (clone $query)->where('notes', 'like', '%ngoại ngữ%')->count();
        $itCount = (clone $query)->where('notes', 'like', '%tin học%')->count();
        $vocationalCount = (clone $query)->where('notes', 'like', '%nghề%')->count();

        // Statistics by type
        $byType = [
            'labels' => ['Chứng chỉ ngoại ngữ', 'Chứng chỉ tin học', 'Chứng chỉ nghề', 'Khác'],
            'values' => [
                $languageCount,
                $itCount,
                $vocationalCount,
                $total - $languageCount - $itCount - $vocationalCount
            ]
        ];

        // Monthly trend
        $byMonth = $this->getMonthlyTrend($query);

        // Detailed breakdown
        $details = [];
        foreach ($byType['labels'] as $index => $label) {
            if ($byType['values'][$index] > 0) {
                $details[] = [
                    'type' => $label,
                    'count' => $byType['values'][$index],
                    'percentage' => $total > 0 ? round(($byType['values'][$index] / $total) * 100, 2) : 0
                ];
            }
        }

        return response()->json([
            'total' => $total,
            'language_count' => $languageCount,
            'it_count' => $itCount,
            'vocational_count' => $vocationalCount,
            'by_type' => $byType,
            'by_month' => $byMonth,
            'details' => $details
        ]);
    }

    /**
     * Export statistics report
     */
    public function exportStatistics(Request $request)
    {
        $type = $request->get('type', 'diplomas');

        if ($type === 'certificates') {
            return $this->exportCertificateReport($request);
        }

        return $this->exportDiplomaReport($request);
    }

    /**
     * Helper: Get statistics by column
     */
    private function getStatsByColumn($query, $column)
    {
        $stats = (clone $query)
            ->select($column, DB::raw('count(*) as count'))
            ->groupBy($column)
            ->get();

        return [
            'labels' => $stats->pluck($column)->toArray(),
            'values' => $stats->pluck('count')->toArray()
        ];
    }

    /**
     * Helper: Get statistics by relation
     */
    private function getStatsByRelation($query, $relation, $column)
    {
        $tableName = $relation === 'major' ? 'majors' : $relation . 's';
        $foreignKey = $relation . '_id';

        $stats = (clone $query)
            ->join($tableName, $tableName . '.' . $foreignKey, '=', 'degrees.' . $foreignKey)
            ->select($tableName . '.' . $column, DB::raw('count(*) as count'))
            ->groupBy($tableName . '.' . $column)
            ->get();

        return [
            'labels' => $stats->pluck($column)->toArray(),
            'values' => $stats->pluck('count')->toArray()
        ];
    }

    /**
     * Helper: Get monthly trend
     */
    private function getMonthlyTrend($query)
    {
        $stats = (clone $query)
            ->select(
                DB::raw('DATE_FORMAT(granting_date, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)
            ->get();

        return [
            'labels' => $stats->pluck('month')->toArray(),
            'values' => $stats->pluck('count')->toArray()
        ];
    }

    /**
     * Helper: Get training type statistics
     */
    private function getTrainingTypeStats($query)
    {
        $stats = (clone $query)
            ->join('students', 'students.student_id', '=', 'degrees.student_id')
            ->select('students.training_type', DB::raw('count(*) as count'))
            ->groupBy('students.training_type')
            ->get();

        return [
            'labels' => $stats->pluck('training_type')->toArray(),
            'values' => $stats->pluck('count')->toArray()
        ];
    }

    /**
     * Helper: Translate degree type
     */
    private function translateDegreeType($type)
    {
        return match ($type) {
            'bachelor' => 'Cử nhân',
            'master' => 'Thạc sĩ',
            'doctor' => 'Tiến sĩ',
            'certificate' => 'Chứng chỉ',
            default => $type
        };
    }

    /**
     * Export diploma report (placeholder)
     */
    private function exportDiplomaReport($request)
    {
        // TODO: Implement Excel export
        return response()->json(['message' => 'Export diploma report - Coming soon']);
    }

    /**
     * Export certificate report (placeholder)
     */
    private function exportCertificateReport($request)
    {
        // TODO: Implement Excel export
        return response()->json(['message' => 'Export certificate report - Coming soon']);
    }
}
