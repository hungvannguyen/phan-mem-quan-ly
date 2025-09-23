@extends('layouts.default')

@section('content')
    <main class="diploma-blank-import">
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

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="import-form-section">
            <!-- Page Header -->
            <div class="import-page-header">
                <div class="header-content">
                    <h1 class="import-page-title">Nhập phôi văn bằng</h1>
                    <p class="import-page-subtitle">Nhập thông tin phôi văn bằng được X02 cấp vào hệ thống</p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('diploma-blank-management') }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại
                    </a>
                </div>
            </div>

            <!-- Import Form -->
            <div class="import-form-card">
                <form class="diploma-import-form" method="POST" action="{{ route('diploma-blank.import.store') }}"
                    id="importForm">
                    @csrf

                    <!-- Step 1: Chọn loại văn bằng -->
                    <div class="form-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <span class="step-number">1</span>
                                Thông tin cơ bản
                            </h3>
                        </div>

                        <div class="form-grid">
                            <div class="form-field">
                                <label for="type_id" class="field-label required">Loại văn bằng/chứng chỉ</label>
                                <select id="type_id" name="type_id" class="field-select" required>
                                    <option value="">-- Chọn loại văn bằng --</option>
                                    @foreach ($diplomaBlankTypes as $type)
                                        <option value="{{ $type->type_id }}"
                                            {{ old('type_id') == $type->type_id ? 'selected' : '' }}>
                                            {{ $type->type_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="field-error" id="type_id_error"></div>
                            </div>

                            <div class="form-field">
                                <label for="document_reference" class="field-label required">Văn bản đề xuất cấp
                                    phôi</label>
                                <input type="text" id="document_reference" name="document_reference" class="field-input"
                                    placeholder="VD: Số 123/QĐ-X02 ngày 15/09/2025" value="{{ old('document_reference') }}"
                                    required>
                                <div class="field-error" id="document_reference_error"></div>
                            </div>

                            <div class="form-field">
                                <label for="issue_date" class="field-label required">Ngày cấp</label>
                                <input type="date" id="issue_date" name="issue_date" class="field-input"
                                    value="{{ old('issue_date', date('Y-m-d')) }}" required>
                                <div class="field-error" id="issue_date_error"></div>
                            </div>

                            <div class="form-field">
                                <label for="quantity" class="field-label required">Số lượng phôi được cấp</label>
                                <input type="number" id="quantity" name="quantity" class="field-input"
                                    placeholder="Nhập số lượng phôi" value="{{ old('quantity') }}" min="1"
                                    max="10000" required>
                                <div class="field-error" id="quantity_error"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Serial Range -->
                    <div class="form-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <span class="step-number">2</span>
                                Thông tin Serial
                            </h3>
                            <div class="section-help">
                                <i class="fas fa-info-circle"></i>
                                <span>Serial có thể có định dạng: [Cố định 1][Số chạy], [Cố định 1][Số chạy][Cố định 2],
                                    hoặc [Số chạy][Cố định 2]</span>
                            </div>
                        </div>

                        <!-- From Serial -->
                        <div class="serial-section">
                            <h4 class="serial-title">Từ Serial</h4>
                            <div class="serial-input-group">
                                <div class="serial-field">
                                    <label for="from_prefix" class="field-label">Trường cố định 1</label>
                                    <input type="text" id="from_prefix" name="from_prefix"
                                        class="field-input serial-input" placeholder="VD: A."
                                        value="{{ old('from_prefix') }}">
                                </div>
                                <div class="serial-field">
                                    <label for="from_number" class="field-label required">Trường số chạy</label>
                                    <input type="text" id="from_number" name="from_number"
                                        class="field-input serial-input" placeholder="VD: 00001"
                                        value="{{ old('from_number') }}" required>
                                </div>
                                <div class="serial-field">
                                    <label for="from_suffix" class="field-label">Trường cố định 2</label>
                                    <input type="text" id="from_suffix" name="from_suffix"
                                        class="field-input serial-input" placeholder="VD: /X02CN"
                                        value="{{ old('from_suffix') }}">
                                </div>
                            </div>
                            <div class="serial-preview">
                                <label class="preview-label">Serial preview:</label>
                                <span class="preview-value" id="from_serial_preview">--</span>
                            </div>
                            <div class="field-error" id="from_serial_error"></div>
                        </div>

                        <!-- To Serial -->
                        <div class="serial-section">
                            <h4 class="serial-title">Đến Serial</h4>
                            <div class="serial-input-group">
                                <div class="serial-field">
                                    <label for="to_prefix" class="field-label">Trường cố định 1</label>
                                    <input type="text" id="to_prefix" name="to_prefix"
                                        class="field-input serial-input" placeholder="VD: A."
                                        value="{{ old('to_prefix') }}">
                                </div>
                                <div class="serial-field">
                                    <label for="to_number" class="field-label required">Trường số chạy</label>
                                    <input type="text" id="to_number" name="to_number"
                                        class="field-input serial-input" placeholder="VD: 00100"
                                        value="{{ old('to_number') }}" required>
                                </div>
                                <div class="serial-field">
                                    <label for="to_suffix" class="field-label">Trường cố định 2</label>
                                    <input type="text" id="to_suffix" name="to_suffix"
                                        class="field-input serial-input" placeholder="VD: /X02CN"
                                        value="{{ old('to_suffix') }}">
                                </div>
                            </div>
                            <div class="serial-preview">
                                <label class="preview-label">Serial preview:</label>
                                <span class="preview-value" id="to_serial_preview">--</span>
                            </div>
                            <div class="field-error" id="to_serial_error"></div>
                        </div>

                        <!-- Validation Info -->
                        <div class="validation-info">
                            <div class="validation-item">
                                <span class="validation-label">Số lượng tính toán:</span>
                                <span class="validation-value" id="calculated_quantity">--</span>
                            </div>
                            <div class="validation-item">
                                <span class="validation-label">Trạng thái kiểm tra:</span>
                                <span class="validation-value" id="validation_status">Chưa kiểm tra</span>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="button" class="btn-validate" id="validateBtn">
                            <i class="fas fa-check-circle"></i>
                            Kiểm tra dữ liệu
                        </button>
                        <button type="submit" class="btn-submit" id="submitBtn" disabled>
                            <i class="fas fa-save"></i>
                            Nhập phôi vào hệ thống
                        </button>
                        <a href="{{ route('diploma-blank-management') }}" class="btn-cancel">
                            <i class="fas fa-times"></i>
                            Hủy bỏ
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const form = document.getElementById('importForm');
            const validateBtn = document.getElementById('validateBtn');
            const submitBtn = document.getElementById('submitBtn');

            // Serial inputs
            const fromPrefix = document.getElementById('from_prefix');
            const fromNumber = document.getElementById('from_number');
            const fromSuffix = document.getElementById('from_suffix');
            const toPrefix = document.getElementById('to_prefix');
            const toNumber = document.getElementById('to_number');
            const toSuffix = document.getElementById('to_suffix');

            // Preview elements
            const fromPreview = document.getElementById('from_serial_preview');
            const toPreview = document.getElementById('to_serial_preview');
            const calculatedQuantity = document.getElementById('calculated_quantity');
            const validationStatus = document.getElementById('validation_status');
            const quantityInput = document.getElementById('quantity');

            let isValid = false;

            // Update serial previews
            function updatePreviews() {
                const fromSerial = (fromPrefix.value || '') + (fromNumber.value || '') + (fromSuffix.value || '');
                const toSerial = (toPrefix.value || '') + (toNumber.value || '') + (toSuffix.value || '');

                fromPreview.textContent = fromSerial || '--';
                toPreview.textContent = toSerial || '--';

                // Reset validation when inputs change
                isValid = false;
                submitBtn.disabled = true;
                validationStatus.textContent = 'Chưa kiểm tra';
                validationStatus.className = 'validation-value';
                calculatedQuantity.textContent = '--';
            }

            // Add event listeners for preview updates
            [fromPrefix, fromNumber, fromSuffix, toPrefix, toNumber, toSuffix].forEach(input => {
                input.addEventListener('input', updatePreviews);
            });

            // Validate serial range
            function validateSerialRange() {
                clearErrors();

                const data = {
                    from_prefix: fromPrefix.value,
                    from_number: fromNumber.value,
                    from_suffix: fromSuffix.value,
                    to_prefix: toPrefix.value,
                    to_number: toNumber.value,
                    to_suffix: toSuffix.value,
                    quantity: quantityInput.value
                };

                // Client-side validation first
                if (!data.from_number || !data.to_number) {
                    showError('from_serial_error', 'Trường số chạy là bắt buộc cho cả From và To Serial');
                    return;
                }

                // Check if fixed fields match
                if (data.from_prefix !== data.to_prefix) {
                    showError('to_serial_error', 'Trường cố định 1 của From Serial và To Serial không khớp');
                    return;
                }

                if (data.from_suffix !== data.to_suffix) {
                    showError('to_serial_error', 'Trường cố định 2 của From Serial và To Serial không khớp');
                    return;
                }

                // Calculate quantity from serial range
                const fromNum = parseInt(data.from_number);
                const toNum = parseInt(data.to_number);

                if (isNaN(fromNum) || isNaN(toNum)) {
                    showError('from_serial_error', 'Trường số chạy phải là số');
                    return;
                }

                if (fromNum >= toNum) {
                    showError('to_serial_error', 'Số chạy của To Serial phải lớn hơn From Serial');
                    return;
                }

                const calculatedQty = toNum - fromNum + 1;
                const inputQty = parseInt(data.quantity);

                calculatedQuantity.textContent = calculatedQty;

                if (calculatedQty !== inputQty) {
                    showError('quantity_error',
                        `Số lượng phôi không khớp. Tính từ serial: ${calculatedQty}, nhập vào: ${inputQty}`);
                    validationStatus.textContent = 'Lỗi: Số lượng không khớp';
                    validationStatus.className = 'validation-value error';
                    return;
                }

                // AJAX validation for duplicate serials
                fetch('{{ route('diploma-blank.validate-range') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            validationStatus.textContent = 'Hợp lệ - Sẵn sàng nhập phôi';
                            validationStatus.className = 'validation-value success';
                            isValid = true;
                            submitBtn.disabled = false;
                        } else {
                            showError('from_serial_error', result.message);
                            validationStatus.textContent = 'Lỗi: ' + result.message;
                            validationStatus.className = 'validation-value error';
                        }
                    })
                    .catch(error => {
                        console.error('Validation error:', error);
                        showError('from_serial_error', 'Lỗi kết nối. Vui lòng thử lại.');
                        validationStatus.textContent = 'Lỗi kết nối';
                        validationStatus.className = 'validation-value error';
                    });
            }

            // Show error message
            function showError(elementId, message) {
                const errorElement = document.getElementById(elementId);
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.style.display = 'block';
                }
            }

            // Clear all errors
            function clearErrors() {
                const errorElements = document.querySelectorAll('.field-error');
                errorElements.forEach(element => {
                    element.textContent = '';
                    element.style.display = 'none';
                });
            }

            // Event listeners
            validateBtn.addEventListener('click', validateSerialRange);

            form.addEventListener('submit', function(e) {
                if (!isValid) {
                    e.preventDefault();
                    alert('Vui lòng kiểm tra dữ liệu trước khi nhập phôi');
                }
            });

            // Initial preview update
            updatePreviews();
        });
    </script>
@endsection
