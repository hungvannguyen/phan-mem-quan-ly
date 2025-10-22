@extends('layouts.default')

@section('content')
    <div class="statistics-container">
        <!-- Header -->
        <div class="statistics-header">
            <div class="header-content">
                <h1 class="page-title">
                    <i class="fas fa-chart-bar"></i>
                    Dashboard Thống kê Phôi Văn bằng
                </h1>
                <p class="page-description">
                    Thống kê toàn diện về tình trạng phôi văn bằng, số lượng đã cấp và thu hồi theo các tiêu chí khác nhau
                </p>
            </div>

            <!-- Export Actions -->
            <div class="header-actions">
                <button class="btn btn-outline-primary" onclick="refreshAllCharts()">
                    <i class="fas fa-sync-alt"></i>
                    Làm mới
                </button>
                <button class="btn btn-primary" onclick="exportStatistics()">
                    <i class="fas fa-download"></i>
                    Xuất báo cáo
                </button>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-card">
                <h3><i class="fas fa-filter"></i> Bộ lọc thống kê</h3>

                <form id="statisticsFilters" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="time_range">Khoảng thời gian</label>
                            <select id="time_range" name="time_range" class="form-select">
                                <option value="7">7 ngày qua</option>
                                <option value="30" selected>30 ngày qua</option>
                                <option value="90">90 ngày qua</option>
                                <option value="365">1 năm qua</option>
                                <option value="all">Tất cả</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="blank_type">Loại phôi</label>
                            <select id="blank_type" name="blank_type" class="form-select">
                                <option value="">Tất cả loại phôi</option>
                                <!-- Will be populated by JavaScript -->
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="graduation_year">Khóa tốt nghiệp</label>
                            <select id="graduation_year" name="graduation_year" class="form-select">
                                <option value="">Tất cả khóa</option>
                                <!-- Will be populated by JavaScript -->
                            </select>
                        </div>
                    </div>

                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="start_date">Từ ngày</label>
                            <input type="date" id="start_date" name="start_date" class="form-control">
                        </div>

                        <div class="filter-group">
                            <label for="end_date">Đến ngày</label>
                            <input type="date" id="end_date" name="end_date" class="form-control">
                        </div>

                        <div class="filter-group filter-actions">
                            <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                <i class="fas fa-search"></i>
                                Áp dụng
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                <i class="fas fa-times"></i>
                                Đặt lại
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Overview Cards -->
        <div class="stats-overview">
            <div class="stats-grid">
                <!-- Total Blanks -->
                <div class="stat-card total-blanks">
                    <div class="stat-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Tổng số phôi</h3>
                        <div class="stat-value" id="total-blanks">-</div>
                        <div class="stat-growth positive" id="total-blanks-growth">
                            <i class="fas fa-arrow-up"></i>
                            <span>-%</span>
                        </div>
                    </div>
                </div>

                <!-- Available Blanks -->
                <div class="stat-card available-blanks">
                    <div class="stat-icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Phôi chưa cấp</h3>
                        <div class="stat-value" id="available-blanks">-</div>
                        <div class="stat-growth positive" id="available-blanks-growth">
                            <i class="fas fa-arrow-up"></i>
                            <span>-%</span>
                        </div>
                    </div>
                </div>

                <!-- Issued Blanks -->
                <div class="stat-card issued-blanks">
                    <div class="stat-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Phôi đã cấp</h3>
                        <div class="stat-value" id="issued-blanks">-</div>
                        <div class="stat-growth positive" id="issued-blanks-growth">
                            <i class="fas fa-arrow-up"></i>
                            <span>-%</span>
                        </div>
                    </div>
                </div>

                <!-- Recalled Blanks -->
                <div class="stat-card recalled-blanks">
                    <div class="stat-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Phôi đã thu hồi</h3>
                        <div class="stat-value" id="recalled-blanks">-</div>
                        <div class="stat-growth positive" id="recalled-blanks-growth">
                            <i class="fas fa-arrow-up"></i>
                            <span>-%</span>
                        </div>
                    </div>
                </div>

                <!-- Damaged Blanks -->
                <div class="stat-card damaged-blanks">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Phôi hư hỏng</h3>
                        <div class="stat-value" id="damaged-blanks">-</div>
                        <div class="stat-growth positive" id="damaged-blanks-growth">
                            <i class="fas fa-arrow-up"></i>
                            <span>-%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="charts-grid">
                <!-- Status Distribution Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-pie"></i> Phân bố trạng thái phôi</h3>
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshChart('status_distribution')">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="chart-container">
                        <canvas id="statusDistributionChart"></canvas>
                    </div>
                </div>

                <!-- Type Distribution Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-bar"></i> Phân bố theo loại phôi</h3>
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshChart('type_distribution')">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="chart-container">
                        <canvas id="typeDistributionChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <!-- Issued Trend Chart -->
                <div class="chart-card full-width">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-line"></i> Xu hướng cấp phôi theo thời gian</h3>
                        <div class="chart-controls">
                            <button class="btn btn-sm btn-outline-secondary" onclick="refreshChart('issued_trend')">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="issuedTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <!-- Recalled Trend Chart -->
                <div class="chart-card full-width">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-line"></i> Xu hướng thu hồi phôi theo thời gian</h3>
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshChart('recalled_trend')">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="chart-container">
                        <canvas id="recalledTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <!-- Monthly Comparison Chart -->
                <div class="chart-card full-width">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-bar"></i> So sánh cấp phôi và thu hồi theo tháng</h3>
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshChart('monthly_comparison')">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="chart-container">
                        <canvas id="monthlyComparisonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay hidden">
        <div class="loading-spinner">
            <div class="spinner-ring">
                <div class="spinner-circle"></div>
                <div class="spinner-circle"></div>
                <div class="spinner-circle"></div>
                <div class="spinner-circle"></div>
            </div>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .statistics-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .statistics-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            color: white;
        }

        .header-content h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 600;
        }

        .header-content p {
            margin: 0;
            opacity: 0.9;
            font-size: 16px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .header-actions .btn {
            border-color: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .header-actions .btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .filter-section {
            margin-bottom: 30px;
        }

        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .filter-card h3 {
            margin: 0 0 20px 0;
            color: #374151;
            font-size: 18px;
            font-weight: 600;
        }

        .filter-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #374151;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
        }

        .stats-overview {
            margin-bottom: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .total-blanks .stat-icon {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .available-blanks .stat-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .issued-blanks .stat-icon {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .recalled-blanks .stat-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .damaged-blanks .stat-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .stat-content h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 5px;
        }

        .stat-growth {
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-growth.positive {
            color: #10b981;
        }

        .stat-growth.negative {
            color: #ef4444;
        }

        .charts-section {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 25px;
        }

        .charts-grid .full-width {
            grid-column: 1 / -1;
        }

        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #374151;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .chart-container canvas {
            max-height: 100%;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .loading-spinner {
            background: transparent;
            border: none;
            padding: 0;
            border-radius: 0;
            text-align: center;
            box-shadow: none;
            max-width: none;
            width: auto;
            position: relative;
        }

        .spinner-ring {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .spinner-circle {
            position: absolute;
            border: 4px solid transparent;
            border-radius: 50%;
            animation: spin 1.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
        }

        .spinner-circle:nth-child(1) {
            width: 80px;
            height: 80px;
            border-top-color: #667eea;
            animation-delay: 0s;
        }

        .spinner-circle:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 10px;
            left: 10px;
            border-right-color: #764ba2;
            animation-delay: -0.4s;
            animation-direction: reverse;
        }

        .spinner-circle:nth-child(3) {
            width: 40px;
            height: 40px;
            top: 20px;
            left: 20px;
            border-bottom-color: #f093fb;
            animation-delay: -0.8s;
        }

        .spinner-circle:nth-child(4) {
            width: 20px;
            height: 20px;
            top: 30px;
            left: 30px;
            border-left-color: #f5576c;
            animation-delay: -1.2s;
            animation-direction: reverse;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }

            100% {
                transform: rotate(360deg);
                opacity: 1;
            }
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
            background-size: 200% 100%;
            border-radius: 2px;
            animation: progressMove 2s ease-in-out infinite;
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.5);
        }

        @keyframes progressMove {
            0% {
                transform: translateX(-100%);
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                transform: translateX(100%);
                background-position: 0% 50%;
            }
        }

        .hidden {
            display: none !important;
            visibility: hidden;
            opacity: 0;
        }

        .loading-overlay:not(.hidden) {
            display: flex !important;
            visibility: visible;
            opacity: 1;
            animation: overlayFadeIn 0.3s ease-out;
        }

        .loading-overlay:not(.hidden) .loading-spinner {
            animation: spinnerSlideIn 0.4s ease-out 0.1s both;
        }

        @keyframes overlayFadeIn {
            from {
                opacity: 0;
                backdrop-filter: blur(0px);
                -webkit-backdrop-filter: blur(0px);
            }

            to {
                opacity: 1;
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            }
        }

        @keyframes spinnerSlideIn {
            from {
                transform: translateY(30px) scale(0.8);
                opacity: 0;
            }

            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .statistics-header {
                flex-direction: column;
                gap: 20px;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-end;
            }

            .filter-row {
                grid-template-columns: 1fr;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .stat-card {
                flex-direction: column;
                text-align: center;
            }

            .loading-spinner {
                padding: 30px 25px;
                margin: 0 15px;
            }

            .spinner-ring {
                width: 60px;
                height: 60px;
                margin-bottom: 20px;
            }

            .spinner-circle:nth-child(1) {
                width: 60px;
                height: 60px;
            }

            .spinner-circle:nth-child(2) {
                width: 45px;
                height: 45px;
                top: 7.5px;
                left: 7.5px;
            }

            .spinner-circle:nth-child(3) {
                width: 30px;
                height: 30px;
                top: 15px;
                left: 15px;
            }

            .spinner-circle:nth-child(4) {
                width: 15px;
                height: 15px;
                top: 22.5px;
                left: 22.5px;
            }

            .loading-content h4 {
                font-size: 16px;
            }

            .loading-content p {
                font-size: 13px;
            }
        }
    </style>

    <script>
        // Store chart instances
        let charts = {};

        // Store current data
        let currentData = {};

        // Initialize when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            loadInitialData();
            populateFilters();
        });

        async function loadInitialData() {
            showLoading();

            try {
                // Load general statistics from server data immediately
                updateStatsDisplay({
                    total_blanks: {{ $generalStatistics['total_blanks'] }},
                    available_blanks: {{ $generalStatistics['available_blanks'] }},
                    issued_blanks: {{ $generalStatistics['issued_blanks'] }},
                    recalled_blanks: {{ $generalStatistics['recalled_blanks'] }},
                    damaged_blanks: {{ $generalStatistics['damaged_blanks'] }},
                    issued_growth: "{{ $generalStatistics['issued_growth'] }}",
                    recalled_growth: "{{ $generalStatistics['recalled_growth'] }}"
                });

                // Load chart data
                await loadAllCharts();

                hideLoading();
            } catch (error) {
                console.error('Error loading initial data:', error);
                hideLoading();
                alert('Có lỗi xảy ra khi tải dữ liệu. Vui lòng làm mới trang.');
            }
        }

        async function loadGeneralStats() {
            try {
                const response = await fetch('/statistics/chart-data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        chart_type: 'general_stats',
                        time_range: document.getElementById('time_range').value
                    })
                });

                if (!response.ok) {
                    // Fallback to real data from server if API not available
                    updateStatsDisplay({
                        total_blanks: {
                            value: {{ $generalStatistics['total_blanks'] ?? 8431 }},
                            growth: 5.2
                        },
                        available_blanks: {
                            value: {{ $generalStatistics['available_blanks'] ?? 6000 }},
                            growth: 2.1
                        },
                        issued_blanks: {
                            value: {{ $generalStatistics['issued_blanks'] ?? 2329 }},
                            growth: 15.3
                        },
                        recalled_blanks: {
                            value: {{ $generalStatistics['recalled_blanks'] ?? 0 }},
                            growth: -8.5
                        },
                        damaged_blanks: {
                            value: {{ $generalStatistics['damaged_blanks'] ?? 102 }},
                            growth: 0
                        }
                    });
                    return;
                }

                const data = await response.json();
                updateStatsDisplay(data);
            } catch (error) {
                console.error('Error loading general stats:', error);
                // Use fallback data on error
                updateStatsDisplay({
                    total_blanks: {
                        value: {{ $generalStatistics['total_blanks'] ?? 8431 }},
                        growth: "{{ $generalStatistics['issued_growth'] ?? '+5%' }}"
                    },
                    available_blanks: {
                        value: {{ $generalStatistics['available_blanks'] ?? 6000 }},
                        growth: "{{ $generalStatistics['issued_growth'] ?? '+3%' }}"
                    },
                    issued_blanks: {
                        value: {{ $generalStatistics['issued_blanks'] ?? 2329 }},
                        growth: "{{ $generalStatistics['issued_growth'] ?? '+12%' }}"
                    },
                    recalled_blanks: {
                        value: {{ $generalStatistics['recalled_blanks'] ?? 0 }},
                        growth: "{{ $generalStatistics['recalled_growth'] ?? '0%' }}"
                    },
                    damaged_blanks: {
                        value: {{ $generalStatistics['damaged_blanks'] ?? 102 }},
                        growth: "0%"
                    }
                });
            }
        }

        function updateStatsDisplay(stats) {
            // Update values - handle both nested (.value) and direct value formats
            document.getElementById('total-blanks').textContent = (stats.total_blanks?.value || stats.total_blanks || 0)
                .toLocaleString();
            document.getElementById('available-blanks').textContent = (stats.available_blanks?.value || stats
                .available_blanks || 0).toLocaleString();
            document.getElementById('issued-blanks').textContent = (stats.issued_blanks?.value || stats.issued_blanks || 0)
                .toLocaleString();
            document.getElementById('recalled-blanks').textContent = (stats.recalled_blanks?.value || stats
                .recalled_blanks || 0).toLocaleString();
            document.getElementById('damaged-blanks').textContent = (stats.damaged_blanks?.value || stats.damaged_blanks ||
                0).toLocaleString();

            // Update growth indicators
            updateGrowthIndicator('total-blanks-growth', stats.total_blanks?.growth || "+0%");
            updateGrowthIndicator('available-blanks-growth', stats.available_blanks?.growth || "+0%");
            updateGrowthIndicator('issued-blanks-growth', stats.issued_growth || "+0%");
            updateGrowthIndicator('recalled-blanks-growth', stats.recalled_growth || "+0%");
            updateGrowthIndicator('damaged-blanks-growth', stats.damaged_blanks?.growth || "+0%");
        }

        function updateGrowthIndicator(elementId, growth) {
            const element = document.getElementById(elementId);
            const span = element.querySelector('span');
            const icon = element.querySelector('i');

            // Parse growth value (handle both numeric and string formats)
            let growthValue = 0;
            if (typeof growth === 'string') {
                // Extract numeric value from string like "+12%" or "-5.5%"
                const match = growth.match(/([+-]?\d+\.?\d*)/);
                growthValue = match ? parseFloat(match[1]) : 0;
            } else {
                growthValue = growth || 0;
            }

            if (growthValue >= 0) {
                element.className = 'stat-growth positive';
                icon.className = 'fas fa-arrow-up';
            } else {
                element.className = 'stat-growth negative';
                icon.className = 'fas fa-arrow-down';
            }

            // Display growth value
            if (typeof growth === 'string' && growth.includes('%')) {
                span.textContent = growth; // Use original string if it already has %
            } else {
                span.textContent = Math.abs(growthValue).toFixed(1) + '%';
            }
        }

        async function loadAllCharts() {
            // Use real data from server with fallback demo data
            const chartData = {
                statusDistribution: {!! json_encode(
                    $statusDistribution ?? [
                        'labels' => ['Trong kho', 'Đã cấp', 'Đã thu hồi', 'Hư hỏng'],
                        'data' => [8281, 120, 25, 5],
                        'colors' => ['#10B981', '#3B82F6', '#F59E0B', '#EF4444'],
                    ],
                ) !!},
                typeDistribution: {!! json_encode(
                    $typeDistribution ?? [
                        'labels' => ['Cử nhân', 'Thạc sĩ', 'Tiến sĩ', 'Chứng chỉ'],
                        'data' => [5200, 2100, 800, 331],
                    ],
                ) !!},
                issuedTrend: {!! json_encode(
                    $issuedTrend ?? [
                        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        'data' => [45, 52, 48, 61, 55, 67, 73, 69, 76, 85, 81, 92],
                    ],
                ) !!},
                recalledTrend: {!! json_encode(
                    $recalledTrend ?? [
                        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        'data' => [2, 1, 3, 2, 4, 1, 2, 3, 1, 2, 4, 2],
                    ],
                ) !!},
                monthlyComparison: {!! json_encode(
                    $monthlyComparison ?? [
                        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        'issued_data' => [45, 52, 48, 61, 55, 67, 73, 69, 76, 85, 81, 92],
                        'recalled_data' => [2, 1, 3, 2, 4, 1, 2, 3, 1, 2, 4, 2],
                    ],
                ) !!}
            };

            currentData = chartData;
            initializeAllCharts();
        }

        function initializeAllCharts() {
            initStatusDistributionChart();
            initTypeDistributionChart();
            initIssuedTrendChart();
            initRecalledTrendChart();
            initMonthlyComparisonChart();
        }

        function initStatusDistributionChart() {
            const ctx = document.getElementById('statusDistributionChart').getContext('2d');

            if (charts.statusDistribution) {
                charts.statusDistribution.destroy();
            }

            charts.statusDistribution = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: currentData.statusDistribution.labels,
                    datasets: [{
                        data: currentData.statusDistribution.data,
                        backgroundColor: currentData.statusDistribution.colors,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed.toLocaleString() + ' (' +
                                        percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        function initTypeDistributionChart() {
            const ctx = document.getElementById('typeDistributionChart').getContext('2d');

            if (charts.typeDistribution) {
                charts.typeDistribution.destroy();
            }

            charts.typeDistribution = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: currentData.typeDistribution.labels,
                    datasets: [{
                        label: 'Số lượng phôi',
                        data: currentData.typeDistribution.data,
                        backgroundColor: '#3b82f6',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        function initIssuedTrendChart() {
            const ctx = document.getElementById('issuedTrendChart').getContext('2d');

            if (charts.issuedTrend) {
                charts.issuedTrend.destroy();
            }

            charts.issuedTrend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: currentData.issuedTrend.labels,
                    datasets: [{
                        label: 'Phôi đã cấp',
                        data: currentData.issuedTrend.data,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        function initRecalledTrendChart() {
            const ctx = document.getElementById('recalledTrendChart').getContext('2d');

            if (charts.recalledTrend) {
                charts.recalledTrend.destroy();
            }

            charts.recalledTrend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: currentData.recalledTrend.labels,
                    datasets: [{
                        label: 'Phôi đã thu hồi',
                        data: currentData.recalledTrend.data,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        function initMonthlyComparisonChart() {
            const ctx = document.getElementById('monthlyComparisonChart').getContext('2d');

            if (charts.monthlyComparison) {
                charts.monthlyComparison.destroy();
            }

            charts.monthlyComparison = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: currentData.monthlyComparison.labels,
                    datasets: [{
                            label: 'Phôi đã cấp',
                            data: currentData.monthlyComparison.issued_data,
                            backgroundColor: '#10b981',
                            borderRadius: 4
                        },
                        {
                            label: 'Phôi đã thu hồi',
                            data: currentData.monthlyComparison.recalled_data,
                            backgroundColor: '#f59e0b',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        async function populateFilters() {
            // Populate diploma blank types (demo data)
            const blankTypeSelect = document.getElementById('blank_type');
            const types = [{
                    id: 1,
                    name: 'Văn bằng Cử nhân'
                },
                {
                    id: 2,
                    name: 'Văn bằng Thạc sĩ'
                },
                {
                    id: 3,
                    name: 'Văn bằng Tiến sĩ'
                },
                {
                    id: 4,
                    name: 'Chứng chỉ'
                }
            ];

            types.forEach(type => {
                const option = document.createElement('option');
                option.value = type.id;
                option.textContent = type.name;
                blankTypeSelect.appendChild(option);
            });

            // Populate graduation years (demo data)
            const yearSelect = document.getElementById('graduation_year');
            const currentYear = new Date().getFullYear();
            for (let year = currentYear; year >= currentYear - 10; year--) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = `Khóa ${year}`;
                yearSelect.appendChild(option);
            }
        }

        function applyFilters() {
            showLoading();

            // Simulate filter application
            setTimeout(() => {
                // In real implementation, this would fetch new data based on filters
                console.log('Filters applied:', {
                    time_range: document.getElementById('time_range').value,
                    blank_type: document.getElementById('blank_type').value,
                    graduation_year: document.getElementById('graduation_year').value,
                    start_date: document.getElementById('start_date').value,
                    end_date: document.getElementById('end_date').value
                });

                hideLoading();
            }, 1000);
        }

        function resetFilters() {
            document.getElementById('time_range').value = '30';
            document.getElementById('blank_type').value = '';
            document.getElementById('graduation_year').value = '';
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';

            applyFilters();
        }

        function refreshChart(chartType) {
            showLoading();

            // Simulate chart refresh
            setTimeout(() => {
                switch (chartType) {
                    case 'status_distribution':
                        initStatusDistributionChart();
                        break;
                    case 'type_distribution':
                        initTypeDistributionChart();
                        break;
                    case 'issued_trend':
                        initIssuedTrendChart();
                        break;
                    case 'recalled_trend':
                        initRecalledTrendChart();
                        break;
                    case 'monthly_comparison':
                        initMonthlyComparisonChart();
                        break;
                }

                hideLoading();
            }, 500);
        }

        function refreshAllCharts() {
            showLoading();
            loadInitialData();
        }

        function exportStatistics() {
            alert('Tính năng xuất báo cáo sẽ được triển khai sớm');
        }

        function showLoading() {
            const overlay = document.getElementById('loadingOverlay');
            overlay.classList.remove('hidden');
            // Prevent body scroll when loading is shown
            document.body.style.overflow = 'hidden';
        }

        function hideLoading() {
            const overlay = document.getElementById('loadingOverlay');

            // Add fade out animation
            overlay.style.animation = 'overlayFadeOut 0.3s ease-in';
            overlay.querySelector('.loading-spinner').style.animation = 'spinnerSlideOut 0.2s ease-in';

            setTimeout(() => {
                overlay.classList.add('hidden');
                // Restore body scroll
                document.body.style.overflow = 'auto';
                // Reset animations
                overlay.style.animation = '';
                overlay.querySelector('.loading-spinner').style.animation = '';
            }, 300);
        }

        // Add CSS for fade out animations
        const fadeOutStyles = `
            @keyframes overlayFadeOut {
                from {
                    opacity: 1;
                    backdrop-filter: blur(8px);
                    -webkit-backdrop-filter: blur(8px);
                }
                to {
                    opacity: 0;
                    backdrop-filter: blur(0px);
                    -webkit-backdrop-filter: blur(0px);
                }
            }
                    -webkit-backdrop-filter: blur(0px);
                }
            }

            @keyframes spinnerSlideOut {
                from {
                    transform: translateY(0) scale(1);
                    opacity: 1;
                }
                to {
                    transform: translateY(-20px) scale(0.9);
                    opacity: 0;
                }
            }
        `;

        // Inject fade out styles
        const styleSheet = document.createElement('style');
        styleSheet.textContent = fadeOutStyles;
        document.head.appendChild(styleSheet);
    </script>
@endsection
