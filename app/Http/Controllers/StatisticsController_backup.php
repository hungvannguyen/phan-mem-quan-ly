<?php

namespace App\Http\Controllers;

use App\Models\DiplomaBlank;
use App\Models\DiplomaBlankType;
use App\Models\Degree;
use App\Enums\DiplomaBlankStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SaKanjo\EasyMetrics\Metrics\Value;
use SaKanjo\EasyMetrics\Metrics\Doughnut;
use SaKanjo\EasyMetrics\Metrics\Trend;
use SaKanjo\EasyMetrics\Metrics\Bar;
use SaKanjo\EasyMetrics\Metrics\Line;
use SaKanjo\EasyMetrics\Metrics\Enums\Range;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    /**
     * Display the statistics dashboard
     */
    public function index(Request $request)
    {
        // Get filters from request
        $timeRange = $request->get('time_range', 30);
        $blankType = $request->get('blank_type');
        $graduationYear = $request->get('graduation_year');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Get diploma blank types for filter
        $diplomaBlankTypes = DiplomaBlankType::orderBy('type_name')->get();
        
        // Get available graduation years
        $graduationYears = Degree::selectRaw('DISTINCT graduation_year')
            ->whereNotNull('graduation_year')
            ->orderBy('graduation_year', 'desc')
            ->pluck('graduation_year');

        // General statistics
        $generalStats = $this->getGeneralStatistics($timeRange);
        
        // Charts data
        $statusDistribution = $this->getStatusDistribution($blankType);
        $issuedTrend = $this->getIssuedTrend($timeRange, $blankType, $graduationYear, $startDate, $endDate);
        $recalledTrend = $this->getRecalledTrend($timeRange, $blankType, $graduationYear, $startDate, $endDate);
        $typeDistribution = $this->getTypeDistribution($timeRange);
        $monthlyComparison = $this->getMonthlyComparison($graduationYear);
        $yearlyGrowth = $this->getYearlyGrowth();

        return view('home', compact(
            'generalStats',
            'statusDistribution', 
            'issuedTrend',
            'recalledTrend',
            'typeDistribution',
            'monthlyComparison',
            'yearlyGrowth',
            'diplomaBlankTypes',
            'graduationYears',
            'timeRange',
            'blankType',
            'graduationYear',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get general statistics overview
     */
    private function getGeneralStatistics($timeRange)
    {
        $query = DiplomaBlank::query();
        
        // Apply time range filter
        if ($timeRange && $timeRange !== 'all') {
            $query->where('created_at', '>=', Carbon::now()->subDays($timeRange));
        }

        // Total blanks
        [$totalBlanks, $totalGrowth] = Value::make(DiplomaBlank::class)
            ->range($timeRange)
            ->withGrowthRate()
            ->count();

        // Available blanks (In Stock)
        $availableBlanksQuery = DiplomaBlank::where('status', DiplomaBlankStatus::IN_STOCK->value);
        [$availableBlanks, $availableGrowth] = Value::make($availableBlanksQuery)
            ->range($timeRange)
            ->withGrowthRate()
            ->count();

        // Issued blanks  
        $issuedBlanksQuery = DiplomaBlank::where('status', DiplomaBlankStatus::ISSUED->value);
        [$issuedBlanks, $issuedGrowth] = Value::make($issuedBlanksQuery)
            ->range($timeRange)
            ->withGrowthRate()
            ->count();

        // Recalled blanks
        $recalledBlanksQuery = DiplomaBlank::where('status', DiplomaBlankStatus::RECALLED->value);
        [$recalledBlanks, $recalledGrowth] = Value::make($recalledBlanksQuery)
            ->range($timeRange)
            ->withGrowthRate()
            ->count();

        // Damaged blanks
        $damagedBlanksQuery = DiplomaBlank::where('status', DiplomaBlankStatus::DAMAGED->value);
        [$damagedBlanks, $damagedGrowth] = Value::make($damagedBlanksQuery)
            ->range($timeRange)
            ->withGrowthRate()
            ->count();

        return [
            'total_blanks' => ['value' => $totalBlanks, 'growth' => $totalGrowth],
            'available_blanks' => ['value' => $availableBlanks, 'growth' => $availableGrowth],
            'issued_blanks' => ['value' => $issuedBlanks, 'growth' => $issuedGrowth],
            'recalled_blanks' => ['value' => $recalledBlanks, 'growth' => $recalledGrowth],
            'damaged_blanks' => ['value' => $damagedBlanks, 'growth' => $damagedGrowth],
        ];
    }

    /**
     * Get diploma blank status distribution
     */
    private function getStatusDistribution($blankType = null)
    {
        $query = DiplomaBlank::query();
        
        if ($blankType) {
            $query->where('type_id', $blankType);
        }

        [$labels, $data] = Doughnut::make(DiplomaBlank::class)
            ->when($blankType, function($metric) use ($blankType) {
                return $metric->where('type_id', $blankType);
            })
            ->count('status');

        // Convert status values to Vietnamese labels
        $translatedLabels = array_map(function($label) {
            return DiplomaBlankStatus::from($label)->getLabel();
        }, $labels);

        return [
            'labels' => $translatedLabels,
            'data' => $data,
            'colors' => ['#10B981', '#F59E0B', '#3B82F6', '#EF4444']
        ];
    }

    /**
     * Get issued blanks trend over time
     */
    private function getIssuedTrend($timeRange, $blankType = null, $graduationYear = null, $startDate = null, $endDate = null)
    {
        $metric = Trend::make(DiplomaBlank::class)
            ->range($timeRange)
            ->where('status', DiplomaBlankStatus::ISSUED->value);

        // Apply filters
        if ($blankType) {
            $metric->where('type_id', $blankType);
        }

        // For graduation year filter, we need to join with degrees table
        if ($graduationYear) {
            $metric->whereExists(function ($query) use ($graduationYear) {
                $query->select(DB::raw(1))
                      ->from('degrees')
                      ->whereColumn('degrees.diploma_blank_id', 'diploma_blanks.diploma_blank_id')
                      ->where('degrees.graduation_year', $graduationYear);
            });
        }

        if ($startDate && $endDate) {
            $metric->whereBetween('issue_date', [$startDate, $endDate]);
        }

        [$labels, $data, $growth] = $metric->withGrowthRate()->countByMonths();

        return [
            'labels' => $labels,
            'data' => $data,
            'growth' => $growth
        ];
    }

    /**
     * Get recalled blanks trend over time
     */
    private function getRecalledTrend($timeRange, $blankType = null, $graduationYear = null, $startDate = null, $endDate = null)
    {
        $metric = Trend::make(DiplomaBlank::class)
            ->range($timeRange)
            ->where('status', DiplomaBlankStatus::RECALLED->value);

        // Apply filters
        if ($blankType) {
            $metric->where('type_id', $blankType);
        }

        // For graduation year filter, we need to join with degrees table
        if ($graduationYear) {
            $metric->whereExists(function ($query) use ($graduationYear) {
                $query->select(DB::raw(1))
                      ->from('degrees')
                      ->whereColumn('degrees.diploma_blank_id', 'diploma_blanks.diploma_blank_id')
                      ->where('degrees.graduation_year', $graduationYear);
            });
        }

        if ($startDate && $endDate) {
            $metric->whereBetween('recall_date', [$startDate, $endDate]);
        }

        [$labels, $data, $growth] = $metric->withGrowthRate()->countByMonths();

        return [
            'labels' => $labels,
            'data' => $data,
            'growth' => $growth
        ];
    }

    /**
     * Get distribution by diploma blank type
     */
    private function getTypeDistribution($timeRange)
    {
        [$labels, $data] = Bar::make(DiplomaBlank::class)
            ->range($timeRange)
            ->count('type_id');

        // Convert type IDs to type names
        $typeNames = DiplomaBlankType::whereIn('type_id', array_keys(array_combine($labels, $data)))
            ->pluck('type_name', 'type_id')
            ->toArray();

        $translatedLabels = array_map(function($typeId) use ($typeNames) {
            return $typeNames[$typeId] ?? "Loại $typeId";
        }, $labels);

        return [
            'labels' => $translatedLabels,
            'data' => $data
        ];
    }

    /**
     * Get monthly comparison of issued vs recalled
     */
    private function getMonthlyComparison($graduationYear = null)
    {
        $issuedMetric = Trend::make(DiplomaBlank::class)
            ->range(12)
            ->where('status', DiplomaBlankStatus::ISSUED->value);

        $recalledMetric = Trend::make(DiplomaBlank::class)
            ->range(12)
            ->where('status', DiplomaBlankStatus::RECALLED->value);

        if ($graduationYear) {
            $issuedMetric->whereExists(function ($query) use ($graduationYear) {
                $query->select(DB::raw(1))
                      ->from('degrees')
                      ->whereColumn('degrees.diploma_blank_id', 'diploma_blanks.diploma_blank_id')
                      ->where('degrees.graduation_year', $graduationYear);
            });
            
            $recalledMetric->whereExists(function ($query) use ($graduationYear) {
                $query->select(DB::raw(1))
                      ->from('degrees')
                      ->whereColumn('degrees.diploma_blank_id', 'diploma_blanks.diploma_blank_id')
                      ->where('degrees.graduation_year', $graduationYear);
            });
        }

        [$labels, $issuedData] = $issuedMetric->countByMonths();
        [, $recalledData] = $recalledMetric->countByMonths();

        return [
            'labels' => $labels,
            'issued_data' => $issuedData,
            'recalled_data' => $recalledData
        ];
    }

    /**
     * Get yearly growth statistics
     */
    private function getYearlyGrowth()
    {
        [$labels, $data, $growth] = Trend::make(DiplomaBlank::class)
            ->where('status', DiplomaBlankStatus::ISSUED->value)
            ->withGrowthRate()
            ->countByYears();

        return [
            'labels' => $labels,
            'data' => $data,
            'growth' => $growth
        ];
    }

    /**
     * Export statistics to PDF or Excel (future feature)
     */
    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return response()->json(['message' => 'Export functionality will be implemented']);
    }

    /**
     * Get AJAX data for dynamic chart updates
     */
    public function getChartData(Request $request)
    {
        $chartType = $request->get('chart_type');
        $timeRange = $request->get('time_range', 30);
        $blankType = $request->get('blank_type');
        $graduationYear = $request->get('graduation_year');

        switch ($chartType) {
            case 'status_distribution':
                return response()->json($this->getStatusDistribution($blankType));
            
            case 'issued_trend':
                return response()->json($this->getIssuedTrend($timeRange, $blankType, $graduationYear));
            
            case 'recalled_trend':
                return response()->json($this->getRecalledTrend($timeRange, $blankType, $graduationYear));
                
            case 'type_distribution':
                return response()->json($this->getTypeDistribution($timeRange));
                
            case 'monthly_comparison':
                return response()->json($this->getMonthlyComparison($graduationYear));
                
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }
}