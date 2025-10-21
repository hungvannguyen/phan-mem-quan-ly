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

        return view('home', compact(
            'generalStatistics',
            'statusDistribution',
            'typeDistribution',
            'issuedTrend',
            'recalledTrend',
            'monthlyComparison'
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
}
