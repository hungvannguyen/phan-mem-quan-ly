<?php

namespace App\Http\Controllers;

use App\Models\DiplomaBlank;
use App\Models\DiplomaBlankType;
use App\Models\Degree;
use App\Models\Major;
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
        $majors = Major::orderBy('major_name')->get();

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
     * Get diploma statistics with filters
     */
    public function getDiplomaStatistics(Request $request)
    {
        $query = Degree::query()
            ->with(['student', 'major', 'diplomaBlank.type'])
            ->whereNotNull('diploma_blank_id'); // Chỉ lấy bằng đã cấp

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

        if ($request->filled('major_id')) {
            $query->where('major_id', $request->major_id);
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
        $byTypeRaw = $this->getStatsByColumn(clone $query, 'degree_type');

        // Translate degree types to Vietnamese
        $byType = [
            'labels' => array_map(function ($type) {
                return $this->translateDegreeType($type);
            }, $byTypeRaw['labels']),
            'values' => $byTypeRaw['values']
        ];

        $byMajor = $this->getStatsByRelation(clone $query, 'major', 'major_name');
        $byRanking = $this->getStatsByColumn(clone $query, 'ranking');
        $byYear = $this->getStatsByColumn(clone $query, 'graduation_year');

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
        $byTrainingType = $this->getTrainingTypeStats(clone $query);

        // Detailed breakdown
        $details = [];

        // Add type breakdown
        foreach ($byType['labels'] as $index => $label) {
            if ($byType['values'][$index] > 0) {
                $details[] = [
                    'criteria' => 'Loại bằng',
                    'value' => $label,
                    'count' => $byType['values'][$index],
                    'percentage' => $total > 0 ? round(($byType['values'][$index] / $total) * 100, 2) : 0
                ];
            }
        }

        // Add gender breakdown
        if ($maleCount > 0) {
            $details[] = [
                'criteria' => 'Giới tính',
                'value' => 'Nam',
                'count' => $maleCount,
                'percentage' => $total > 0 ? round(($maleCount / $total) * 100, 2) : 0
            ];
        }
        if ($femaleCount > 0) {
            $details[] = [
                'criteria' => 'Giới tính',
                'value' => 'Nữ',
                'count' => $femaleCount,
                'percentage' => $total > 0 ? round(($femaleCount / $total) * 100, 2) : 0
            ];
        }

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

        // Only filter by diploma_blank_id if specifically requested
        // By default, show all certificates (issued and not issued)
        if ($request->filled('only_issued') && $request->only_issued == '1') {
            $query->whereNotNull('diploma_blank_id');
        }

        // Apply filters
        if ($request->filled('certificate_type')) {
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
        $languageCount = (clone $query)->where(function ($q) {
            $q->where('notes', 'like', '%ngoại ngữ%')
                ->orWhere('notes', 'like', '%tiếng%');
        })->count();

        $itCount = (clone $query)->where(function ($q) {
            $q->where('notes', 'like', '%tin học%')
                ->orWhere('notes', 'like', '%cntt%')
                ->orWhere('notes', 'like', '%ict%');
        })->count();

        $vocationalCount = (clone $query)->where('notes', 'like', '%nghề%')->count();
        $otherCount = $total - $languageCount - $itCount - $vocationalCount;

        // Statistics by type
        $byType = [
            'labels' => ['Chứng chỉ ngoại ngữ', 'Chứng chỉ tin học', 'Chứng chỉ nghề', 'Khác'],
            'values' => [
                $languageCount,
                $itCount,
                $vocationalCount,
                $otherCount
            ]
        ];

        // Monthly trend
        $byMonth = $this->getMonthlyTrend(clone $query);

        // Detailed breakdown
        $details = [];
        foreach ($byType['labels'] as $index => $label) {
            $details[] = [
                'type' => $label,
                'count' => $byType['values'][$index],
                'percentage' => $total > 0 ? round(($byType['values'][$index] / $total) * 100, 2) : 0
            ];
        }

        return response()->json([
            'total' => $total,
            'language_count' => $languageCount,
            'it_count' => $itCount,
            'vocational_count' => $vocationalCount,
            'other_count' => $otherCount,
            'by_type' => $byType,
            'by_month' => $byMonth,
            'details' => $details
        ]);
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

        // Get the base query builder and modify it to use table-qualified column names
        $baseQuery = clone $query;

        // Get the SQL with bindings to check existing WHERE conditions
        $sql = $baseQuery->toSql();
        $bindings = $baseQuery->getBindings();

        // Rebuild query with proper table qualification
        $stats = Degree::query()
            ->join($tableName, $tableName . '.' . $foreignKey, '=', 'degrees.' . $foreignKey)
            ->select($tableName . '.' . $column, DB::raw('count(*) as count'))
            ->whereNotNull('degrees.diploma_blank_id'); // Base condition

        // Apply same filters from original query, but with explicit table names
        $request = request();

        if ($request->filled('graduation_year')) {
            $stats->where('degrees.graduation_year', $request->graduation_year);
        }

        if ($request->filled('start_date')) {
            $stats->whereDate('degrees.granting_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $stats->whereDate('degrees.granting_date', '<=', $request->end_date);
        }

        if ($request->filled('degree_type')) {
            $stats->where('degrees.degree_type', $request->degree_type);
        }

        if ($request->filled('major_id')) {
            $stats->where('degrees.major_id', $request->major_id);
        }

        if ($request->filled('gender')) {
            $stats->whereHas('student', function ($q) use ($request) {
                $q->where('gender', $request->gender);
            });
        }

        if ($request->filled('ranking')) {
            $stats->where('degrees.ranking', $request->ranking);
        }

        if ($request->filled('training_type')) {
            $stats->whereHas('student', function ($q) use ($request) {
                $q->where('training_type', $request->training_type);
            });
        }

        $result = $stats->groupBy($tableName . '.' . $column)->get();

        return [
            'labels' => $result->pluck($column)->toArray(),
            'values' => $result->pluck('count')->toArray()
        ];
    }

    /**
     * Helper: Get monthly trend
     */
    private function getMonthlyTrend($query)
    {
        $stats = (clone $query)
            ->whereNotNull('granting_date')
            ->select(
                DB::raw('DATE_FORMAT(granting_date, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->reverse()
            ->values();

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
        // Rebuild query with proper table qualification to avoid ambiguous column errors
        $stats = Degree::query()
            ->join('students', 'students.student_id', '=', 'degrees.student_id')
            ->select('students.training_type', DB::raw('count(*) as count'))
            ->whereNotNull('degrees.diploma_blank_id'); // Base condition

        // Apply same filters from original query
        $request = request();

        if ($request->filled('graduation_year')) {
            $stats->where('degrees.graduation_year', $request->graduation_year);
        }

        if ($request->filled('start_date')) {
            $stats->whereDate('degrees.granting_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $stats->whereDate('degrees.granting_date', '<=', $request->end_date);
        }

        if ($request->filled('degree_type')) {
            $stats->where('degrees.degree_type', $request->degree_type);
        }

        if ($request->filled('major_id')) {
            $stats->where('degrees.major_id', $request->major_id);
        }

        if ($request->filled('gender')) {
            $stats->where('students.gender', $request->gender);
        }

        if ($request->filled('ranking')) {
            $stats->where('degrees.ranking', $request->ranking);
        }

        if ($request->filled('training_type')) {
            $stats->where('students.training_type', $request->training_type);
        }

        $result = $stats->groupBy('students.training_type')->get();

        return [
            'labels' => $result->pluck('training_type')->toArray(),
            'values' => $result->pluck('count')->toArray()
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
     * Export bachelor degree information
     */
    public function exportBachelorInfo(Request $request)
    {
        // Prepare filters
        $filters = [
            'graduation_year' => $request->get('graduation_year'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'major_id' => $request->get('major_id'),
            'gender' => $request->get('gender'),
            'ranking' => $request->get('ranking'),
            'training_type' => $request->get('training_type'),
        ];

        return $this->handleExport('bachelor-info', $filters);
    }

    /**
     * Export master degree information
     */
    public function exportMasterInfo(Request $request)
    {
        // Prepare filters
        $filters = [
            'graduation_year' => $request->get('graduation_year'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'major_id' => $request->get('major_id'),
            'gender' => $request->get('gender'),
            'ranking' => $request->get('ranking'),
            'training_type' => $request->get('training_type'),
        ];

        return $this->handleExport('master-info', $filters);
    }

    /**
     * Export doctorate degree information
     */
    public function exportDoctorateInfo(Request $request)
    {
        // Prepare filters
        $filters = [
            'graduation_year' => $request->get('graduation_year'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'major_id' => $request->get('major_id'),
            'gender' => $request->get('gender'),
            'ranking' => $request->get('ranking'),
            'training_type' => $request->get('training_type'),
        ];

        return $this->handleExport('doctorate-info', $filters);
    }

    /**
     * Export advanced political theory certificate information
     */
    public function exportAdvancedPoliticalTheoryInfo(Request $request)
    {
        // Prepare filters
        $filters = [
            'graduation_year' => $request->get('graduation_year'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'major_id' => $request->get('major_id'),
            'gender' => $request->get('gender'),
            'ranking' => $request->get('ranking'),
            'training_type' => $request->get('training_type'),
        ];

        return $this->handleExport('advanced-political-theory-info', $filters);
    }

    /**
     * Export intermediate political theory certificate information
     */
    public function exportIntermediatePoliticalTheoryInfo(Request $request)
    {
        // Prepare filters
        $filters = [
            'graduation_year' => $request->get('graduation_year'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'major_id' => $request->get('major_id'),
            'gender' => $request->get('gender'),
            'ranking' => $request->get('ranking'),
            'training_type' => $request->get('training_type'),
        ];

        return $this->handleExport('intermediate-political-theory-info', $filters);
    }

    /**
     * Export all certificates information
     */
    public function exportAllCertificatesInfo(Request $request)
    {
        // Prepare filters
        $filters = [
            'graduation_year' => $request->get('graduation_year'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'major_id' => $request->get('major_id'),
            'gender' => $request->get('gender'),
            'ranking' => $request->get('ranking'),
            'training_type' => $request->get('training_type'),
        ];

        return $this->handleExport('all-certificates-info', $filters);
    }

    /**
     * Handle export using factory pattern
     *
     * @param string $type Export type key from config
     * @param array $filters Filter parameters
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    protected function handleExport(string $type, array $filters)
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
                'filters' => $filters,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi xuất file: ' . $e->getMessage());
        }
    }
}
