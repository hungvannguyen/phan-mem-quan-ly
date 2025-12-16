// Store chart instances
let charts = {};

// Store current data
let currentData = {};

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function () {
    loadInitialData();
    setupTabSwitching();
});

// Tab switching functionality
function setupTabSwitching() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const tabType = this.dataset.tab;

            // Remove active class from all tabs
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            // Add active class to clicked tab
            this.classList.add('active');
            document.getElementById(tabType + '-tab').classList.add('active');
            document.getElementById(tabType + '-results').classList.add('active');
        });
    });
}

async function loadInitialData() {
    showLoading();

    try {
        // Load general statistics from server data
        if (window.generalStatistics) {
            updateStatsDisplay(window.generalStatistics);
        }

        // Load default diploma statistics (without filters)
        await loadDefaultDiplomaStatistics();

        // Load default certificate statistics (without filters)
        await loadDefaultCertificateStatistics();

        hideLoading();
    } catch (error) {
        console.error('Error loading initial data:', error);
        hideLoading();
        alert('Có lỗi xảy ra khi tải dữ liệu. Vui lòng làm mới trang.');
    }
}

// Load default diploma statistics without filters
async function loadDefaultDiplomaStatistics() {
    try {
        const response = await fetch('/statistics/diplomas');
        const data = await response.json();

        // Update charts
        updateDiplomaCharts(data);

        // Update table
        updateDiplomaTable(data.details || []);
    } catch (error) {
        console.error('Error loading default diploma statistics:', error);
    }
}

// Load default certificate statistics without filters
async function loadDefaultCertificateStatistics() {
    try {
        const response = await fetch('/statistics/certificates');
        const data = await response.json();

        // Update charts
        updateCertificateCharts(data);

        // Update table
        updateCertificateTable(data.details || []);
    } catch (error) {
        console.error('Error loading default certificate statistics:', error);
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

// Apply diploma filters
async function applyDiplomaFilters() {
    showLoading();
    const formData = new FormData(document.getElementById('diplomaFilters'));
    const params = new URLSearchParams(formData);

    try {
        const response = await fetch(`/statistics/diplomas?${params}`);
        const data = await response.json();

        // Update charts
        updateDiplomaCharts(data);

        // Update table
        updateDiplomaTable(data.details || []);

        hideLoading();
    } catch (error) {
        console.error('Error fetching diploma statistics:', error);
        hideLoading();
        alert('Có lỗi xảy ra khi lấy dữ liệu thống kê');
    }
}

// Apply certificate filters
async function applyCertificateFilters() {
    showLoading();
    const formData = new FormData(document.getElementById('certificateFilters'));
    const params = new URLSearchParams(formData);

    try {
        const response = await fetch(`/statistics/certificates?${params}`);
        const data = await response.json();

        // Update charts
        updateCertificateCharts(data);

        // Update table
        updateCertificateTable(data.details || []);

        hideLoading();
    } catch (error) {
        console.error('Error fetching certificate statistics:', error);
        hideLoading();
        alert('Có lỗi xảy ra khi lấy dữ liệu thống kê');
    }
}

// Update diploma charts
function updateDiplomaCharts(data) {
    // Degree Type Chart
    updateOrCreateChart('degreeTypeChart', {
        type: 'doughnut',
        data: {
            labels: data.by_type?.labels || [],
            datasets: [{
                data: data.by_type?.values || [],
                backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        }
    });

    // Gender Chart
    updateOrCreateChart('genderChart', {
        type: 'pie',
        data: {
            labels: ['Nam', 'Nữ'],
            datasets: [{
                data: [data.male_count || 0, data.female_count || 0],
                backgroundColor: ['#3B82F6', '#EC4899'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        }
    });

    // Ranking Chart
    updateOrCreateChart('rankingChart', {
        type: 'bar',
        data: {
            labels: data.by_ranking?.labels || [],
            datasets: [{
                label: 'Số lượng',
                data: data.by_ranking?.values || [],
                backgroundColor: '#10B981',
                borderRadius: 6
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Major Chart
    updateOrCreateChart('majorChart', {
        type: 'bar',
        data: {
            labels: data.by_major?.labels || [],
            datasets: [{
                label: 'Số lượng',
                data: data.by_major?.values || [],
                backgroundColor: '#8B5CF6',
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Graduation Year Chart
    updateOrCreateChart('graduationYearChart', {
        type: 'line',
        data: {
            labels: data.by_year?.labels || [],
            datasets: [{
                label: 'Số lượng',
                data: data.by_year?.values || [],
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Training Type Chart
    updateOrCreateChart('trainingTypeChart', {
        type: 'doughnut',
        data: {
            labels: data.by_training_type?.labels || [],
            datasets: [{
                data: data.by_training_type?.values || [],
                backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        }
    });
}

// Update certificate charts
function updateCertificateCharts(data) {
    // Certificate Type Chart
    const typeLabels = data.by_type?.labels || [];
    const typeValues = data.by_type?.values || [];

    // Filter out items with zero values for better visualization
    const filteredTypeData = typeLabels.reduce((acc, label, index) => {
        if (typeValues[index] > 0) {
            acc.labels.push(label);
            acc.values.push(typeValues[index]);
        }
        return acc;
    }, {
        labels: [],
        values: []
    });

    updateOrCreateChart('certificateTypeChart', {
        type: 'doughnut',
        data: {
            labels: filteredTypeData.labels.length > 0 ? filteredTypeData.labels : ['Không có dữ liệu'],
            datasets: [{
                data: filteredTypeData.values.length > 0 ? filteredTypeData.values : [1],
                backgroundColor: filteredTypeData.values.length > 0 ? ['#EF4444', '#06B6D4',
                    '#F59E0B', '#6B7280'
                ] : ['#E5E7EB'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: filteredTypeData.values.length > 0
                }
            }
        }
    });

    // Certificate Trend Chart
    updateOrCreateChart('certificateTrendChart', {
        type: 'line',
        data: {
            labels: data.by_month?.labels || [],
            datasets: [{
                label: 'Số lượng',
                data: data.by_month?.values || [],
                borderColor: '#8B5CF6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// Helper function to update or create chart
function updateOrCreateChart(canvasId, config) {
    if (charts[canvasId]) {
        charts[canvasId].destroy();
    }
    const ctx = document.getElementById(canvasId);
    if (ctx) {
        // Add default options if not provided
        if (!config.options) {
            config.options = {};
        }

        // Set default responsive options
        config.options.responsive = true;
        config.options.maintainAspectRatio = false;

        // Add default plugins if not provided
        if (!config.options.plugins) {
            config.options.plugins = {};
        }

        // Add legend configuration
        if (!config.options.plugins.legend) {
            config.options.plugins.legend = {
                display: true,
                position: 'bottom',
                labels: {
                    padding: 15,
                    usePointStyle: true,
                    font: {
                        size: 12
                    }
                }
            };
        }

        // Add tooltip configuration
        if (!config.options.plugins.tooltip) {
            config.options.plugins.tooltip = {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleFont: {
                    size: 14
                },
                bodyFont: {
                    size: 13
                }
            };
        }

        charts[canvasId] = new Chart(ctx, config);
    }
}

// Update diploma table
function updateDiplomaTable(details) {
    const tbody = document.querySelector('#diplomaStatsTable tbody');
    if (details.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="5" class="text-center text-gray-500">Không có dữ liệu</td></tr>';
        return;
    }

    tbody.innerHTML = details.map((item, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>${item.criteria}</td>
            <td>${item.value}</td>
            <td>${item.count}</td>
            <td>${item.percentage}%</td>
        </tr>
    `).join('');
}

// Update certificate table
function updateCertificateTable(details) {
    const tbody = document.querySelector('#certificateStatsTable tbody');
    if (details.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="4" class="text-center text-gray-500">Không có dữ liệu</td></tr>';
        return;
    }

    tbody.innerHTML = details.map((item, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>${item.type}</td>
            <td>${item.count}</td>
            <td>${item.percentage}%</td>
        </tr>
    `).join('');
}

// Reset filters
function resetDiplomaFilters() {
    document.getElementById('diplomaFilters').reset();
    // Clear diploma charts and table
    Object.keys(charts).forEach(key => {
        if (key.includes('degree') || key.includes('gender') || key.includes('ranking') ||
            key.includes('major') || key.includes('graduation') || key.includes('training')) {
            if (charts[key]) {
                charts[key].destroy();
                delete charts[key];
            }
        }
    });

    // Reset table
    const diplomaTableBody = document.querySelector('#diplomaStatsTable tbody');
    if (diplomaTableBody) {
        diplomaTableBody.innerHTML =
            '<tr><td colspan="5" class="text-center text-gray-500">Vui lòng chọn bộ lọc và nhấn "Thống kê"</td></tr>';
    }

    // Reload default statistics
    loadDefaultDiplomaStatistics();
}

function resetCertificateFilters() {
    document.getElementById('certificateFilters').reset();
    // Clear certificate charts and table
    if (charts['certificateTypeChart']) {
        charts['certificateTypeChart'].destroy();
        delete charts['certificateTypeChart'];
    }
    if (charts['certificateTrendChart']) {
        charts['certificateTrendChart'].destroy();
        delete charts['certificateTrendChart'];
    }

    // Reset table
    const certTableBody = document.querySelector('#certificateStatsTable tbody');
    if (certTableBody) {
        certTableBody.innerHTML =
            '<tr><td colspan="4" class="text-center text-gray-500">Vui lòng chọn bộ lọc và nhấn "Thống kê"</td></tr>';
    }

    // Reload default statistics
    loadDefaultCertificateStatistics();
}

// Export functions
async function exportBachelorInfo() {
    showLoading();

    try {
        const formData = new FormData(document.getElementById('diplomaFilters'));
        const params = new URLSearchParams(formData);

        // Use fetch to download file
        const response = await fetch(`/statistics/export-bachelor-info?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            }
        });

        if (!response.ok) {
            throw new Error('Lỗi khi xuất file: ' + response.statusText);
        }

        // Get filename from Content-Disposition header
        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = 'Thong_tin_cap_bang_cu_nhan.xlsx';
        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
            if (filenameMatch) {
                filename = filenameMatch[1];
            }
        }

        // Convert response to blob and trigger download
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();

        // Cleanup
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        hideLoading();
    } catch (error) {
        console.error('Export error:', error);
        hideLoading();
        alert('Có lỗi xảy ra khi xuất file: ' + error.message);
    }
}

// Export master info
async function exportMasterInfo() {
    showLoading();

    try {
        const formData = new FormData(document.getElementById('diplomaFilters'));
        const params = new URLSearchParams(formData);

        // Use fetch to download file
        const response = await fetch(`/statistics/export-master-info?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            }
        });

        if (!response.ok) {
            throw new Error('Lỗi khi xuất file: ' + response.statusText);
        }

        // Get filename from Content-Disposition header
        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = 'Thong_tin_cap_bang_thac_si.xlsx';
        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
            if (filenameMatch) {
                filename = filenameMatch[1];
            }
        }

        // Convert response to blob and trigger download
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();

        // Cleanup
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        hideLoading();
    } catch (error) {
        console.error('Export error:', error);
        hideLoading();
        alert('Có lỗi xảy ra khi xuất file: ' + error.message);
    }
}

// Export doctorate info
async function exportDoctorateInfo() {
    showLoading();

    try {
        const formData = new FormData(document.getElementById('diplomaFilters'));
        const params = new URLSearchParams(formData);

        // Use fetch to download file
        const response = await fetch(`/statistics/export-doctorate-info?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            }
        });

        if (!response.ok) {
            throw new Error('Lỗi khi xuất file: ' + response.statusText);
        }

        // Get filename from Content-Disposition header
        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = 'Thong_tin_cap_bang_tien_si.xlsx';
        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
            if (filenameMatch) {
                filename = filenameMatch[1];
            }
        }

        // Convert response to blob and trigger download
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();

        // Cleanup
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        hideLoading();
    } catch (error) {
        console.error('Export error:', error);
        hideLoading();
        alert('Có lỗi xảy ra khi xuất file: ' + error.message);
    }
}

// Export advanced political theory info
async function exportAdvancedPoliticalTheoryInfo() {
    showLoading();

    try {
        const formData = new FormData(document.getElementById('diplomaFilters'));
        const params = new URLSearchParams(formData);

        // Use fetch to download file
        const response = await fetch(`/statistics/export-advanced-political-theory-info?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            }
        });

        if (!response.ok) {
            throw new Error('Lỗi khi xuất file: ' + response.statusText);
        }

        // Get filename from Content-Disposition header
        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = 'Thong_tin_cap_bang_cao_cap_LLCT.xlsx';
        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
            if (filenameMatch) {
                filename = filenameMatch[1];
            }
        }

        // Convert response to blob and trigger download
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();

        // Cleanup
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        hideLoading();
    } catch (error) {
        console.error('Export error:', error);
        hideLoading();
        alert('Có lỗi xảy ra khi xuất file: ' + error.message);
    }
}

// Export intermediate political theory info
async function exportIntermediatePoliticalTheoryInfo() {
    showLoading();

    try {
        const formData = new FormData(document.getElementById('diplomaFilters'));
        const params = new URLSearchParams(formData);

        // Use fetch to download file
        const response = await fetch(`/statistics/export-intermediate-political-theory-info?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            }
        });

        if (!response.ok) {
            throw new Error('Lỗi khi xuất file: ' + response.statusText);
        }

        // Get filename from Content-Disposition header
        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = 'Thong_tin_cap_bang_trung_cap_LLCT.xlsx';
        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
            if (filenameMatch) {
                filename = filenameMatch[1];
            }
        }

        // Convert response to blob and trigger download
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();

        // Cleanup
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        hideLoading();
    } catch (error) {
        console.error('Export error:', error);
        hideLoading();
        alert('Có lỗi xảy ra khi xuất file: ' + error.message);
    }
}

// Export all certificates info
async function exportAllCertificatesInfo() {
    showLoading();

    try {
        const formData = new FormData(document.getElementById('diplomaFilters'));
        const params = new URLSearchParams(formData);

        // Use fetch to download file
        const response = await fetch(`/statistics/export-all-certificates-info?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            }
        });

        if (!response.ok) {
            throw new Error('Lỗi khi xuất file: ' + response.statusText);
        }

        // Get filename from Content-Disposition header
        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = 'Thong_tin_cap_chung_chi.xlsx';
        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
            if (filenameMatch) {
                filename = filenameMatch[1];
            }
        }

        // Convert response to blob and trigger download
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();

        // Cleanup
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        hideLoading();
    } catch (error) {
        console.error('Export error:', error);
        hideLoading();
        alert('Có lỗi xảy ra khi xuất file: ' + error.message);
    }
}

// Expose functions to global scope for inline onclick handlers
window.exportBachelorInfo = exportBachelorInfo;
window.exportMasterInfo = exportMasterInfo;
window.exportDoctorateInfo = exportDoctorateInfo;
window.exportAdvancedPoliticalTheoryInfo = exportAdvancedPoliticalTheoryInfo;
window.exportIntermediatePoliticalTheoryInfo = exportIntermediatePoliticalTheoryInfo;
window.exportAllCertificatesInfo = exportAllCertificatesInfo;
window.applyDiplomaFilters = applyDiplomaFilters;
window.applyCertificateFilters = applyCertificateFilters;
window.resetDiplomaFilters = resetDiplomaFilters;
window.resetCertificateFilters = resetCertificateFilters;

function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    overlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    overlay.style.animation = 'overlayFadeOut 0.3s ease-in';
    overlay.querySelector('.loading-spinner').style.animation = 'spinnerSlideOut 0.2s ease-in';

    setTimeout(() => {
        overlay.classList.add('hidden');
        document.body.style.overflow = 'auto';
        overlay.style.animation = '';
        overlay.querySelector('.loading-spinner').style.animation = '';
    }, 300);
}
