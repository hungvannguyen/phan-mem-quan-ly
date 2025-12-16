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

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab-btn active" data-tab="diplomas">
                <i class="fas fa-certificate"></i>
                Thống kê Văn bằng
            </button>
            <button class="tab-btn" data-tab="certificates">
                <i class="fas fa-award"></i>
                Thống kê Chứng chỉ
            </button>
        </div>

        <!-- Diplomas Statistics Tab -->
        <div id="diplomas-tab" class="tab-content active">
            <div class="filter-section">
                <div class="filter-card">
                    <h3><i class="fas fa-filter"></i> Bộ lọc thống kê văn bằng</h3>

                    <form id="diplomaFilters" class="filter-form">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="graduation_year">Khóa tốt nghiệp</label>
                                <select id="graduation_year" name="graduation_year" class="form-select">
                                    <option value="">Tất cả khóa</option>
                                    @for ($year = date('Y'); $year >= 2000; $year--)
                                        <option value="{{ $year }}">Khóa {{ $year }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="start_date">Từ ngày</label>
                                <input type="date" id="start_date" name="start_date" class="form-control">
                            </div>

                            <div class="filter-group">
                                <label for="end_date">Đến ngày</label>
                                <input type="date" id="end_date" name="end_date" class="form-control">
                            </div>

                            <div class="filter-group">
                                <label for="degree_type">Loại bằng</label>
                                <select id="degree_type" name="degree_type" class="form-select">
                                    <option value="">Tất cả loại bằng</option>
                                    <option value="bachelor">Cử nhân</option>
                                    <option value="master">Thạc sĩ</option>
                                    <option value="doctor">Tiến sĩ</option>
                                </select>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="major_id">Ngành học</label>
                                <select id="major_id" name="major_id" class="form-select">
                                    <option value="">Tất cả ngành</option>
                                    @foreach ($majors as $major)
                                        <option value="{{ $major->major_id }}">{{ $major->major_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="gender">Giới tính</label>
                                <select id="gender" name="gender" class="form-select">
                                    <option value="">Tất cả</option>
                                    <option value="Male">Nam</option>
                                    <option value="Female">Nữ</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="ranking">Xếp loại</label>
                                <select id="ranking" name="ranking" class="form-select">
                                    <option value="">Tất cả</option>
                                    <option value="Xuất sắc">Xuất sắc</option>
                                    <option value="Giỏi">Giỏi</option>
                                    <option value="Khá">Khá</option>
                                    <option value="Trung bình">Trung bình</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="training_type">Hình thức đào tạo</label>
                                <select id="training_type" name="training_type" class="form-select">
                                    <option value="">Tất cả</option>
                                    <option value="Chính quy">Chính quy</option>
                                    <option value="Liên thông">Liên thông</option>
                                    <option value="Vừa học vừa làm">Vừa học vừa làm</option>
                                </select>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="filter-group filter-actions">
                                <button type="button" class="btn btn-primary" onclick="applyDiplomaFilters()">
                                    <i class="fas fa-search"></i>
                                    Thống kê
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetDiplomaFilters()">
                                    <i class="fas fa-undo"></i>
                                    Đặt lại
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Certificates Statistics Tab -->
        <div id="certificates-tab" class="tab-content">
            <div class="filter-section">
                <div class="filter-card">
                    <h3><i class="fas fa-filter"></i> Bộ lọc thống kê chứng chỉ</h3>

                    <form id="certificateFilters" class="filter-form">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="certificate_type">Loại chứng chỉ</label>
                                <select id="certificate_type" name="certificate_type" class="form-select">
                                    <option value="">Tất cả loại chứng chỉ</option>
                                    <option value="ngoại ngữ">Chứng chỉ ngoại ngữ</option>
                                    <option value="tin học">Chứng chỉ tin học</option>
                                    <option value="nghề">Chứng chỉ nghề</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="cert_start_date">Từ ngày</label>
                                <input type="date" id="cert_start_date" name="start_date" class="form-control">
                            </div>

                            <div class="filter-group">
                                <label for="cert_end_date">Đến ngày</label>
                                <input type="date" id="cert_end_date" name="end_date" class="form-control">
                            </div>

                            <div class="filter-group filter-actions">
                                <button type="button" class="btn btn-primary" onclick="applyCertificateFilters()">
                                    <i class="fas fa-search"></i>
                                    Thống kê
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetCertificateFilters()">
                                    <i class="fas fa-undo"></i>
                                    Đặt lại
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
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

        <!-- Diploma Statistics Results (Diplomas Tab) -->
        <div id="diplomas-results" class="tab-content active">
            <div class="charts-section">
                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-pie"></i> Phân bố theo loại bằng</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="degreeTypeChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-pie"></i> Phân bố theo giới tính</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="genderChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-bar"></i> Phân bố theo xếp loại</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="rankingChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-bar"></i> Phân bố theo ngành</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="majorChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-card full-width">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-line"></i> Phân bố theo khóa</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="graduationYearChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-card full-width">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-pie"></i> Phân bố theo hình thức đào tạo</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="trainingTypeChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detailed Table -->
                <div class="chart-card full-width">
                    <div class="chart-header">
                        <h3><i class="fas fa-table"></i> Chi tiết thống kê văn bằng</h3>
                    </div>
                    <div class="table-wrapper">
                        <table class="data-table" id="diplomaStatsTable">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tiêu chí</th>
                                    <th>Giá trị</th>
                                    <th>Số lượng</th>
                                    <th>Tỷ lệ %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center text-gray-500">Vui lòng chọn bộ lọc và nhấn
                                        "Thống kê"</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Export Section -->
                <div class="chart-card full-width">
                    <div class="chart-header">
                        <h3><i class="fas fa-file-export"></i> Xuất báo cáo Excel</h3>
                    </div>
                    <div class="export-actions">
                        <button type="button" class="btn btn-success" onclick="exportDiplomaDetailed()">
                            <i class="fas fa-file-excel"></i>
                            Xuất chi tiết văn bằng
                        </button>
                        <button type="button" class="btn btn-primary" onclick="exportBachelorInfo()">
                            <i class="fas fa-file-excel"></i>
                            Thông tin cấp bằng cử nhân
                        </button>
                        <button type="button" class="btn btn-info" onclick="exportDiplomaSummary('degree_type')">
                            <i class="fas fa-file-excel"></i>
                            Tổng hợp theo loại bằng
                        </button>
                        <button type="button" class="btn btn-info" onclick="exportDiplomaSummary('graduation_year')">
                            <i class="fas fa-file-excel"></i>
                            Tổng hợp theo khóa
                        </button>
                        <button type="button" class="btn btn-info" onclick="exportDiplomaSummary('major')">
                            <i class="fas fa-file-excel"></i>
                            Tổng hợp theo ngành
                        </button>
                        <button type="button" class="btn btn-info" onclick="exportDiplomaSummary('ranking')">
                            <i class="fas fa-file-excel"></i>
                            Tổng hợp theo xếp loại
                        </button>
                        <button type="button" class="btn btn-info" onclick="exportDiplomaSummary('gender')">
                            <i class="fas fa-file-excel"></i>
                            Tổng hợp theo giới tính
                        </button>
                        <button type="button" class="btn btn-info" onclick="exportDiplomaSummary('training_type')">
                            <i class="fas fa-file-excel"></i>
                            Tổng hợp theo hình thức
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Certificate Statistics Results (Certificates Tab) -->
        <div id="certificates-results" class="tab-content">
            <div class="charts-section">
                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-pie"></i> Phân bố theo loại chứng chỉ</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="certificateTypeChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-line"></i> Xu hướng cấp chứng chỉ theo tháng</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="certificateTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detailed Table -->
                <div class="chart-card full-width" style="margin-top: 25px;">
                    <div class="chart-header">
                        <h3><i class="fas fa-table"></i> Chi tiết thống kê chứng chỉ</h3>
                    </div>
                    <div class="table-wrapper">
                        <table class="data-table" id="certificateStatsTable">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Loại chứng chỉ</th>
                                    <th>Số lượng</th>
                                    <th>Tỷ lệ %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center text-gray-500">Vui lòng chọn bộ lọc và nhấn
                                        "Thống kê"</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Export Section -->
                <div class="chart-card full-width">
                    <div class="chart-header">
                        <h3><i class="fas fa-file-export"></i> Xuất báo cáo Excel</h3>
                    </div>
                    <div class="export-actions">
                        <button type="button" class="btn btn-success" onclick="exportCertificateDetailed()">
                            <i class="fas fa-file-excel"></i>
                            Xuất chi tiết chứng chỉ
                        </button>
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

    <!-- Statistics CSS and JS -->
    @vite(['resources/css/statistics.css', 'resources/js/statistics.js'])

    <!-- Pass server data to JavaScript -->
    <script>
        window.generalStatistics = {
            total_blanks: {{ $generalStatistics['total_blanks'] }},
            available_blanks: {{ $generalStatistics['available_blanks'] }},
            issued_blanks: {{ $generalStatistics['issued_blanks'] }},
            recalled_blanks: {{ $generalStatistics['recalled_blanks'] }},
            damaged_blanks: {{ $generalStatistics['damaged_blanks'] }},
            issued_growth: "{{ $generalStatistics['issued_growth'] }}",
            recalled_growth: "{{ $generalStatistics['recalled_growth'] }}"
        };
    </script>
@endsection
