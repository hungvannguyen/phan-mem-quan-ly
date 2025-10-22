@extends('layouts.default')

@section('content')
    <main class="recall-page">
        {{-- Hiển thị thông báo --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="form-section">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Thu hồi Phôi Văn bằng</h1>
                <p class="page-subtitle">Thu hồi các phôi văn bằng đã cấp khi cần thiết</p>
            </div>

            <!-- Action Buttons -->
            <div class="page-actions">
                <a href="{{ route('diploma-blank-recalls.management') }}" class="action-btn action-btn-info">
                    <i class="fas fa-list"></i> Xem phôi đã thu hồi
                </a>
                <a href="{{ route('diploma-blank-management') }}" class="action-btn action-btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại quản lý
                </a>
            </div>

            <!-- Recall Form -->
            <div class="recall-card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin thu hồi phôi</h3>
                </div>

                <form id="recallForm" class="recall-form">
                    @csrf

                    <!-- Step 1: Nhập Serial Number -->
                    <div class="form-step" id="step1">
                        <h4 class="step-title">Bước 1: Nhập thông tin phôi cần thu hồi</h4>

                        <div class="form-field">
                            <label for="serial_number" class="field-label required">
                                Số Serial phôi
                            </label>
                            <div class="input-group">
                                <input type="text" id="serial_number" name="serial_number" class="field-input"
                                    placeholder="Nhập số serial phôi cần thu hồi" required>
                                <button type="button" id="checkSerialBtn" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Kiểm tra
                                </button>
                            </div>
                            <small class="form-text">
                                Nhập đúng số serial của phôi đã cấp cần thu hồi
                            </small>
                        </div>

                        <!-- Loading indicator -->
                        <div id="checkingLoader" class="loading-indicator" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...
                        </div>

                        <!-- Error display -->
                        <div id="serialError" class="alert alert-danger" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span id="serialErrorMessage"></span>
                        </div>

                        <!-- Success display -->
                        <div id="serialSuccess" class="alert alert-success" style="display: none;">
                            <i class="fas fa-check-circle"></i>
                            <span id="serialSuccessMessage"></span>
                        </div>
                    </div>

                    <!-- Step 2: Hiển thị thông tin phôi và nhập lý do -->
                    <div class="form-step" id="step2" style="display: none;">
                        <h4 class="step-title">Bước 2: Xác nhận thông tin và nhập lý do thu hồi</h4>

                        <!-- Hiển thị thông tin phôi -->
                        <div class="diploma-info-card">
                            <h5 class="info-title">Thông tin phôi</h5>
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Số Serial:</label>
                                    <span id="displaySerial"></span>
                                </div>
                                <div class="info-item">
                                    <label>Loại phôi:</label>
                                    <span id="displayType"></span>
                                </div>
                                <div class="info-item">
                                    <label>Ngày cấp:</label>
                                    <span id="displayIssueDate"></span>
                                </div>
                                <div class="info-item">
                                    <label>Trạng thái:</label>
                                    <span id="displayStatus" class="status-badge"></span>
                                </div>
                            </div>

                            <!-- Thông tin sinh viên (nếu có) -->
                            <div id="studentInfo" style="display: none;">
                                <h6 class="info-subtitle">Thông tin sinh viên</h6>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <label>Tên sinh viên:</label>
                                        <span id="displayStudentName"></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Ngành:</label>
                                        <span id="displayMajor"></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Năm tốt nghiệp:</label>
                                        <span id="displayGraduationYear"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nhập lý do thu hồi -->
                        <div class="form-field">
                            <label for="recall_reason" class="field-label required">
                                Lý do thu hồi
                            </label>
                            <textarea id="recall_reason" name="recall_reason" class="field-textarea" rows="4"
                                placeholder="Nhập lý do thu hồi phôi..." required></textarea>
                            <small class="form-text">
                                Mô tả chi tiết lý do tại sao cần thu hồi phôi này
                            </small>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="button" id="backBtn" class="btn btn-secondary" style="display: none;">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </button>
                        <button type="submit" id="recallBtn" class="btn btn-danger" style="display: none;">
                            <i class="fas fa-undo"></i> Thu hồi phôi
                        </button>
                        <button type="button" id="resetBtn" class="btn btn-outline-secondary">
                            <i class="fas fa-refresh"></i> Làm mới
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    {{-- Styles are now handled by SCSS component: resources/scss/components/_recall-form.scss --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('recallForm');
            const serialInput = document.getElementById('serial_number');
            const checkSerialBtn = document.getElementById('checkSerialBtn');
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            const backBtn = document.getElementById('backBtn');
            const recallBtn = document.getElementById('recallBtn');
            const resetBtn = document.getElementById('resetBtn');
            const checkingLoader = document.getElementById('checkingLoader');
            const serialError = document.getElementById('serialError');
            const serialSuccess = document.getElementById('serialSuccess');

            let currentDiplomaData = null;

            // Check serial number
            checkSerialBtn.addEventListener('click', async function() {
                const serialNumber = serialInput.value.trim();

                if (!serialNumber) {
                    showError('Vui lòng nhập số serial phôi.');
                    return;
                }

                hideMessages();
                showLoading(true);
                checkSerialBtn.disabled = true;

                try {
                    const response = await fetch('{{ route('diploma-blank-recalls.check-serial') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            serial_number: serialNumber
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        currentDiplomaData = result.data;
                        showSuccess(result.message);
                        displayDiplomaInfo(result.data);
                        showStep2();
                    } else {
                        showError(result.message);
                        currentDiplomaData = null;
                    }
                } catch (error) {
                    showError('Có lỗi xảy ra khi kiểm tra phôi. Vui lòng thử lại.');
                    console.error('Error checking serial:', error);
                } finally {
                    showLoading(false);
                    checkSerialBtn.disabled = false;
                }
            });

            // Handle form submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const serialNumber = serialInput.value.trim();
                const recallReason = document.getElementById('recall_reason').value.trim();

                if (!serialNumber || !recallReason) {
                    alert('Vui lòng điền đầy đủ thông tin.');
                    return;
                }

                if (!currentDiplomaData) {
                    alert('Vui lòng kiểm tra thông tin phôi trước khi thu hồi.');
                    return;
                }

                if (!confirm('Bạn có chắc chắn muốn thu hồi phôi này?')) {
                    return;
                }

                recallBtn.disabled = true;
                recallBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thu hồi...';

                try {
                    const response = await fetch('{{ route('diploma-blank-recalls.recall') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            serial_number: serialNumber,
                            recall_reason: recallReason
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert('Thu hồi phôi thành công!');
                        resetForm();
                    } else {
                        alert('Lỗi: ' + result.message);
                    }
                } catch (error) {
                    alert('Có lỗi xảy ra khi thu hồi phôi. Vui lòng thử lại.');
                    console.error('Error recalling diploma:', error);
                } finally {
                    recallBtn.disabled = false;
                    recallBtn.innerHTML = '<i class="fas fa-undo"></i> Thu hồi phôi';
                }
            });

            // Back button
            backBtn.addEventListener('click', function() {
                showStep1();
            });

            // Reset button
            resetBtn.addEventListener('click', function() {
                resetForm();
            });

            // Enter key support for serial input
            serialInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    checkSerialBtn.click();
                }
            });

            // Helper functions
            function showLoading(show) {
                checkingLoader.style.display = show ? 'block' : 'none';
            }

            function showError(message) {
                document.getElementById('serialErrorMessage').textContent = message;
                serialError.style.display = 'block';
                serialSuccess.style.display = 'none';
            }

            function showSuccess(message) {
                document.getElementById('serialSuccessMessage').textContent = message;
                serialSuccess.style.display = 'block';
                serialError.style.display = 'none';
            }

            function hideMessages() {
                serialError.style.display = 'none';
                serialSuccess.style.display = 'none';
            }

            function displayDiplomaInfo(data) {
                document.getElementById('displaySerial').textContent = data.serial_number;
                document.getElementById('displayType').textContent = data.type_name;
                document.getElementById('displayIssueDate').textContent = data.issue_date || 'N/A';

                const statusSpan = document.getElementById('displayStatus');
                statusSpan.textContent = data.status;
                statusSpan.className = 'status-badge status-completed';

                // Show student info if available
                if (data.degree_info) {
                    document.getElementById('displayStudentName').textContent = data.degree_info.student_name;
                    document.getElementById('displayMajor').textContent = data.degree_info.major_name;
                    document.getElementById('displayGraduationYear').textContent = data.degree_info.graduation_year;
                    document.getElementById('studentInfo').style.display = 'block';
                } else {
                    document.getElementById('studentInfo').style.display = 'none';
                }
            }

            function showStep1() {
                step1.style.display = 'block';
                step2.style.display = 'none';
                backBtn.style.display = 'none';
                recallBtn.style.display = 'none';
                hideMessages();
            }

            function showStep2() {
                step1.style.display = 'none';
                step2.style.display = 'block';
                backBtn.style.display = 'inline-flex';
                recallBtn.style.display = 'inline-flex';
            }

            function resetForm() {
                form.reset();
                currentDiplomaData = null;
                showStep1();
                hideMessages();
            }
        });
    </script>
@endsection
