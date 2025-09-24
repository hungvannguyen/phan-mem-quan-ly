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

                        <!-- Serial Range -->
                        <div class="serial-section">
                            <h4 class="serial-title">Cấu hình Serial Range</h4>

                            <!-- Serial Structure -->
                            <div class="serial-input-group">
                                <div class="serial-field">
                                    <label for="prefix" class="field-label">Trường cố định 1</label>
                                    <input type="text" id="prefix" name="prefix" class="field-input serial-input"
                                        placeholder="VD: A." value="{{ old('prefix') }}">
                                    <div class="field-error" id="prefix_error"></div>
                                </div>


                                <div class="serial-field">
                                    <label for="from_number" class="field-label required">Từ số</label>
                                    <input type="text" id="from_number" name="from_number"
                                        class="field-input serial-input" placeholder="VD: 00001"
                                        value="{{ old('from_number') }}" required>
                                    <div class="field-error" id="from_number_error"></div>
                                </div>


                                <div class="serial-field">
                                    <label for="to_number" class="field-label required">Đến số</label>
                                    <input type="text" id="to_number" name="to_number"
                                        class="field-input serial-input" placeholder="VD: 00100"
                                        value="{{ old('to_number') }}" required>
                                    <div class="field-error" id="to_number_error"></div>
                                </div>


                                <div class="serial-field">
                                    <label for="suffix" class="field-label">Trường cố định 2</label>
                                    <input type="text" id="suffix" name="suffix" class="field-input serial-input"
                                        placeholder="VD: /X02CN" value="{{ old('suffix') }}">
                                    <div class="field-error" id="suffix_error"></div>
                                </div>
                            </div>

                            <!-- Serial Preview -->
                            <div class="serial-preview">
                                <label class="preview-label">Serial range preview:</label>
                                <div class="preview-range">
                                    <span class="preview-value" id="from_serial_preview">--</span>
                                    <span class="range-arrow">→</span>
                                    <span class="preview-value" id="to_serial_preview">--</span>
                                </div>
                            </div>

                            <div class="field-error" id="serial_error"></div>
                        </div>

                        <!-- Validation Info -->
                        <div class="validation-info">
                            <div class="validation-item">
                                <span class="validation-label">Số lượng tính toán:</span>
                                <span class="validation-value" id="calculated_quantity">--</span>
                            </div>
                            <div class="field-error" id="quantity_validation_error"></div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
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

    <style>
        .serial-range-group {
            display: flex;
            align-items: flex-end;
            gap: 15px;
            margin: 10px 0;
        }

        .serial-range-group .serial-field {
            flex: 1;
        }

        .range-separator {
            padding: 0 10px;
            font-weight: 500;
            color: #666;
            margin-bottom: 5px;
            white-space: nowrap;
        }

        .preview-range {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 5px;
        }

        .range-arrow {
            font-size: 18px;
            color: #666;
            font-weight: bold;
        }

        .preview-value {
            font-family: monospace;
            padding: 5px 10px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            min-width: 80px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const form = document.getElementById('importForm');
            const submitBtn = form.querySelector('.btn-submit');

            // Basic form inputs
            const typeId = document.getElementById('type_id');
            const documentReference = document.getElementById('document_reference');
            const issueDate = document.getElementById('issue_date');
            const quantityInput = document.getElementById('quantity');

            // Serial inputs
            const prefix = document.getElementById('prefix');
            const fromNumber = document.getElementById('from_number');
            const toNumber = document.getElementById('to_number');
            const suffix = document.getElementById('suffix');

            // Preview elements
            const fromPreview = document.getElementById('from_serial_preview');
            const toPreview = document.getElementById('to_serial_preview');
            const calculatedQuantity = document.getElementById('calculated_quantity');

            // Error display functions
            function showError(elementId, message) {
                const errorElement = document.getElementById(elementId + '_error');
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.style.display = 'block';
                    errorElement.style.color = 'red';
                }

                // Also show quantity error in validation area
                if (elementId === 'quantity') {
                    const validationError = document.getElementById('quantity_validation_error');
                    if (validationError) {
                        validationError.textContent = message;
                        validationError.style.display = 'block';
                        validationError.style.color = 'red';
                    }
                }
            }

            function clearError(elementId) {
                const errorElement = document.getElementById(elementId + '_error');
                if (errorElement) {
                    errorElement.textContent = '';
                    errorElement.style.display = 'none';
                }

                // Also clear quantity error in validation area
                if (elementId === 'quantity') {
                    const validationError = document.getElementById('quantity_validation_error');
                    if (validationError) {
                        validationError.textContent = '';
                        validationError.style.display = 'none';
                    }
                }
            }

            function clearAllErrors() {
                const errorIds = ['type_id', 'document_reference', 'issue_date', 'quantity', 'serial',
                    'prefix', 'from_number', 'to_number', 'suffix'
                ];
                errorIds.forEach(id => clearError(id));

                // Also clear quantity validation error
                const quantityValidationError = document.getElementById('quantity_validation_error');
                if (quantityValidationError) {
                    quantityValidationError.textContent = '';
                    quantityValidationError.style.display = 'none';
                }
            }

            // Update serial previews
            function updatePreviews() {
                const fromSerial = (prefix.value || '') + (fromNumber.value || '') + (suffix.value || '');
                const toSerial = (prefix.value || '') + (toNumber.value || '') + (suffix.value || '');

                fromPreview.textContent = fromSerial || '--';
                toPreview.textContent = toSerial || '--';

                // Calculate quantity if both numbers are provided
                if (fromNumber.value && toNumber.value) {
                    const fromNum = parseInt(fromNumber.value);
                    const toNum = parseInt(toNumber.value);

                    if (!isNaN(fromNum) && !isNaN(toNum) && toNum >= fromNum) {
                        const calculatedQty = toNum - fromNum + 1;
                        calculatedQuantity.textContent = calculatedQty;

                        // Check quantity matching and show error if needed
                        if (quantityInput.value) {
                            const inputQty = parseInt(quantityInput.value);
                            if (!isNaN(inputQty) && calculatedQty !== inputQty) {
                                showError('quantity',
                                    `Số lượng không khớp! Tính từ serial: ${calculatedQty}, nhập vào: ${inputQty}`
                                );
                            } else if (calculatedQty === inputQty) {
                                clearError('quantity');
                            }
                        }
                    } else {
                        calculatedQuantity.textContent = '--';
                    }
                } else {
                    calculatedQuantity.textContent = '--';
                    // Clear quantity error when serial inputs are incomplete
                    if (quantityInput.value) {
                        clearError('quantity');
                    }
                }
            }

            // Form validation
            function validateForm() {
                let isValid = true;

                // 1. Validate basic required fields
                if (!typeId.value) {
                    isValid = false;
                }

                if (!documentReference.value.trim()) {
                    isValid = false;
                }

                if (!issueDate.value) {
                    isValid = false;
                }

                if (!quantityInput.value || parseInt(quantityInput.value) <= 0) {
                    isValid = false;
                }

                // 2. Validate serial numbers
                if (!fromNumber.value.trim()) {
                    isValid = false;
                }

                if (!toNumber.value.trim()) {
                    isValid = false;
                }

                // 3. Validate that at least one prefix/suffix exists
                const hasPrefix = prefix.value.trim();
                const hasSuffix = suffix.value.trim();
                if (!hasPrefix && !hasSuffix) {
                    isValid = false;
                }

                // 4. Validate number range and quantity matching
                if (fromNumber.value && toNumber.value) {
                    const fromNum = parseInt(fromNumber.value);
                    const toNum = parseInt(toNumber.value);

                    if (isNaN(fromNum) || isNaN(toNum)) {
                        isValid = false;
                    } else if (fromNum >= toNum) {
                        isValid = false;
                    } else {
                        // 5. Validate quantity matching - MUST match calculated quantity
                        const calculatedQty = toNum - fromNum + 1;
                        const inputQty = parseInt(quantityInput.value);

                        if (calculatedQty !== inputQty) {
                            isValid = false;
                        }
                    }
                }

                // Update submit button state
                submitBtn.disabled = !isValid;

                if (isValid) {
                    submitBtn.style.opacity = '1';
                    submitBtn.style.cursor = 'pointer';
                } else {
                    submitBtn.style.opacity = '0.6';
                    submitBtn.style.cursor = 'not-allowed';
                }

                return isValid;
            }

            // Validate individual field on blur (when user leaves field)
            function validateFieldOnBlur(field) {
                const fieldId = field.id;

                // Clear existing error first
                clearError(fieldId);

                switch (fieldId) {
                    case 'type_id':
                        if (!field.value) {
                            showError('type_id', 'Vui lòng chọn loại văn bằng/chứng chỉ');
                        }
                        break;

                    case 'document_reference':
                        if (!field.value.trim()) {
                            showError('document_reference', 'Vui lòng nhập văn bản đề xuất cấp phôi');
                        }
                        break;

                    case 'issue_date':
                        if (!field.value) {
                            showError('issue_date', 'Vui lòng chọn ngày cấp');
                        }
                        break;

                    case 'quantity':
                        if (!field.value || parseInt(field.value) <= 0) {
                            showError('quantity', 'Vui lòng nhập số lượng phôi hợp lệ (lớn hơn 0)');
                        } else if (fromNumber.value && toNumber.value) {
                            // Check quantity matching when quantity field is validated
                            const fromNum = parseInt(fromNumber.value);
                            const toNum = parseInt(toNumber.value);

                            if (!isNaN(fromNum) && !isNaN(toNum) && toNum > fromNum) {
                                const calculatedQty = toNum - fromNum + 1;
                                const inputQty = parseInt(field.value);

                                if (calculatedQty !== inputQty) {
                                    showError('quantity',
                                        `Số lượng không khớp! Tính từ serial: ${calculatedQty}, nhập vào: ${inputQty}`
                                    );
                                }
                            }
                        }
                        break;

                    case 'from_number':
                        if (!field.value.trim()) {
                            showError('from_number', 'Vui lòng nhập số bắt đầu');
                        } else if (isNaN(parseInt(field.value))) {
                            showError('from_number', 'Số bắt đầu phải là số hợp lệ');
                        } else if (toNumber.value) {
                            // Check range validation
                            const fromNum = parseInt(field.value);
                            const toNum = parseInt(toNumber.value);

                            if (fromNum >= toNum) {
                                showError('from_number', 'Số bắt đầu phải nhỏ hơn số kết thúc');
                            } else if (quantityInput.value) {
                                // Check quantity matching when serial changes
                                const calculatedQty = toNum - fromNum + 1;
                                const inputQty = parseInt(quantityInput.value);

                                if (!isNaN(inputQty) && calculatedQty !== inputQty) {
                                    showError('quantity',
                                        `Số lượng không khớp! Tính từ serial: ${calculatedQty}, nhập vào: ${inputQty}`
                                    );
                                } else if (calculatedQty === inputQty) {
                                    clearError('quantity');
                                }
                            }
                        }
                        break;

                    case 'to_number':
                        if (!field.value.trim()) {
                            showError('to_number', 'Vui lòng nhập số kết thúc');
                        } else if (isNaN(parseInt(field.value))) {
                            showError('to_number', 'Số kết thúc phải là số hợp lệ');
                        } else {
                            // Check range validation
                            if (fromNumber.value) {
                                const fromNum = parseInt(fromNumber.value);
                                const toNum = parseInt(field.value);

                                if (fromNum >= toNum) {
                                    showError('to_number', 'Số kết thúc phải lớn hơn số bắt đầu');
                                } else if (quantityInput.value) {
                                    // Check quantity matching when serial changes
                                    const calculatedQty = toNum - fromNum + 1;
                                    const inputQty = parseInt(quantityInput.value);

                                    if (!isNaN(inputQty) && calculatedQty !== inputQty) {
                                        showError('quantity',
                                            `Số lượng không khớp! Tính từ serial: ${calculatedQty}, nhập vào: ${inputQty}`
                                        );
                                    } else if (calculatedQty === inputQty) {
                                        clearError('quantity');
                                    }
                                }
                            }
                        }
                        break;

                    case 'prefix':
                        // Check if at least one prefix/suffix exists
                        const hasPrefix = field.value.trim();
                        const hasSuffix = suffix.value.trim();
                        if (!hasPrefix && !hasSuffix) {
                            showError('prefix',
                                'Phải có ít nhất một trong hai: Trường cố định 1 hoặc Trường cố định 2');
                        }
                        break;

                    case 'suffix':
                        // Check if at least one prefix/suffix exists
                        const hasPrefixS = prefix.value.trim();
                        const hasSuffixS = field.value.trim();
                        if (!hasPrefixS && !hasSuffixS) {
                            showError('suffix',
                                'Phải có ít nhất một trong hai: Trường cố định 1 hoặc Trường cố định 2');
                        }
                        break;
                }

                // Re-validate form after individual field validation
                validateForm();
            }

            // Auto-fill functionality (không cần thiết nữa vì chỉ có một set trường cố định)
            // function autoFillToFields() - Removed

            // Event listeners
            const allInputs = [typeId, documentReference, issueDate, quantityInput, prefix, fromNumber,
                toNumber, suffix
            ];

            allInputs.forEach(input => {
                // Only update previews and validate form state on input (no error display)
                input.addEventListener('input', function() {
                    updatePreviews();
                    validateForm(); // Only for button state, no error display
                });

                // Show errors only on blur (when user leaves field)
                input.addEventListener('blur', function() {
                    validateFieldOnBlur(this);
                });
            });

            // Form submit validation
            form.addEventListener('submit', function(e) {
                // Clear all errors first
                clearAllErrors();

                // Validate all fields and show errors
                let hasErrors = false;

                allInputs.forEach(input => {
                    validateFieldOnBlur(input);

                    // Check if this field has errors
                    const errorElement = document.getElementById(input.id + '_error');
                    if (errorElement && errorElement.textContent && errorElement.style.display ===
                        'block') {
                        hasErrors = true;
                    }
                });

                if (hasErrors || !validateForm()) {
                    e.preventDefault();
                    alert('Vui lòng kiểm tra và điền đầy đủ thông tin trước khi submit!');

                    // Focus on first error field
                    const firstError = form.querySelector('.field-error[style*="block"]');
                    if (firstError) {
                        const fieldId = firstError.id.replace('_error', '');
                        const field = document.getElementById(fieldId);
                        if (field) {
                            field.focus();
                        }
                    }
                }
            });

            // Initial setup - only update previews and button state, no error display
            updatePreviews();
            validateForm(); // Only for initial button state
        });
    </script>
@endsection
