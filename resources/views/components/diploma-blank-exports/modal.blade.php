{{-- Modal Xuất phôi --}}
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true"
    style="z-index: 9999;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Xuất phôi văn bằng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="exportForm" action="{{ route('diploma-blank-exports.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_type_id" class="form-label">Loại văn bằng <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="export_type_id" name="type_id" required>
                                    <option value="">-- Chọn loại văn bằng --</option>
                                    @foreach ($diplomaBlankTypes as $type)
                                        <option value="{{ $type->type_id }}">{{ $type->type_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_course" class="form-label">Khóa</label>
                                <input type="text" class="form-control" id="export_course" name="course"
                                    placeholder="Nhập khóa học">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_graduation_year" class="form-label">Năm tốt nghiệp <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="export_graduation_year"
                                    name="graduation_year" min="2000" max="2100" value="{{ date('Y') }}"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_decision_number" class="form-label">Quyết định tốt nghiệp <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="export_decision_number"
                                    name="decision_number" placeholder="Số quyết định" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_issue_date" class="form-label">Ngày ban hành <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="export_issue_date" name="issue_date"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_quantity" class="form-label">Số lượng <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="export_quantity" name="quantity"
                                    min="1" placeholder="Nhập số lượng" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-info" id="getSuggestedRanges">
                            <i class="fas fa-magic"></i> Gợi ý dải Serial
                        </button>
                        <button type="button" class="btn btn-secondary" id="addCustomRange">
                            <i class="fas fa-plus"></i> Thêm dải tùy chỉnh
                        </button>
                    </div>

                    {{-- Hiển thị dải serial --}}
                    <div id="serialRanges" style="display: none;">
                        <h6>Dải serial xuất phôi:</h6>
                        <div id="rangesList"></div>
                        <input type="hidden" id="rangesData" name="ranges">
                    </div>

                    <div class="mb-3">
                        <label for="export_notes" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="export_notes" name="notes" rows="3"
                            placeholder="Nhập ghi chú (tùy chọn)"></textarea>
                    </div>

                    {{-- Hiển thị thông báo --}}
                    <div id="exportMessage" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="exportSubmitBtn" disabled>
                        <i class="fas fa-download"></i> Xuất phôi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal thêm dải tùy chỉnh --}}
<div class="modal fade" id="customRangeModal" tabindex="-1" aria-labelledby="customRangeModalLabel"
    aria-hidden="true" style="z-index: 10000;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customRangeModalLabel">Thêm dải Serial tùy chỉnh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="customRangeForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="custom_from_serial" class="form-label">Từ Serial <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="custom_from_serial"
                                    name="from_serial" placeholder="VD: A001" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="custom_to_serial" class="form-label">Đến Serial <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="custom_to_serial" name="to_serial"
                                    placeholder="VD: A100" required>
                            </div>
                        </div>
                    </div>
                    <div id="customRangeMessage" style="display: none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="validateCustomRange">Kiểm tra</button>
                <button type="button" class="btn btn-success" id="addCustomRangeBtn"
                    style="display: none;">Thêm</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modal styling fixes */
    .modal {
        z-index: 999999 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
    }

    .modal-backdrop {
        z-index: 999998 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
    }

    .modal-dialog {
        z-index: 1000000 !important;
        position: relative;
        pointer-events: auto !important;
    }

    .modal-content {
        position: relative;
        z-index: 1000001 !important;
        pointer-events: auto !important;
    }

    .range-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .range-info {
        font-weight: 500;
    }

    .range-count {
        color: #6c757d;
        font-size: 0.9em;
    }

    .range-remove {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
    }

    .range-remove:hover {
        background-color: #dc3545;
        color: white;
    }

    .alert {
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    /* Ensure all modal elements are clickable */
    .modal * {
        pointer-events: auto !important;
    }

    .modal-backdrop.show {
        opacity: 0.5;
    }

    /* Fix for potential overlay issues */
    body.modal-open {
        overflow: hidden;
    }

    .btn,
    .form-control,
    .form-select {
        position: relative;
        z-index: 1;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const exportModal = new bootstrap.Modal(document.getElementById('exportModal'), {
            backdrop: true,
            keyboard: true,
            focus: true
        });
        const customRangeModal = new bootstrap.Modal(document.getElementById('customRangeModal'), {
            backdrop: true,
            keyboard: true,
            focus: true
        });

        let currentRanges = [];

        // Debug modal events
        document.getElementById('exportModal').addEventListener('shown.bs.modal', function() {
            console.log('Export modal is now shown');
        });

        document.getElementById('exportModal').addEventListener('hidden.bs.modal', function() {
            console.log('Export modal is now hidden');
        });

        // Mở modal xuất phôi
        document.querySelector('.action-btn-warning').addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Opening export modal...');
            resetExportForm();
            exportModal.show();
        });

        // Lấy gợi ý dải serial
        document.getElementById('getSuggestedRanges').addEventListener('click', function() {
            const typeId = document.getElementById('export_type_id').value;
            const quantity = document.getElementById('export_quantity').value;

            if (!typeId || !quantity) {
                showMessage('exportMessage', 'Vui lòng chọn loại văn bằng và nhập số lượng', 'danger');
                return;
            }

            fetch('/diploma-blank-exports/suggested-ranges', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    body: JSON.stringify({
                        type_id: typeId,
                        quantity: parseInt(quantity)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // data.ranges: array of {from_serial, to_serial, count}
                        currentRanges = data.ranges || [];
                        // attach damaged serials if any for display
                        currentRanges.__damaged = data.damaged_serials || [];
                        currentRanges.__issued = data.issued_serials || [];
                        currentRanges.__recalled = data.recalled_serials || [];
                        displayRanges();
                        let msg =
                            `Đã gợi ý ${currentRanges.length} dải cho ${data.total_quantity} phôi`;
                        const unavailableCount = (data.damaged_serials || []).length +
                            (data.issued_serials || []).length +
                            (data.recalled_serials || []).length;
                        if (unavailableCount > 0) {
                            msg += `. Phôi không khả dụng: ${unavailableCount}`;
                        }
                        showMessage('exportMessage', msg, 'success');
                    } else {
                        showMessage('exportMessage', data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('exportMessage', 'Có lỗi xảy ra khi lấy gợi ý dải serial',
                    'danger');
                });
        });

        // Mở modal thêm dải tùy chỉnh
        document.getElementById('addCustomRange').addEventListener('click', function() {
            const typeId = document.getElementById('export_type_id').value;
            if (!typeId) {
                showMessage('exportMessage', 'Vui lòng chọn loại văn bằng trước', 'danger');
                return;
            }

            document.getElementById('customRangeForm').reset();
            document.getElementById('customRangeMessage').style.display = 'none';
            document.getElementById('addCustomRangeBtn').style.display = 'none';
            customRangeModal.show();
        });

        // Kiểm tra dải tùy chỉnh
        document.getElementById('validateCustomRange').addEventListener('click', function() {
            const typeId = document.getElementById('export_type_id').value;
            const fromSerial = document.getElementById('custom_from_serial').value;
            const toSerial = document.getElementById('custom_to_serial').value;

            if (!fromSerial || !toSerial) {
                showMessage('customRangeMessage', 'Vui lòng nhập đầy đủ từ serial và đến serial',
                    'danger');
                return;
            }

            fetch('/diploma-blank-exports/validate-range', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    body: JSON.stringify({
                        type_id: typeId,
                        from_serial: fromSerial,
                        to_serial: toSerial
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('customRangeMessage', `Dải serial hợp lệ (${data.count} phôi)`,
                            'success');
                        document.getElementById('addCustomRangeBtn').style.display = 'inline-block';
                    } else {
                        showMessage('customRangeMessage', data.message, 'danger');
                        document.getElementById('addCustomRangeBtn').style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('customRangeMessage', 'Có lỗi xảy ra khi kiểm tra dải serial',
                        'danger');
                });
        });

        // Thêm dải tùy chỉnh
        document.getElementById('addCustomRangeBtn').addEventListener('click', function() {
            const fromSerial = document.getElementById('custom_from_serial').value;
            const toSerial = document.getElementById('custom_to_serial').value;

            // Tính số lượng
            const count = calculateRangeCount(fromSerial, toSerial);

            currentRanges.push({
                from_serial: fromSerial,
                to_serial: toSerial,
                count: count
            });

            displayRanges();
            customRangeModal.hide();
            showMessage('exportMessage', 'Đã thêm dải serial tùy chỉnh', 'success');
        });

        // Submit form xuất phôi
        document.getElementById('exportForm').addEventListener('submit', function(e) {
            if (currentRanges.length === 0) {
                e.preventDefault();
                showMessage('exportMessage', 'Vui lòng chọn ít nhất một dải serial', 'danger');
                return false;
            }

            // Set ranges data to hidden input before form submission
            document.getElementById('rangesData').value = JSON.stringify(currentRanges);

            // Show loading state
            const submitBtn = document.getElementById('exportSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xuất phôi...';

            // Let the form submit normally (no preventDefault)
            // Will redirect to success page or back with error message
            return true;
        });

        function displayRanges() {
            const rangesList = document.getElementById('rangesList');
            const serialRanges = document.getElementById('serialRanges');
            const exportSubmitBtn = document.getElementById('exportSubmitBtn');

            if (currentRanges.length === 0) {
                serialRanges.style.display = 'none';
                exportSubmitBtn.disabled = true;
                return;
            }

            serialRanges.style.display = 'block';
            rangesList.innerHTML = '';

            // Render each range. If from == to, show single serial; otherwise show from - to
            currentRanges.forEach((range, index) => {
                const rangeItem = document.createElement('div');
                rangeItem.className = 'range-item';

                let label = '';
                if (!range || !range.from_serial) return;

                if (range.from_serial === range.to_serial) {
                    label = `${range.from_serial}`;
                } else {
                    label = `${range.from_serial} - ${range.to_serial}`;
                }

                rangeItem.innerHTML = `
                <div>
                    <div class="range-info">${label}</div>
                    <div class="range-count">${range.count} phôi</div>
                </div>
                <button type="button" class="range-remove" onclick="removeRange(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;

                rangesList.appendChild(rangeItem);
            });

            // If there are unavailable serials, show them below the ranges
            const damaged = currentRanges.__damaged || [];
            const issued = currentRanges.__issued || [];
            const recalled = currentRanges.__recalled || [];

            if (damaged.length > 0) {
                const damagedSection = document.createElement('div');
                damagedSection.className = 'mt-2';
                damagedSection.innerHTML =
                    `<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Phôi hỏng (bỏ qua): <strong>${damaged.join(', ')}</strong></div>`;
                rangesList.appendChild(damagedSection);
            }

            if (issued.length > 0) {
                const issuedSection = document.createElement('div');
                issuedSection.className = 'mt-2';
                issuedSection.innerHTML =
                    `<div class="alert alert-info"><i class="fas fa-check-circle"></i> Phôi đã cấp (bỏ qua): <strong>${issued.join(', ')}</strong></div>`;
                rangesList.appendChild(issuedSection);
            }

            if (recalled.length > 0) {
                const recalledSection = document.createElement('div');
                recalledSection.className = 'mt-2';
                recalledSection.innerHTML =
                    `<div class="alert alert-secondary"><i class="fas fa-undo"></i> Phôi đã thu hồi (bỏ qua): <strong>${recalled.join(', ')}</strong></div>`;
                rangesList.appendChild(recalledSection);
            }

            const totalCount = currentRanges.reduce((sum, range) => sum + (range.count || 0), 0);
            document.getElementById('rangesData').value = JSON.stringify(currentRanges);
            exportSubmitBtn.disabled = false;
        }

        function resetExportForm() {
            document.getElementById('exportForm').reset();
            currentRanges = [];
            document.getElementById('serialRanges').style.display = 'none';
            document.getElementById('exportMessage').style.display = 'none';
            document.getElementById('exportSubmitBtn').disabled = true;
            document.getElementById('export_graduation_year').value = new Date().getFullYear();
            document.getElementById('export_issue_date').value = new Date().toISOString().split('T')[0];
        }

        function showMessage(elementId, message, type) {
            const element = document.getElementById(elementId);
            element.className = `alert alert-${type}`;
            element.textContent = message;
            element.style.display = 'block';
        }

        function calculateRangeCount(fromSerial, toSerial) {
            const fromMatch = fromSerial.match(/^([A-Za-z]*)(\d+)$/);
            const toMatch = toSerial.match(/^([A-Za-z]*)(\d+)$/);

            if (!fromMatch || !toMatch || fromMatch[1] !== toMatch[1]) {
                return 0;
            }

            return parseInt(toMatch[2]) - parseInt(fromMatch[2]) + 1;
        }

        // Global function để xóa range
        window.removeRange = function(index) {
            currentRanges.splice(index, 1);
            displayRanges();
        };
    });
</script>
