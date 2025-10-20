@props([
    'id' => 'date_search',
    'name' => 'date_search',
    'label' => 'Tìm kiếm theo ngày',
    'value' => '',
    'placeholder' => 'Nhập để tìm kiếm...',
    'inputClass' => 'field-input',
])

<div class="form-field">
    <label for="{{ $id }}" class="field-label">{{ $label }}</label>
    <div class="flexible-date-search">
        <input type="text" id="{{ $id }}" name="{{ $name }}"
            class="{{ $inputClass }} flexible-date-input" placeholder="{{ $placeholder }}" value="{{ $value }}"
            autocomplete="off">
        <div class="date-search-examples">
            <small class="text-muted">
                Ví dụ: 15, 2024, 03/2024, 15/03, 15/03/2024
            </small>
        </div>
        <div class="date-search-suggestions hidden" id="{{ $id }}_suggestions"></div>
        <div class="input-error-message hidden" id="{{ $id }}_error">
            <small class="text-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span class="error-text"></span>
            </small>
        </div>
    </div>
    {{ $slot }}
</div>

<style>
    .flexible-date-search {
        position: relative;
    }

    .flexible-date-input {
        width: 100%;
        padding-right: 40px;
    }

    .date-search-examples {
        margin-top: 4px;
    }

    .date-search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #d1d5db;
        border-top: none;
        border-radius: 0 0 4px 4px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
    }

    .suggestion-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: background-color 0.2s;
    }

    .suggestion-item:hover {
        background-color: #f9fafb;
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    .suggestion-type {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        font-weight: 500;
    }

    .suggestion-text {
        font-size: 14px;
        color: #374151;
        margin-top: 2px;
    }

    .suggestion-description {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 1px;
    }

    .hidden {
        display: none !important;
    }

    /* Input validation styles */
    .border-red-500 {
        border-color: #ef4444 !important;
    }

    .bg-red-50 {
        background-color: #fef2f2 !important;
    }

    .flexible-date-input.border-red-500:focus {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }

    /* Error message styles */
    .input-error-message {
        margin-top: 4px;
    }

    .text-error {
        color: #ef4444;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .text-error i {
        font-size: 11px;
    }

    .input-error-message.hidden {
        display: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('{{ $id }}');
        const suggestions = document.getElementById('{{ $id }}_suggestions');
        const errorMessage = document.getElementById('{{ $id }}_error');

        if (!input || !suggestions || !errorMessage) return;

        let debounceTimer;

        function showErrorMessage(message) {
            const errorText = errorMessage.querySelector('.error-text');
            if (errorText) {
                errorText.textContent = message;
                errorMessage.classList.remove('hidden');
            }
        }

        function hideErrorMessage() {
            errorMessage.classList.add('hidden');
        }

        function getValidationErrorMessage(input) {
            if (!input || input.trim() === '') return '';

            const trimmed = input.trim();
            const currentYear = new Date().getFullYear();

            // Check if it's a display text format (already validated)
            if (trimmed.startsWith('Ngày ') || trimmed.startsWith('Tháng ') || trimmed.startsWith('Năm ')) {
                return '';
            }

            // Validate single number (day or month or year)
            if (/^\d{1,2}$/.test(trimmed)) {
                const num = parseInt(trimmed);
                if (num < 1 || num > 31) {
                    return 'Số phải từ 1-31';
                }
                return '';
            }

            // Validate year format (YYYY)
            if (/^\d{4}$/.test(trimmed)) {
                const year = parseInt(trimmed);
                if (year < 1900 || year > currentYear + 10) {
                    return `Năm phải từ 1900-${currentYear + 10}`;
                }
                return '';
            }

            // Validate month/year format (MM/YYYY)
            if (/^\d{1,2}\/\d{4}$/.test(trimmed)) {
                const [month, year] = trimmed.split('/').map(n => parseInt(n));
                if (month < 1 || month > 12) {
                    return 'Tháng phải từ 1-12';
                }
                if (year < 1900 || year > currentYear + 10) {
                    return `Năm phải từ 1900-${currentYear + 10}`;
                }
                return '';
            }

            // Validate day/month format (DD/MM)
            if (/^\d{1,2}\/\d{1,2}$/.test(trimmed)) {
                const [day, month] = trimmed.split('/').map(n => parseInt(n));
                if (day < 1 || day > 31) {
                    return 'Ngày phải từ 1-31';
                }
                if (month < 1 || month > 12) {
                    return 'Tháng phải từ 1-12';
                }
                return '';
            }

            // Validate full date format (DD/MM/YYYY)
            if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(trimmed)) {
                const [day, month, year] = trimmed.split('/').map(n => parseInt(n));
                if (day < 1 || day > 31) {
                    return 'Ngày phải từ 1-31';
                }
                if (month < 1 || month > 12) {
                    return 'Tháng phải từ 1-12';
                }
                if (year < 1900 || year > currentYear + 10) {
                    return `Năm phải từ 1900-${currentYear + 10}`;
                }
                return '';
            }

            return 'Định dạng không hợp lệ. Ví dụ: 15, 2024, 03/2024, 15/03, 15/03/2024';
        }

        function isValidDateInput(input) {
            if (!input || input.trim() === '') return true; // Empty is valid (no search criteria)

            const trimmed = input.trim();
            const currentYear = new Date().getFullYear();

            // Check if it's a display text format (already validated)
            if (trimmed.startsWith('Ngày ') || trimmed.startsWith('Tháng ') || trimmed.startsWith('Năm ')) {
                return true;
            }

            // Validate single number (day or month or year)
            if (/^\d{1,2}$/.test(trimmed)) {
                const num = parseInt(trimmed);
                return num >= 1 && num <= 31; // Allow 1-31 (could be day or month)
            }

            // Validate year format (YYYY)
            if (/^\d{4}$/.test(trimmed)) {
                const year = parseInt(trimmed);
                return year >= 1900 && year <= currentYear + 10;
            }

            // Validate month/year format (MM/YYYY)
            if (/^\d{1,2}\/\d{4}$/.test(trimmed)) {
                const [month, year] = trimmed.split('/').map(n => parseInt(n));
                return month >= 1 && month <= 12 && year >= 1900 && year <= currentYear + 10;
            }

            // Validate day/month format (DD/MM)
            if (/^\d{1,2}\/\d{1,2}$/.test(trimmed)) {
                const [day, month] = trimmed.split('/').map(n => parseInt(n));
                return day >= 1 && day <= 31 && month >= 1 && month <= 12;
            }

            // Validate full date format (DD/MM/YYYY)
            if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(trimmed)) {
                const [day, month, year] = trimmed.split('/').map(n => parseInt(n));
                return day >= 1 && day <= 31 && month >= 1 && month <= 12 && year >= 1900 && year <=
                    currentYear + 10;
            }

            return false; // Invalid format
        }

        function convertDisplayTextToBackendFormat(displayText) {
            // Convert display text like "Tháng 3" to backend format "thang:3"
            if (displayText.startsWith('Ngày ')) {
                const day = displayText.replace('Ngày ', '');
                return `ngay:${day}`;
            } else if (displayText.startsWith('Tháng ')) {
                const monthPart = displayText.replace('Tháng ', '');
                if (monthPart.includes('/')) {
                    return `thang_nam:${monthPart}`;
                } else {
                    return `thang:${monthPart}`;
                }
            } else if (displayText.startsWith('Năm ')) {
                const year = displayText.replace('Năm ', '');
                return `nam:${year}`;
            } else if (/^\d{1,2}\/\d{1,2}$/.test(displayText)) {
                return `ngay_thang:${displayText}`;
            } else if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(displayText)) {
                return `ngay_cu_the:${displayText}`;
            }

            // If no pattern matches, return as is
            return displayText;
        }

        function generateSuggestions(query) {
            const suggestions = [];
            const currentYear = new Date().getFullYear();

            if (!query || query.length === 0) return suggestions;

            // Normalize query
            const normalizedQuery = query.toLowerCase().trim();

            // Don't generate suggestions for invalid input
            if (!isValidDateInput(normalizedQuery)) {
                return suggestions;
            }

            // Parse different formats
            if (/^\d{1,2}$/.test(normalizedQuery)) {
                const day = parseInt(normalizedQuery);
                if (day >= 1 && day <= 31) {
                    suggestions.push({
                        type: 'Ngày',
                        text: `Ngày ${day}`,
                        description: `Tìm tất cả ngày ${day} trong mọi tháng/năm`,
                        value: `ngay:${day}`,
                        query: normalizedQuery
                    });
                }

                if (day >= 1 && day <= 12) {
                    suggestions.push({
                        type: 'Tháng',
                        text: `Tháng ${day}`,
                        description: `Tìm tất cả tháng ${day} trong mọi năm`,
                        value: `thang:${day}`,
                        query: normalizedQuery
                    });
                }
            }

            // Year format (YYYY)
            if (/^\d{4}$/.test(normalizedQuery)) {
                const year = parseInt(normalizedQuery);
                if (year >= 1900 && year <= currentYear + 10) {
                    suggestions.push({
                        type: 'Năm',
                        text: `Năm ${year}`,
                        description: `Tìm tất cả năm ${year}`,
                        value: `nam:${year}`,
                        query: normalizedQuery
                    });
                }
            }

            // Month/Year format (MM/YYYY)
            if (/^\d{1,2}\/\d{4}$/.test(normalizedQuery)) {
                const [month, year] = normalizedQuery.split('/').map(n => parseInt(n));
                if (month >= 1 && month <= 12 && year >= 1900 && year <= currentYear + 10) {
                    suggestions.push({
                        type: 'Tháng/Năm',
                        text: `Tháng ${month}/${year}`,
                        description: `Tìm tháng ${month} năm ${year}`,
                        value: `thang_nam:${month}/${year}`,
                        query: normalizedQuery
                    });
                }
            }

            // Day/Month format (DD/MM)
            if (/^\d{1,2}\/\d{1,2}$/.test(normalizedQuery)) {
                const [day, month] = normalizedQuery.split('/').map(n => parseInt(n));
                if (day >= 1 && day <= 31 && month >= 1 && month <= 12) {
                    suggestions.push({
                        type: 'Ngày/Tháng',
                        text: `${day}/${month}`,
                        description: `Tìm ngày ${day} tháng ${month} trong mọi năm`,
                        value: `ngay_thang:${day}/${month}`,
                        query: normalizedQuery
                    });
                }
            }

            // Full date format (DD/MM/YYYY)
            if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(normalizedQuery)) {
                const [day, month, year] = normalizedQuery.split('/').map(n => parseInt(n));
                if (day >= 1 && day <= 31 && month >= 1 && month <= 12 && year >= 1900 && year <= currentYear +
                    10) {
                    suggestions.push({
                        type: 'Ngày cụ thể',
                        text: `${day}/${month}/${year}`,
                        description: `Tìm chính xác ngày ${day}/${month}/${year}`,
                        value: `ngay_cu_the:${day}/${month}/${year}`,
                        query: normalizedQuery
                    });
                }
            }

            return suggestions;
        }

        function showSuggestions(suggestionList) {
            suggestions.innerHTML = '';

            if (suggestionList.length === 0) {
                suggestions.classList.add('hidden');
                return;
            }

            suggestionList.forEach(suggestion => {
                const item = document.createElement('div');
                item.className = 'suggestion-item';
                item.dataset.value = suggestion.value;

                item.innerHTML = `
                    <div class="suggestion-type">${suggestion.type}</div>
                    <div class="suggestion-text">${suggestion.text}</div>
                    <div class="suggestion-description">${suggestion.description}</div>
                `;

                item.addEventListener('click', function() {
                    input.value = suggestion.text;
                    input.dataset.searchValue = suggestion.value;
                    suggestions.classList.add('hidden');

                    // Trigger validation on parent form
                    input.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                });

                suggestions.appendChild(item);
            });

            suggestions.classList.remove('hidden');
        }

        function hideSuggestions() {
            setTimeout(() => {
                suggestions.classList.add('hidden');
            }, 200);
        }

        // Input event handler
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const query = this.value.trim();

                // Validate input and provide visual feedback
                const errorMsg = getValidationErrorMessage(query);
                if (errorMsg) {
                    // Show error styling and message
                    this.classList.add('border-red-500', 'bg-red-50');
                    showErrorMessage(errorMsg);
                } else {
                    // Remove error styling and hide message
                    this.classList.remove('border-red-500', 'bg-red-50');
                    hideErrorMessage();
                }

                const suggestionList = generateSuggestions(query);
                showSuggestions(suggestionList);

                // Auto-set search value if only one suggestion matches exactly
                if (suggestionList.length === 1 && suggestionList[0].query === query) {
                    this.dataset.searchValue = suggestionList[0].value;
                }

                // Trigger validation on parent form
                this.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            }, 300);
        });

        // Focus event - show suggestions if input has value
        input.addEventListener('focus', function() {
            if (this.value.trim()) {
                const suggestionList = generateSuggestions(this.value.trim());
                showSuggestions(suggestionList);
            }
        });

        // Blur event - hide suggestions
        input.addEventListener('blur', hideSuggestions);

        // Click outside to hide suggestions
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                suggestions.classList.add('hidden');
            }
        });

        // Keyboard navigation
        input.addEventListener('keydown', function(e) {
            const items = suggestions.querySelectorAll('.suggestion-item');
            let selected = suggestions.querySelector('.suggestion-item.selected');
            let selectedIndex = selected ? Array.from(items).indexOf(selected) : -1;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (selected) selected.classList.remove('selected');
                selectedIndex = (selectedIndex + 1) % items.length;
                if (items[selectedIndex]) {
                    items[selectedIndex].classList.add('selected');
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (selected) selected.classList.remove('selected');
                selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
                if (items[selectedIndex]) {
                    items[selectedIndex].classList.add('selected');
                }
            } else if (e.key === 'Enter') {
                if (selected) {
                    e.preventDefault();
                    selected.click();
                }
            } else if (e.key === 'Escape') {
                suggestions.classList.add('hidden');
            }
        });

        // Handle form submission - convert display text to backend format
        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                if (input.dataset.searchValue) {
                    // Use the structured search value
                    input.value = input.dataset.searchValue;
                } else if (input.value.trim()) {
                    // Try to convert display text to backend format
                    const backendFormat = convertDisplayTextToBackendFormat(input.value.trim());
                    if (backendFormat !== input.value.trim()) {
                        input.value = backendFormat;
                    }
                }
            });
        }
    });
</script>
