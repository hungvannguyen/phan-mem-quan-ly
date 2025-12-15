@extends('layouts.default')

@section('content')
    <main class="diploma-management">
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

        <div class="diploma-form-section">
            <!-- Page Header -->
            <div class="diploma-page-header">
                <div class="header-with-nav">
                    <div class="breadcrumb">
                        <a href="{{ route('diploma-blank-import.index') }}" class="breadcrumb-item">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách phôi
                        </a>
                    </div>
                    <h1 class="diploma-page-title">Nhập phôi văn bằng mới</h1>
                    <p class="diploma-page-subtitle">Nhập thông tin phôi văn bằng được cấp từ X02</p>
                </div>
            </div>

            <!-- Import Form -->
            <div class="diploma-search-card">
                <form class="diploma-import-form" method="POST" action="{{ route('diploma-blanks.process-import') }}">
                    @csrf

                    <!-- Step 1: Chọn loại văn bằng -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <span class="step-number">1</span>
                            Thông tin cơ bản
                        </h3>

                        <div class="form-grid-2">
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
                            </div>

                            <x-vietnamese-date-input id="import_date" name="import_date" label="Ngày cấp" :required="true"
                                value="{{ old('import_date', date('Y-m-d')) }}" />
                        </div>

                        <div class="form-field">
                            <label for="document_request" class="field-label required">Văn bản đề xuất cấp phôi</label>
                            <input type="text" id="document_request" name="document_request" class="field-input"
                                placeholder="Nhập số/tên văn bản đề xuất cấp phôi" value="{{ old('document_request') }}"
                                required>
                        </div>
                    </div>

                    <!-- Step 2: Thông tin số lượng -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <span class="step-number">2</span>
                            Số lượng phôi
                        </h3>

                        <div class="form-field">
                            <label for="quantity" class="field-label required">Số lượng phôi được cấp</label>
                            <input type="number" id="quantity" name="quantity" class="field-input"
                                placeholder="Nhập số lượng phôi" value="{{ old('quantity') }}" min="1"
                                max="10000" required>
                            <small class="field-help">Số lượng từ 1 đến 10,000 phôi</small>
                        </div>
                    </div>

                    <!-- Step 3: Serial từ -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <span class="step-number">3</span>
                            Serial bắt đầu (Từ Serial)
                        </h3>

                        <div class="serial-group">
                            <div class="serial-fields">
                                <div class="form-field">
                                    <label for="from_prefix" class="field-label">Trường cố định 1</label>
                                    <input type="text" id="from_prefix" name="from_prefix" class="field-input"
                                        placeholder="VD: A." value="{{ old('from_prefix') }}">
                                    <small class="field-help">Có thể để trống</small>
                                </div>

                                <div class="form-field">
                                    <label for="from_number" class="field-label required">Trường số chạy</label>
                                    <input type="text" id="from_number" name="from_number" class="field-input"
                                        placeholder="VD: 00001" value="{{ old('from_number') }}" required>
                                    <small class="field-help">Bắt buộc nhập</small>
                                </div>

                                <div class="form-field">
                                    <label for="from_suffix" class="field-label">Trường cố định 2</label>
                                    <input type="text" id="from_suffix" name="from_suffix" class="field-input"
                                        placeholder="VD: /X02CN" value="{{ old('from_suffix') }}">
                                    <small class="field-help">Có thể để trống</small>
                                </div>
                            </div>

                            <div class="serial-preview">
                                <label class="field-label">Preview Serial:</label>
                                <div class="preview-box" id="from-serial-preview">
                                    <span class="preview-text">Nhập thông tin để xem preview</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Serial đến -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <span class="step-number">4</span>
                            Serial kết thúc (Đến Serial)
                        </h3>

                        <div class="serial-group">
                            <div class="serial-fields">
                                <div class="form-field">
                                    <label for="to_prefix" class="field-label">Trường cố định 1</label>
                                    <input type="text" id="to_prefix" name="to_prefix" class="field-input"
                                        placeholder="VD: A." value="{{ old('to_prefix') }}">
                                    <small class="field-help">Phải khớp với "Từ Serial"</small>
                                </div>

                                <div class="form-field">
                                    <label for="to_number" class="field-label required">Trường số chạy</label>
                                    <input type="text" id="to_number" name="to_number" class="field-input"
                                        placeholder="VD: 00100" value="{{ old('to_number') }}" required>
                                    <small class="field-help">Bắt buộc nhập</small>
                                </div>

                                <div class="form-field">
                                    <label for="to_suffix" class="field-label">Trường cố định 2</label>
                                    <input type="text" id="to_suffix" name="to_suffix" class="field-input"
                                        placeholder="VD: /X02CN" value="{{ old('to_suffix') }}">
                                    <small class="field-help">Phải khớp với "Từ Serial"</small>
                                </div>
                            </div>

                            <div class="serial-preview">
                                <label class="field-label">Preview Serial:</label>
                                <div class="preview-box" id="to-serial-preview">
                                    <span class="preview-text">Nhập thông tin để xem preview</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Validation Summary -->
                    <div class="form-section">
                        <div class="validation-summary" id="validation-summary">
                            <h4><i class="fas fa-info-circle"></i> Kiểm tra thông tin</h4>
                            <div class="validation-item" id="validation-fixed-fields">
                                <i class="fas fa-circle text-muted"></i>
                                <span>Trường cố định giữa "Từ Serial" và "Đến Serial"</span>
                            </div>
                            <div class="validation-item" id="validation-quantity">
                                <i class="fas fa-circle text-muted"></i>
                                <span>Số lượng phôi và khoảng Serial</span>
                            </div>
                            <div class="validation-item" id="validation-duplicates">
                                <i class="fas fa-circle text-muted"></i>
                                <span>Kiểm tra trùng lặp với Serial hiện có</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <button type="button" id="validate-btn" class="btn-secondary">
                            <i class="fas fa-check"></i>
                            Kiểm tra dữ liệu
                        </button>
                        <button type="submit" id="submit-btn" class="btn-primary" disabled>
                            <i class="fas fa-save"></i>
                            Nhập phôi vào hệ thống
                        </button>
                        <a href="{{ route('diploma-blank-import.index') }}" class="btn-cancel">
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
            const fromPrefix = document.getElementById('from_prefix');
            const fromNumber = document.getElementById('from_number');
            const fromSuffix = document.getElementById('from_suffix');
            const toPrefix = document.getElementById('to_prefix');
            const toNumber = document.getElementById('to_number');
            const toSuffix = document.getElementById('to_suffix');
            const quantity = document.getElementById('quantity');

            const fromPreview = document.getElementById('from-serial-preview');
            const toPreview = document.getElementById('to-serial-preview');
            const validateBtn = document.getElementById('validate-btn');
            const submitBtn = document.getElementById('submit-btn');

            // Validation elements
            const validationFixedFields = document.getElementById('validation-fixed-fields');
            const validationQuantity = document.getElementById('validation-quantity');

            // Update preview function
            function updatePreviews() {
                const fromSerial = (fromPrefix.value || '') + (fromNumber.value || '') + (fromSuffix.value || '');
                const toSerial = (toPrefix.value || '') + (toNumber.value || '') + (toSuffix.value || '');

                fromPreview.innerHTML = fromSerial ?
                    `<span class="serial-complete">${fromSerial}</span>` :
                    '<span class="preview-text">Nhập thông tin để xem preview</span>';

                toPreview.innerHTML = toSerial ?
                    `<span class="serial-complete">${toSerial}</span>` :
                    '<span class="preview-text">Nhập thông tin để xem preview</span>';
            }

            // Validation function
            function validateForm() {
                let isValid = true;

                // Check fixed fields match
                const fixedFieldsMatch = (fromPrefix.value === toPrefix.value) && (fromSuffix.value === toSuffix
                    .value);

                if (fixedFieldsMatch) {
                    validationFixedFields.querySelector('i').className = 'fas fa-check-circle text-success';
                    validationFixedFields.querySelector('span').textContent = 'Trường cố định khớp nhau ✓';
                } else {
                    validationFixedFields.querySelector('i').className = 'fas fa-times-circle text-danger';
                    validationFixedFields.querySelector('span').textContent = 'Trường cố định không khớp ✗';
                    isValid = false;
                }

                // Check quantity matches serial range
                const fromNum = parseInt(fromNumber.value) || 0;
                const toNum = parseInt(toNumber.value) || 0;
                const quantityValue = parseInt(quantity.value) || 0;
                const serialRange = toNum - fromNum + 1;

                if (quantityValue > 0 && serialRange === quantityValue) {
                    validationQuantity.querySelector('i').className = 'fas fa-check-circle text-success';
                    validationQuantity.querySelector('span').textContent =
                        `Số lượng khớp với khoảng Serial (${quantityValue} phôi) ✓`;
                } else if (quantityValue > 0) {
                    validationQuantity.querySelector('i').className = 'fas fa-times-circle text-danger';
                    validationQuantity.querySelector('span').textContent =
                        `Số lượng không khớp: ${quantityValue} phôi vs ${serialRange} serial ✗`;
                    isValid = false;
                } else {
                    validationQuantity.querySelector('i').className = 'fas fa-circle text-muted';
                    validationQuantity.querySelector('span').textContent = 'Nhập đầy đủ thông tin để kiểm tra';
                    isValid = false;
                }

                submitBtn.disabled = !isValid;
                return isValid;
            }

            // Event listeners
            [fromPrefix, fromNumber, fromSuffix, toPrefix, toNumber, toSuffix].forEach(input => {
                input.addEventListener('input', updatePreviews);
                input.addEventListener('input', validateForm);
            });

            quantity.addEventListener('input', validateForm);

            validateBtn.addEventListener('click', function() {
                if (!validateForm()) {
                    return;
                }

                // Check for duplicates via AJAX
                const validationDuplicates = document.getElementById('validation-duplicates');
                validationDuplicates.querySelector('i').className = 'fas fa-spinner fa-spin text-info';
                validationDuplicates.querySelector('span').textContent = 'Đang kiểm tra trùng lặp...';

                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content'));
                formData.append('from_prefix', fromPrefix.value);
                formData.append('from_number', fromNumber.value);
                formData.append('from_suffix', fromSuffix.value);
                formData.append('to_number', toNumber.value);

                fetch('{{ route('diploma-blanks.check-duplicates') }}', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.hasDuplicates) {
                            validationDuplicates.querySelector('i').className =
                                'fas fa-times-circle text-danger';
                            validationDuplicates.querySelector('span').textContent =
                                `Có ${data.duplicates.length} Serial trùng lặp ✗`;
                            submitBtn.disabled = true;
                        } else {
                            validationDuplicates.querySelector('i').className =
                                'fas fa-check-circle text-success';
                            validationDuplicates.querySelector('span').textContent =
                                `Đã kiểm tra ${data.total_checked} Serial - Không trùng lặp ✓`;
                            submitBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        validationDuplicates.querySelector('i').className =
                            'fas fa-exclamation-circle text-warning';
                        validationDuplicates.querySelector('span').textContent =
                            'Lỗi kiểm tra - Vui lòng thử lại';
                        console.error('Error:', error);
                    });
            });

            // Auto-sync fixed fields
            fromPrefix.addEventListener('input', function() {
                if (!toPrefix.value) toPrefix.value = fromPrefix.value;
            });

            fromSuffix.addEventListener('input', function() {
                if (!toSuffix.value) toSuffix.value = fromSuffix.value;
            });

            // Initial validation
            updatePreviews();
            validateForm();
        });
    </script>
@endsection
