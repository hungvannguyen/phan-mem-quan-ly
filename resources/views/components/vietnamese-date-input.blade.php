@props([
    'id' => 'date_input',
    'name' => 'date',
    'value' => '',
    'required' => false,
    'placeholder' => 'dd/mm/yyyy',
    'label' => 'Ngày',
    'description' => 'Định dạng: ngày/tháng/năm',
    'inputClass' => 'field-input',
])

<div class="form-field">
    <label for="{{ $id }}_display" class="field-label">
        {{ $label }}
        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    <div class="date-input-wrapper">
        <input type="text" id="{{ $id }}_display" class="{{ $inputClass }} vietnamese-date-input"
            placeholder="{{ $placeholder }}" value="{{ $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '' }}"
            autocomplete="off" data-target="{{ $id }}">
        <input type="date" id="{{ $id }}" name="{{ $name }}" class="hidden"
            value="{{ $value }}" {{ $required ? 'required' : '' }}>
        <button type="button" class="date-picker-btn" data-target="{{ $id }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </button>
    </div>
    @if ($description)
        <div class="field-description">
            <small class="text-gray-600">{{ $description }}</small>
        </div>
    @endif
    {{ $slot }}

    <!-- Vietnamese Date Picker Modal -->
    <div id="{{ $id }}_modal" class="date-picker-modal hidden">
        <div class="date-picker-overlay"></div>
        <div class="date-picker-content">
            <div class="date-picker-tabs">
                <button type="button" class="tab-btn active" id="{{ $id }}_tab_calendar">Lịch</button>
                <button type="button" class="tab-btn" id="{{ $id }}_tab_manual">Nhập tay</button>
            </div>
            <div class="date-picker-header">
                <button type="button" class="nav-btn" id="{{ $id }}_prev_month">&lt;</button>
                <select class="month-select" id="{{ $id }}_month_select">
                    <option value="0">Tháng 1</option>
                    <option value="1">Tháng 2</option>
                    <option value="2">Tháng 3</option>
                    <option value="3">Tháng 4</option>
                    <option value="4">Tháng 5</option>
                    <option value="5">Tháng 6</option>
                    <option value="6">Tháng 7</option>
                    <option value="7">Tháng 8</option>
                    <option value="8">Tháng 9</option>
                    <option value="9">Tháng 10</option>
                    <option value="10">Tháng 11</option>
                    <option value="11">Tháng 12</option>
                </select>
                <select class="year-select" id="{{ $id }}_year_select"></select>
                <button type="button" class="nav-btn" id="{{ $id }}_next_month">&gt;</button>
            </div>
            <div class="date-picker-calendar">
                <div class="weekdays">
                    <div>CN</div>
                    <div>T2</div>
                    <div>T3</div>
                    <div>T4</div>
                    <div>T5</div>
                    <div>T6</div>
                    <div>T7</div>
                </div>
                <div class="days" id="{{ $id }}_days"></div>
            </div>
            <div class="date-picker-manual hidden" id="{{ $id }}_manual_input">
                <div class="manual-input-form">
                    <div class="manual-row">
                        <div class="manual-field">
                            <label>Ngày</label>
                            <input type="number" id="{{ $id }}_manual_day" class="manual-input"
                                min="1" max="31" placeholder="DD">
                        </div>
                        <div class="manual-field">
                            <label>Tháng</label>
                            <input type="number" id="{{ $id }}_manual_month" class="manual-input"
                                min="1" max="12" placeholder="MM">
                        </div>
                        <div class="manual-field">
                            <label>Năm</label>
                            <input type="number" id="{{ $id }}_manual_year" class="manual-input"
                                min="1900" max="2100" placeholder="YYYY">
                        </div>
                    </div>
                    <div class="manual-actions">
                        <button type="button" class="btn-apply-manual" id="{{ $id }}_apply_manual">Áp
                            dụng</button>
                    </div>
                </div>
            </div>
            <div class="date-picker-notification" id="{{ $id }}_notification"></div>
            <div class="date-picker-footer">
                <button type="button" class="btn-today" id="{{ $id }}_today">Hôm nay</button>
                <button type="button" class="btn-apply" id="{{ $id }}_apply" style="display: none;">Chọn
                    ngày này</button>
                <button type="button" class="btn-cancel" id="{{ $id }}_cancel">Hủy</button>
            </div>
        </div>
    </div>
</div>

<style>
    .date-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .date-picker-btn {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .date-picker-btn:hover {
        color: #3b82f6;
        background-color: #f3f4f6;
    }

    .vietnamese-date-input {
        padding-right: 40px;
    }

    /* Date Picker Modal Styles */
    .date-picker-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
    }

    .date-picker-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }

    .date-picker-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        padding: 16px;
        width: 320px;
    }

    .date-picker-tabs {
        display: flex;
        margin-bottom: 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .tab-btn {
        flex: 1;
        padding: 8px 16px;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        font-size: 14px;
        color: #6b7280;
        transition: all 0.2s;
    }

    .tab-btn.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
        font-weight: 500;
    }

    .tab-btn:hover:not(.active) {
        color: #374151;
        background: #f9fafb;
    }

    .date-picker-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .nav-btn {
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        padding: 4px 8px;
        cursor: pointer;
        font-weight: bold;
    }

    .nav-btn:hover {
        background: #e5e7eb;
    }

    .month-select,
    .year-select {
        border: 1px solid #d1d5db;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 14px;
    }

    .weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
        margin-bottom: 8px;
    }

    .weekdays>div {
        text-align: center;
        font-weight: bold;
        color: #6b7280;
        padding: 8px 4px;
        font-size: 12px;
    }

    .days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
    }

    .day {
        text-align: center;
        padding: 8px 4px;
        cursor: pointer;
        border-radius: 4px;
        font-size: 14px;
    }

    .day:hover {
        background: #f3f4f6;
    }

    .day.selected {
        background: #3b82f6;
        color: white;
    }

    .day.other-month {
        color: #d1d5db;
    }

    .day.today {
        background: #fef3c7;
        font-weight: bold;
    }

    .date-picker-notification {
        margin-top: 12px;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 13px;
        text-align: center;
        min-height: 20px;
        transition: all 0.3s ease;
    }

    .date-picker-notification.info {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }

    .date-picker-notification.warning {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }

    .date-picker-notification.empty {
        background: transparent;
        border: none;
    }

    .date-picker-footer {
        margin-top: 16px;
        display: flex;
        justify-content: space-between;
    }

    .btn-today,
    .btn-today,
    .btn-apply,
    .btn-cancel {
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .btn-today {
        background: #3b82f6;
        color: white;
        border: none;
    }

    .btn-today:hover {
        background: #2563eb;
    }

    .btn-apply {
        background: #10b981;
        color: white;
        border: none;
    }

    .btn-apply:hover {
        background: #059669;
    }

    .btn-cancel {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
    }

    .date-picker-manual {
        padding: 16px 0;
    }

    .manual-input-form {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .manual-row {
        display: flex;
        gap: 12px;
    }

    .manual-field {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .manual-field label {
        font-size: 12px;
        font-weight: 500;
        color: #374151;
    }

    .manual-input {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 14px;
        text-align: center;
        transition: border-color 0.2s;
    }

    .manual-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 1px #3b82f6;
    }

    .manual-input.error {
        border-color: #ef4444;
        box-shadow: 0 0 0 1px #ef4444;
    }

    .manual-actions {
        display: flex;
        justify-content: center;
    }

    .btn-apply-manual {
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        border: none;
        transition: all 0.2s;
        background: #10b981;
        color: white;
    }

    .btn-apply-manual:hover {
        background: #059669;
    }

    .btn-apply-manual:disabled {
        background: #d1d5db;
        cursor: not-allowed;
    }

    .hidden {
        display: none !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to format date to Vietnamese format
        function formatToVietnamese(dateValue) {
            if (!dateValue) return '';
            const date = new Date(dateValue);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        // Function to get maximum days in a month
        function getMaxDaysInMonth(month, year) {
            if (!month || month < 1 || month > 12) return 31;
            if (!year) return 31; // Default to 31 if year not provided yet

            const daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

            // Check for leap year (February)
            if (month === 2 && ((year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0))) {
                return 29;
            }

            return daysInMonth[month - 1];
        }

        // Function to parse Vietnamese date format to ISO format
        function parseVietnameseDate(vietnameseDate) {
            if (!vietnameseDate) return '';
            const parts = vietnameseDate.split('/');
            if (parts.length === 3) {
                const day = parseInt(parts[0]);
                const month = parseInt(parts[1]);
                const year = parseInt(parts[2]);

                // Validate day, month, year ranges
                if (day < 1 || day > 31 || month < 1 || month > 12 || year < 1900 || year > 2100) {
                    return null; // Invalid date
                }

                // Check if date is valid (handles leap years, different month lengths)
                const testDate = new Date(year, month - 1, day);
                if (testDate.getDate() !== day || testDate.getMonth() !== (month - 1) || testDate
                    .getFullYear() !== year) {
                    return null; // Invalid date (e.g., 31/02/2024)
                }

                const dayStr = String(day).padStart(2, '0');
                const monthStr = String(month).padStart(2, '0');
                return `${year}-${monthStr}-${dayStr}`;
            }
            return '';
        }

        // Handle all Vietnamese date inputs
        document.querySelectorAll('.vietnamese-date-input').forEach(function(displayInput) {
            const targetId = displayInput.getAttribute('data-target');
            const hiddenInput = document.getElementById(targetId);
            const pickerBtn = document.querySelector(`[data-target="${targetId}"]`);

            if (!hiddenInput) return;

            // Handle text input for Vietnamese format
            displayInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
                let formattedValue = '';

                if (value.length > 0) {
                    // Handle day (first 2 digits)
                    let day = value.substring(0, 2);
                    let month = value.substring(2, 4);
                    let year = value.substring(4, 8);

                    // Auto-format and auto-correct day
                    if (day.length === 1) {
                        let dayNum = parseInt(day);
                        if (dayNum > 3) {
                            day = '3'; // If single digit > 3, set to 3
                        }
                    } else if (day.length === 2) {
                        let dayNum = parseInt(day);
                        let firstDigit = parseInt(day[0]);
                        let secondDigit = parseInt(day[1]);

                        // If first digit > 3, auto-correct to 3x
                        if (firstDigit > 3) {
                            day = '3' + day[1];
                            dayNum = parseInt(day);
                        }

                        if (dayNum < 1 && day !== '00') {
                            day = '01'; // Minimum 01
                        } else if (dayNum > 31) {
                            day = '31'; // Cap at 31 initially
                        }

                        // If we have month info, validate day against month
                        if (month.length >= 1) {
                            let monthNum = month.length === 1 ? parseInt('0' + month) :
                                parseInt(month);
                            if (monthNum >= 1 && monthNum <= 12) {
                                let yearNum = year.length === 4 ? parseInt(year) : new Date()
                                    .getFullYear();
                                let maxDays = getMaxDaysInMonth(monthNum, yearNum);
                                if (dayNum > maxDays) {
                                    day = String(maxDays).padStart(2, '0');
                                }
                            }
                        }
                    }
                    formattedValue = day;

                    // Handle month (digits 3-4)
                    if (value.length > 2) {
                        formattedValue += '/';

                        // Auto-format and auto-correct month
                        if (month.length === 1) {
                            let monthNum = parseInt(month);
                            if (monthNum > 1) {
                                month = '0' + month; // Auto add 0 prefix for months > 1
                            }
                        } else if (month.length === 2) {
                            let monthNum = parseInt(month);
                            if (monthNum > 12) {
                                // If month > 12, take first digit and pad with 0
                                let firstDigit = parseInt(month[0]);
                                if (firstDigit >= 1 && firstDigit <= 9) {
                                    month = '0' + firstDigit;
                                } else {
                                    month = '12'; // fallback to 12
                                }
                            } else if (monthNum < 1 && month !== '00') {
                                month = '01'; // Minimum 01
                            }
                        }
                        formattedValue += month;

                        // Handle year (digits 5-8)
                        if (value.length > 4) {
                            formattedValue += '/';
                            formattedValue += year;
                        }
                    }
                }

                e.target.value = formattedValue;

                // Parse and set the hidden date input
                if (formattedValue.length === 10) {
                    const isoDate = parseVietnameseDate(formattedValue);
                    if (isoDate === null) {
                        hiddenInput.value = '';
                        e.target.setCustomValidity(
                            'Ngày không hợp lệ. Vui lòng nhập đúng định dạng dd/mm/yyyy');
                    } else if (isoDate) {
                        hiddenInput.value = isoDate;
                        // Additional validation for birth date range
                        const testDate = new Date(isoDate);
                        const currentYear = new Date().getFullYear();
                        if (testDate.getFullYear() < 1900 || testDate.getFullYear() >
                            currentYear) {
                            e.target.setCustomValidity(
                                `Năm sinh không hợp lệ (1900-${currentYear})`);
                        } else {
                            e.target.setCustomValidity('');
                        }
                    } else {
                        hiddenInput.value = '';
                        e.target.setCustomValidity('Định dạng ngày không đúng');
                    }
                } else {
                    hiddenInput.value = '';
                    if (formattedValue.length > 0) {
                        e.target.setCustomValidity('Vui lòng nhập đủ ngày/tháng/năm');
                    } else {
                        e.target.setCustomValidity('');
                    }
                }
            });

            // Handle keydown to prevent invalid input
            displayInput.addEventListener('keydown', function(e) {
                const currentValue = e.target.value;
                const cursorPos = e.target.selectionStart;

                // Allow backspace, delete, tab, escape, enter
                if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                    // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                    (e.keyCode === 65 && e.ctrlKey === true) ||
                    (e.keyCode === 67 && e.ctrlKey === true) ||
                    (e.keyCode === 86 && e.ctrlKey === true) ||
                    (e.keyCode === 88 && e.ctrlKey === true) ||
                    // Allow home, end, left, right
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    return;
                }

                // Ensure that it's a number
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e
                        .keyCode > 105)) {
                    e.preventDefault();
                    return;
                }

                // Check position-specific restrictions
                const nextValue = currentValue.slice(0, cursorPos) + String.fromCharCode(e
                    .keyCode) + currentValue.slice(cursorPos);
                const numbersOnly = nextValue.replace(/\D/g, '');

                // Check day restriction (positions 0-1)
                if (numbersOnly.length >= 1) {
                    const dayStr = numbersOnly.substring(0, 2);
                    if (dayStr.length === 1 && parseInt(dayStr) > 3) {
                        e.preventDefault();
                        return;
                    }
                    if (dayStr.length === 2 && parseInt(dayStr) > 31) {
                        e.preventDefault();
                        return;
                    }
                }

                // Check month restriction (positions 2-3)
                if (numbersOnly.length >= 3) {
                    const monthStr = numbersOnly.substring(2, 4);
                    if (monthStr.length === 1 && parseInt(monthStr) > 1) {
                        e.preventDefault();
                        return;
                    }
                    if (monthStr.length === 2 && parseInt(monthStr) > 12) {
                        e.preventDefault();
                        return;
                    }
                }

                // Allow up to 8 digits total (ddmmyyyy) - increased from previous limit
                if (numbersOnly.length > 8) {
                    e.preventDefault();
                    return;
                }
            });

            // Handle paste event
            displayInput.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = (e.clipboardData || window.clipboardData).getData('text');
                const numbersOnly = pastedData.replace(/\D/g, '');

                if (numbersOnly.length > 8) {
                    return; // Don't allow paste if too many digits
                }

                let formattedValue = '';
                if (numbersOnly.length > 0) {
                    // Format the pasted data
                    let day = numbersOnly.substring(0, 2);
                    let month = numbersOnly.substring(2, 4);
                    let year = numbersOnly.substring(4, 8);

                    // Auto-format and validate day
                    if (day.length === 1) {
                        if (parseInt(day) > 3) day = '3';
                    } else if (day.length === 2) {
                        let dayNum = parseInt(day);
                        let firstDigit = parseInt(day[0]);

                        // Auto-correct first digit if > 3
                        if (firstDigit > 3) {
                            day = '3' + day[1];
                            dayNum = parseInt(day);
                        }

                        if (dayNum > 31) day = '31';
                        if (dayNum < 1) day = '01';

                        // Check against month if available
                        if (month.length >= 1) {
                            // Auto-format month
                            if (month.length === 1 && parseInt(month) > 1) {
                                month = '0' + month;
                            } else if (month.length === 2) {
                                let monthNum = parseInt(month);
                                if (monthNum > 12) {
                                    // If month > 12, take first digit and pad with 0
                                    let firstDigit = parseInt(month[0]);
                                    if (firstDigit >= 1 && firstDigit <= 9) {
                                        month = '0' + firstDigit;
                                    } else {
                                        month = '12'; // fallback to 12
                                    }
                                }
                                if (monthNum < 1) month = '01';
                            }

                            let monthNum = parseInt(month);
                            if (monthNum >= 1 && monthNum <= 12) {
                                let yearNum = year.length === 4 ? parseInt(year) : new Date()
                                    .getFullYear();
                                let maxDays = getMaxDaysInMonth(monthNum, yearNum);
                                if (dayNum > maxDays) {
                                    day = String(maxDays).padStart(2, '0');
                                }
                            }
                        }
                    }

                    formattedValue = day;
                    if (month) formattedValue += '/' + month;
                    if (year) formattedValue += '/' + year;
                }

                e.target.value = formattedValue;

                // Trigger input event to handle validation
                e.target.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            });

            // Handle blur event for validation
            displayInput.addEventListener('blur', function(e) {
                const value = e.target.value.trim();
                if (value && value.length === 10) {
                    const parts = value.split('/');
                    if (parts.length === 3) {
                        const day = parseInt(parts[0]);
                        const month = parseInt(parts[1]);
                        const year = parseInt(parts[2]);

                        let errorMessage = '';

                        if (day < 1 || day > 31) {
                            errorMessage = 'Ngày phải từ 01 đến 31';
                        } else if (month < 1 || month > 12) {
                            errorMessage = 'Tháng phải từ 01 đến 12';
                        } else if (year < 1900 || year > new Date().getFullYear()) {
                            errorMessage = `Năm phải từ 1900 đến ${new Date().getFullYear()}`;
                        } else {
                            // Check if it's a valid date (handles leap years, month lengths)
                            const testDate = new Date(year, month - 1, day);
                            if (testDate.getDate() !== day || testDate.getMonth() !== (month -
                                    1)) {
                                errorMessage =
                                    'Ngày không tồn tại (ví dụ: tháng 2 không có 30 ngày)';
                            }
                        }

                        if (errorMessage) {
                            e.target.setCustomValidity(errorMessage);
                            e.target.reportValidity();
                        } else {
                            e.target.setCustomValidity('');
                        }
                    }
                } else if (value && value.length < 10) {
                    e.target.setCustomValidity('Vui lòng nhập đủ 10 ký tự (dd/mm/yyyy)');
                    e.target.reportValidity();
                }
            });

            // Handle date picker button click - show Vietnamese date picker
            if (pickerBtn) {
                pickerBtn.addEventListener('click', function() {
                    showVietnameseDatePicker(targetId, hiddenInput, displayInput);
                });
            }

            // Handle hidden date input change
            hiddenInput.addEventListener('change', function() {
                if (this.value) {
                    displayInput.value = formatToVietnamese(this.value);
                    displayInput.setCustomValidity('');
                }
            });

            // Initialize display if there's already a value
            if (hiddenInput.value) {
                displayInput.value = formatToVietnamese(hiddenInput.value);
            }
        });

        // Vietnamese Date Picker Functions
        function showVietnameseDatePicker(targetId, hiddenInput, displayInput) {
            const modal = document.getElementById(targetId + '_modal');
            const monthSelect = document.getElementById(targetId + '_month_select');
            const yearSelect = document.getElementById(targetId + '_year_select');
            const daysContainer = document.getElementById(targetId + '_days');
            const notification = document.getElementById(targetId + '_notification');

            let currentDate = new Date();
            if (hiddenInput.value) {
                currentDate = new Date(hiddenInput.value);
            }

            let currentMonth = currentDate.getMonth();
            let currentYear = currentDate.getFullYear();
            let selectedDay = hiddenInput.value ? currentDate.getDate() : null;

            // Populate year select
            yearSelect.innerHTML = '';
            for (let year = 1900; year <= new Date().getFullYear() + 10; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                if (year === currentYear) option.selected = true;
                yearSelect.appendChild(option);
            }

            monthSelect.value = currentMonth;

            // Tab switching logic
            const tabCalendar = document.getElementById(targetId + '_tab_calendar');
            const tabManual = document.getElementById(targetId + '_tab_manual');
            const calendarView = document.querySelector(`#${targetId}_modal .date-picker-header`).parentElement
                .querySelector('.date-picker-calendar').parentElement;
            const manualView = document.getElementById(targetId + '_manual_input');

            function switchToCalendarTab() {
                tabCalendar.classList.add('active');
                tabManual.classList.remove('active');
                document.querySelector(`#${targetId}_modal .date-picker-header`).classList.remove('hidden');
                document.querySelector(`#${targetId}_modal .date-picker-calendar`).classList.remove('hidden');
                manualView.classList.add('hidden');
            }

            function switchToManualTab() {
                tabManual.classList.add('active');
                tabCalendar.classList.remove('active');
                document.querySelector(`#${targetId}_modal .date-picker-header`).classList.add('hidden');
                document.querySelector(`#${targetId}_modal .date-picker-calendar`).classList.add('hidden');
                manualView.classList.remove('hidden');

                // Populate manual input with current values if available
                if (hiddenInput.value) {
                    const date = new Date(hiddenInput.value);
                    document.getElementById(targetId + '_manual_day').value = date.getDate();
                    document.getElementById(targetId + '_manual_month').value = date.getMonth() + 1;
                    document.getElementById(targetId + '_manual_year').value = date.getFullYear();
                }
            }

            tabCalendar.onclick = switchToCalendarTab;
            tabManual.onclick = switchToManualTab;

            // Notification helper function
            function showNotification(message, type = 'info') {
                notification.textContent = message;
                notification.className = `date-picker-notification ${type}`;
                setTimeout(() => {
                    notification.textContent = '';
                    notification.className = 'date-picker-notification empty';
                }, 3000);
            }

            function renderCalendar() {
                daysContainer.innerHTML = '';

                const firstDay = new Date(currentYear, currentMonth, 1);
                const lastDay = new Date(currentYear, currentMonth + 1, 0);
                const startDate = new Date(firstDay);
                startDate.setDate(startDate.getDate() - firstDay.getDay());

                const today = new Date();
                const selectedDate = hiddenInput.value ? new Date(hiddenInput.value) : null;

                // Check if selected day exists in current month
                let dayExistsInMonth = true;
                if (selectedDay) {
                    const maxDaysInMonth = getMaxDaysInMonth(currentMonth + 1, currentYear);
                    if (selectedDay > maxDaysInMonth) {
                        dayExistsInMonth = false;
                    }
                }

                for (let i = 0; i < 42; i++) {
                    const date = new Date(startDate);
                    date.setDate(startDate.getDate() + i);

                    const dayElement = document.createElement('div');
                    dayElement.className = 'day';
                    dayElement.textContent = date.getDate();

                    if (date.getMonth() !== currentMonth) {
                        dayElement.classList.add('other-month');
                    }

                    if (date.toDateString() === today.toDateString()) {
                        dayElement.classList.add('today');
                    }

                    // Highlight selected day if it exists in current month
                    if (selectedDay && date.getMonth() === currentMonth && date.getDate() === selectedDay &&
                        dayExistsInMonth) {
                        dayElement.classList.add('selected');
                    } else if (selectedDate && date.toDateString() === selectedDate.toDateString()) {
                        dayElement.classList.add('selected');
                    }

                    dayElement.addEventListener('click', function() {
                        const isoDate = date.getFullYear() + '-' +
                            String(date.getMonth() + 1).padStart(2, '0') + '-' +
                            String(date.getDate()).padStart(2, '0');
                        hiddenInput.value = isoDate;
                        displayInput.value = formatToVietnamese(isoDate);
                        displayInput.setCustomValidity('');
                        selectedDay = date.getDate();
                        currentMonth = date.getMonth();
                        currentYear = date.getFullYear();
                        modal.classList.add('hidden');
                    });

                    daysContainer.appendChild(dayElement);
                }
            }

            // Event listeners
            document.getElementById(targetId + '_prev_month').onclick = function() {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                monthSelect.value = currentMonth;
                yearSelect.value = currentYear;
                renderCalendar();
            };

            document.getElementById(targetId + '_next_month').onclick = function() {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                monthSelect.value = currentMonth;
                yearSelect.value = currentYear;
                renderCalendar();
            };

            monthSelect.onchange = function() {
                const newMonth = parseInt(this.value);
                const oldMonth = currentMonth;
                currentMonth = newMonth;

                // Check if selected day exists in new month
                if (selectedDay) {
                    const maxDaysInMonth = getMaxDaysInMonth(currentMonth + 1, currentYear);
                    const applyBtn = document.getElementById(targetId + '_apply');

                    if (selectedDay > maxDaysInMonth) {
                        showNotification(
                            `Ngày ${selectedDay} không tồn tại trong tháng này. Vui lòng chọn ngày khác.`,
                            'warning');
                        applyBtn.style.display = 'none';
                    } else if (oldMonth !== newMonth) {
                        showNotification(
                            `Có thể chọn ngày ${selectedDay} cho tháng ${currentMonth + 1}. Nhấn "Chọn ngày này" để áp dụng.`,
                            'info');
                        applyBtn.style.display = 'inline-block';
                        applyBtn.textContent = `Chọn ngày ${selectedDay}`;
                    }
                }

                renderCalendar();
            };

            yearSelect.onchange = function() {
                const newYear = parseInt(this.value);
                const oldYear = currentYear;
                currentYear = newYear;

                // Check if selected day exists in new year (mainly for Feb 29)
                if (selectedDay) {
                    const maxDaysInMonth = getMaxDaysInMonth(currentMonth + 1, currentYear);
                    const applyBtn = document.getElementById(targetId + '_apply');

                    if (selectedDay > maxDaysInMonth) {
                        if (currentMonth === 1) { // February
                            showNotification(
                                `Ngày 29/2 không tồn tại trong năm ${currentYear}. Vui lòng chọn ngày khác.`,
                                'warning');
                        } else {
                            showNotification(
                                `Ngày ${selectedDay} không tồn tại trong năm ${currentYear}. Vui lòng chọn ngày khác.`,
                                'warning');
                        }
                        applyBtn.style.display = 'none';
                    } else if (oldYear !== newYear) {
                        showNotification(
                            `Có thể chọn ngày ${selectedDay} cho năm ${currentYear}. Nhấn "Chọn ngày này" để áp dụng.`,
                            'info');
                        applyBtn.style.display = 'inline-block';
                        applyBtn.textContent = `Chọn ngày ${selectedDay}`;
                    }
                }

                renderCalendar();
            };

            document.getElementById(targetId + '_today').onclick = function() {
                const today = new Date();
                const isoDate = today.getFullYear() + '-' +
                    String(today.getMonth() + 1).padStart(2, '0') + '-' +
                    String(today.getDate()).padStart(2, '0');
                hiddenInput.value = isoDate;
                displayInput.value = formatToVietnamese(isoDate);
                displayInput.setCustomValidity('');
                modal.classList.add('hidden');
            };

            document.getElementById(targetId + '_apply').onclick = function() {
                if (selectedDay) {
                    const isoDate = currentYear + '-' +
                        String(currentMonth + 1).padStart(2, '0') + '-' +
                        String(selectedDay).padStart(2, '0');
                    hiddenInput.value = isoDate;
                    displayInput.value = formatToVietnamese(isoDate);
                    displayInput.setCustomValidity('');
                    modal.classList.add('hidden');
                }
            };

            document.getElementById(targetId + '_cancel').onclick = function() {
                modal.classList.add('hidden');
            };

            // Close on overlay click
            modal.querySelector('.date-picker-overlay').onclick = function() {
                modal.classList.add('hidden');
            };

            // Manual input validation and logic
            const manualDay = document.getElementById(targetId + '_manual_day');
            const manualMonth = document.getElementById(targetId + '_manual_month');
            const manualYear = document.getElementById(targetId + '_manual_year');
            const applyManualBtn = document.getElementById(targetId + '_apply_manual');

            let correctionTimeout;

            function autoCorrectAndValidate() {
                let day = parseInt(manualDay.value) || 1;
                let month = parseInt(manualMonth.value) || 1;
                let year = parseInt(manualYear.value) || new Date().getFullYear();

                // Auto-correct values to valid ranges
                if (day < 1) day = 1;
                if (day > 31) day = 1; // Reset to 1 if invalid

                if (month < 1) month = 1;
                if (month > 12) month = 1; // Reset to 1 if invalid

                if (year < 1900) year = 1900;
                if (year > new Date().getFullYear() + 10) year = new Date().getFullYear();

                // Check if day exists in the corrected month/year
                const maxDaysInMonth = getMaxDaysInMonth(month, year);
                if (day > maxDaysInMonth) {
                    day = 1; // Reset to 1 if day doesn't exist in month
                }

                // Update the input fields with corrected values
                manualDay.value = day;
                manualMonth.value = month;
                manualYear.value = year;

                // Reset error states
                manualDay.classList.remove('error');
                manualMonth.classList.remove('error');
                manualYear.classList.remove('error');

                // Show notification about auto-correction
                if (parseInt(manualDay.dataset.originalValue || manualDay.value) !== day ||
                    parseInt(manualMonth.dataset.originalValue || manualMonth.value) !== month ||
                    parseInt(manualYear.dataset.originalValue || manualYear.value) !== year) {
                    showNotification(`Đã tự động sửa thành ${day}/${month}/${year}`, 'info');
                }

                // Always enable apply button since values are now valid
                applyManualBtn.disabled = false;

                return true;
            }

            function applyManualInput() {
                autoCorrectAndValidate(); // Ensure values are corrected before applying

                const day = parseInt(manualDay.value);
                const month = parseInt(manualMonth.value);
                const year = parseInt(manualYear.value);

                const isoDate = year + '-' +
                    String(month).padStart(2, '0') + '-' +
                    String(day).padStart(2, '0');

                hiddenInput.value = isoDate;
                displayInput.value = formatToVietnamese(isoDate);
                displayInput.setCustomValidity('');
                modal.classList.add('hidden');
            }

            // Auto-correct on input change
            [manualDay, manualMonth, manualYear].forEach(input => {
                // Store original value for comparison
                input.addEventListener('focus', function() {
                    this.dataset.originalValue = this.value;
                });

                input.addEventListener('blur', function() {
                    if (this.value) {
                        autoCorrectAndValidate();
                    }
                });

                input.addEventListener('input', function() {
                    // Clear error state while typing
                    this.classList.remove('error');

                    // Clear existing timeout
                    clearTimeout(correctionTimeout);

                    // Auto-correct with delay to avoid interrupting user input
                    correctionTimeout = setTimeout(() => {
                        if (this.value) {
                            const currentValue = parseInt(this.value);
                            let correctedValue = currentValue;
                            let shouldCorrect = false;

                            if (this === manualDay && (currentValue > 31 ||
                                    currentValue < 1)) {
                                correctedValue = 1;
                                shouldCorrect = true;
                            } else if (this === manualMonth && (currentValue > 12 ||
                                    currentValue < 1)) {
                                correctedValue = 1;
                                shouldCorrect = true;
                            } else if (this === manualYear && (currentValue < 1900 ||
                                    currentValue > new Date().getFullYear() + 10)) {
                                correctedValue = currentValue < 1900 ? 1900 : new Date()
                                    .getFullYear();
                                shouldCorrect = true;
                            }

                            if (shouldCorrect) {
                                this.value = correctedValue;
                                showNotification(
                                    `Đã tự động sửa ${this.getAttribute('id').includes('day') ? 'ngày' : this.getAttribute('id').includes('month') ? 'tháng' : 'năm'} thành ${correctedValue}`,
                                    'info');
                            }
                        }

                        // Enable apply button if all fields have values
                        if (manualDay.value && manualMonth.value && manualYear.value) {
                            applyManualBtn.disabled = false;
                        } else {
                            applyManualBtn.disabled = true;
                        }
                    }, 800); // Wait 800ms after user stops typing
                });

                // Enter key to apply
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        if (manualDay.value && manualMonth.value && manualYear.value) {
                            applyManualInput();
                        }
                    }
                });
            });

            applyManualBtn.onclick = applyManualInput;

            renderCalendar();
            modal.classList.remove('hidden');
        }
    });
</script>
