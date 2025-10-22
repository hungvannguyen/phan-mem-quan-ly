{{-- Styles for modal positioning and z-index --}}
<style>
    /* Ensure export modal has highest z-index and proper centering */
    #exportModal {
        z-index: 99999 !important;
    }

    #exportModal.modal.show {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #exportModal .modal-dialog {
        margin: 0 auto !important;
        position: relative;
        z-index: 100000 !important;
    }

    /* Ensure backdrop doesn't interfere */
    .modal-backdrop {
        z-index: 99998 !important;
    }

    /* For nested modals */
    #customRangeModal {
        z-index: 100001 !important;
    }

    #customRangeModal.modal.show {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #customRangeModal+.modal-backdrop {
        z-index: 100000 !important;
    }
</style>

{{-- Modal Xuất phôi --}}
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true"
    style="z-index: 99999 !important;">
    <div class="modal-dialog export-modal-dialog modal-lg" style="margin: 0 auto !important;">
        <div class="modal-content export-modal-content">
            <div class="modal-header export-modal-header">
                <h5 class="modal-title export-modal-title" id="exportModalLabel">Xuất phôi văn bằng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="exportForm" action="{{ route('diploma-blank-exports.store') }}" method="POST">
                @csrf
                <div class="modal-body export-modal-body">
                    <div class="form-row">
                        <div class="form-col-half">
                            <div class="field-container">
                                <label for="export_type_id" class="field-label required">Loại văn bằng</label>
                                <select class="field-select" id="export_type_id" name="type_id" required>
                                    <option value="">-- Chọn loại văn bằng --</option>
                                    @foreach ($diplomaBlankTypes as $type)
                                        <option value="{{ $type->type_id }}">{{ $type->type_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-col-half">
                            <div class="field-container">
                                <label for="export_course" class="field-label">Khóa</label>
                                <input type="text" class="field-input" id="export_course" name="course"
                                    placeholder="Nhập khóa học">
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col-half">
                            <div class="field-container">
                                <label for="export_graduation_year" class="field-label required">Năm tốt nghiệp</label>
                                <input type="number" class="field-input" id="export_graduation_year"
                                    name="graduation_year" min="2000" max="2100" value="{{ date('Y') }}"
                                    required>
                            </div>
                        </div>
                        <div class="form-col-half">
                            <div class="field-container">
                                <label for="export_decision_number" class="field-label required">Quyết định tốt
                                    nghiệp</label>
                                <input type="text" class="field-input" id="export_decision_number"
                                    name="decision_number" placeholder="Số quyết định" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col-half">
                            <div class="field-container">
                                <label for="export_issue_date" class="field-label required">Ngày ban hành</label>
                                <input type="date" class="field-input" id="export_issue_date" name="issue_date"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="form-col-half">
                            <div class="field-container">
                                <label for="export_quantity" class="field-label required">Số lượng</label>
                                <input type="number" class="field-input" id="export_quantity" name="quantity"
                                    min="1" placeholder="Nhập số lượng" required>
                            </div>
                        </div>
                    </div>

                    <div class="field-container">
                        <button type="button" class="btn-base btn-info" id="getSuggestedRanges">
                            <i class="fas fa-magic"></i> Gợi ý dải Serial
                        </button>
                        <button type="button" class="btn-base btn-secondary" id="addCustomRange">
                            <i class="fas fa-plus"></i> Thêm dải tùy chỉnh
                        </button>
                    </div>

                    {{-- Hiển thị dải serial --}}
                    <div id="serialRanges" class="range-container hidden-element">
                        <h6 class="range-header">Dải serial xuất phôi:</h6>
                        <div id="rangesList" class="range-list"></div>
                        <input type="hidden" id="rangesData" name="ranges">
                    </div>

                    <div class="field-container">
                        <label for="export_notes" class="field-label">Ghi chú</label>
                        <textarea class="field-textarea" id="export_notes" name="notes" rows="3"
                            placeholder="Nhập ghi chú (tùy chọn)"></textarea>
                    </div>

                    {{-- Hiển thị thông báo --}}
                    <div id="exportMessage" class="message-container"></div>
                </div>
                <div class="modal-footer export-modal-footer">
                    <button type="button" class="btn-base btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn-base btn-primary" id="exportSubmitBtn" disabled>
                        <i class="fas fa-download"></i> Xuất phôi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal thêm dải tùy chỉnh --}}
<div class="modal fade" id="customRangeModal" tabindex="-1" aria-labelledby="customRangeModalLabel"
    aria-hidden="true" style="z-index: 100001 !important;">
    <div class="modal-dialog" style="margin: 0 auto !important;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title export-modal-title" id="customRangeModalLabel">Thêm dải Serial tùy chỉnh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="customRangeForm">
                    <div class="form-row">
                        <div class="form-col-half">
                            <div class="field-container">
                                <label for="custom_from_serial" class="field-label required">Từ Serial</label>
                                <input type="text" class="field-input" id="custom_from_serial" name="from_serial"
                                    placeholder="VD: A001" required>
                            </div>
                        </div>
                        <div class="form-col-half">
                            <div class="field-container">
                                <label for="custom_to_serial" class="field-label required">Đến Serial</label>
                                <input type="text" class="field-input" id="custom_to_serial" name="to_serial"
                                    placeholder="VD: A100" required>
                            </div>
                        </div>
                    </div>
                    <div id="customRangeMessage" class="message-container"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-base btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn-base btn-primary" id="validateCustomRange">Kiểm tra</button>
                <button type="button" class="btn-base btn-success hidden-element"
                    id="addCustomRangeBtn">Thêm</button>
            </div>
        </div>
    </div>
</div>

{{-- Styles are now handled by SCSS in resources/scss/components/_export-modal.scss --}}

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Helper functions for managing element visibility using CSS classes
        function hideElement(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.classList.add('hidden-element');
                element.classList.remove('show');
            }
        }

        function showElement(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.classList.remove('hidden-element');
                if (element.classList.contains('message-container')) {
                    element.classList.add('show');
                }
            }
        }

        function hideMessageContainer(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.classList.remove('show');
                element.classList.add('hidden-element');
            }
        }

        function showMessageContainer(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.classList.remove('hidden-element');
                element.classList.add('show');
            }
        }

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

            // Show loading state
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tìm phôi...';

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
                        currentRanges = data.ranges || [];
                        displayRanges();

                        // ✅ Display optimized summary
                        let msg =
                            `✅ Đã gợi ý ${currentRanges.length} dải cho ${data.total_quantity} phôi`;

                        if (data.status_summary) {
                            const summary = data.status_summary;
                            msg +=
                                `\n📊 Tổng quan: ${summary.total} phôi (${summary.available} khả dụng`;

                            const unavailableItems = [];
                            if (summary.issued_count > 0) unavailableItems.push(
                                `${summary.issued_count} đã cấp`);
                            if (summary.damaged_count > 0) unavailableItems.push(
                                `${summary.damaged_count} lỗi`);
                            if (summary.recalled_count > 0) unavailableItems.push(
                                `${summary.recalled_count} thu hồi`);

                            if (unavailableItems.length > 0) {
                                msg += `, ${unavailableItems.join(', ')}`;
                            }
                            msg += ')';
                        }

                        showMessage('exportMessage', msg, 'success');
                    } else {
                        let errorMsg = data.message;

                        // ✅ Show status summary even on error
                        if (data.status_summary) {
                            const summary = data.status_summary;
                            errorMsg +=
                                `\n📊 Tình trạng phôi: ${summary.total} tổng (${summary.available} khả dụng`;

                            if (summary.issued_count > 0) errorMsg +=
                                `, ${summary.issued_count} đã cấp`;
                            if (summary.damaged_count > 0) errorMsg +=
                                `, ${summary.damaged_count} lỗi`;
                            if (summary.recalled_count > 0) errorMsg +=
                                `, ${summary.recalled_count} thu hồi`;
                            errorMsg += ')';
                        }

                        showMessage('exportMessage', errorMsg, 'warning');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('exportMessage', 'Có lỗi xảy ra khi lấy gợi ý dải serial',
                        'danger');
                })
                .finally(() => {
                    // Reset button state
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-magic"></i> Gợi ý dải Serial';
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
            hideMessageContainer('customRangeMessage');
            hideElement('addCustomRangeBtn');
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
                        showElement('addCustomRangeBtn');
                    } else {
                        showMessage('customRangeMessage', data.message, 'danger');
                        hideElement('addCustomRangeBtn');
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
                hideElement('serialRanges');
                exportSubmitBtn.disabled = true;
                return;
            }

            showElement('serialRanges');
            rangesList.innerHTML = '';

            // ✅ OPTIMIZED: Only show the actual ranges, not unavailable serials
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

            // ✅ Show summary info instead of long lists
            const totalCount = currentRanges.reduce((sum, range) => sum + (range.count || 0), 0);

            if (totalCount > 0) {
                const summarySection = document.createElement('div');
                summarySection.className = 'mt-3 p-3 bg-light rounded';
                summarySection.innerHTML = `
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="text-primary fw-bold">${totalCount}</div>
                            <small class="text-muted">Phôi sẽ xuất</small>
                        </div>
                        <div class="col-md-4">
                            <div class="text-success fw-bold">${currentRanges.length}</div>
                            <small class="text-muted">Dải serial</small>
                        </div>
                        <div class="col-md-4">
                            <div class="text-info fw-bold">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <small class="text-muted">Sẵn sàng xuất</small>
                        </div>
                    </div>
                `;
                rangesList.appendChild(summarySection);
            }

            document.getElementById('rangesData').value = JSON.stringify(currentRanges);
            exportSubmitBtn.disabled = false;
        }

        function resetExportForm() {
            document.getElementById('exportForm').reset();
            currentRanges = [];
            hideElement('serialRanges');
            hideMessageContainer('exportMessage');
            document.getElementById('exportSubmitBtn').disabled = true;
            document.getElementById('export_graduation_year').value = new Date().getFullYear();
            document.getElementById('export_issue_date').value = new Date().toISOString().split('T')[0];
        }

        function showMessage(elementId, message, type) {
            const element = document.getElementById(elementId);
            element.className = `alert alert-${type}`;
            // ✅ Support multi-line messages
            element.innerHTML = message.replace(/\n/g, '<br>');
            showMessageContainer(elementId);
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
