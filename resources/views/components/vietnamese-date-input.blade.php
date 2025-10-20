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

        // Function to parse Vietnamese date format to ISO format
        function parseVietnameseDate(vietnameseDate) {
            if (!vietnameseDate) return '';
            const parts = vietnameseDate.split('/');
            if (parts.length === 3) {
                const day = parts[0].padStart(2, '0');
                const month = parts[1].padStart(2, '0');
                const year = parts[2];
                return `${year}-${month}-${day}`;
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

                // Auto-format as user types
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2);
                }
                if (value.length >= 5) {
                    value = value.substring(0, 5) + '/' + value.substring(5, 9);
                }

                e.target.value = value;

                // Parse and set the hidden date input
                if (value.length === 10) {
                    const isoDate = parseVietnameseDate(value);
                    if (isoDate) {
                        hiddenInput.value = isoDate;
                        // Validate date
                        const testDate = new Date(isoDate);
                        if (testDate.getFullYear() < 1900 || testDate.getFullYear() > 2010) {
                            e.target.setCustomValidity('Năm sinh không hợp lệ (1900-2010)');
                        } else {
                            e.target.setCustomValidity('');
                        }
                    }
                } else {
                    hiddenInput.value = '';
                    e.target.setCustomValidity('');
                }
            });

            // Handle date picker button click
            if (pickerBtn) {
                pickerBtn.addEventListener('click', function() {
                    hiddenInput.type = 'date';
                    hiddenInput.style.position = 'absolute';
                    hiddenInput.style.top = '0';
                    hiddenInput.style.left = '0';
                    hiddenInput.style.width = '100%';
                    hiddenInput.style.height = '100%';
                    hiddenInput.style.opacity = '0';
                    hiddenInput.style.cursor = 'pointer';

                    // Trigger the date picker
                    hiddenInput.focus();
                    hiddenInput.click();
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
    });
</script>
