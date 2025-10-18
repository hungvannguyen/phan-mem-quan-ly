@extends('layouts.default')

@section('content')
    <main class="management-page">
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
                <div class="header-content">
                    @if ($currentImport)
                        <h1 class="page-title">Phôi Văn bằng từ Import #{{ $currentImport->id }}</h1>
                        <p class="page-subtitle">
                            Văn bản: {{ $currentImport->document_reference }} |
                            Loại: {{ $currentImport->diplomaBlankType->type_name ?? 'N/A' }} |
                            Số lượng: {{ $currentImport->total_quantity }} phôi
                        </p>
                        <div class="import-info mt-3">
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Dải Serial:</label>
                                    <span>{{ $currentImport->prefix }}{{ $currentImport->from_number }}{{ $currentImport->suffix }}
                                        →
                                        {{ $currentImport->prefix }}{{ $currentImport->to_number }}{{ $currentImport->suffix }}</span>
                                </div>
                                <div class="info-item">
                                    <label>Tiến độ:</label>
                                    <span>{{ $currentImport->processed_count }}/{{ $currentImport->total_quantity }}
                                        ({{ number_format(($currentImport->processed_count / $currentImport->total_quantity) * 100, 1) }}%)</span>
                                </div>
                                <div class="info-item">
                                    <label>Trạng thái:</label>
                                    <span class="status-badge {{ $currentImport->status->getBadgeClass() }}"
                                        id="import-status">
                                        {{ $currentImport->status->getLabel() }}
                                    </span>
                                    @if ($currentImport->status === \App\Enums\ImportStatus::PROCESSING)
                                        <span id="processing-indicator" class="ml-2 text-blue-600">
                                            <i class="fas fa-spinner fa-spin"></i> Đang cập nhật...
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <h1 class="page-title">Danh sách Phôi Văn bằng</h1>
                        <p class="page-subtitle">Tìm kiếm và quản lý phôi văn bằng trong hệ thống</p>
                    @endif
                </div>
                <div class="header-actions">
                    @if ($currentImport)
                        @if ($currentImport->status === \App\Enums\ImportStatus::COMPLETED)
                            <button onclick="openUpdateImportModal()" class="btn-secondary me-2">
                                <i class="fas fa-edit"></i> Cập nhật Import
                            </button>
                        @elseif ($currentImport->status === \App\Enums\ImportStatus::PROCESSING)
                            <button disabled class="btn-secondary me-2 cursor-not-allowed opacity-50">
                                <i class="fas fa-spinner fa-spin"></i> Đang cập nhật...
                            </button>
                        @endif
                        <a href="{{ route('diploma-blank-management') }}" class="btn-back">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách Import
                        </a>
                    @else
                        <a href="{{ route('diploma-blank-import.create') }}" class="btn-primary">
                            <i class="fas fa-plus"></i> Nhập phôi mới
                        </a>
                    @endif
                </div>
            </div>

            <!-- Search Form -->
            <div class="search-card">
                @if (isset($importId))
                    <form class="search-form" method="GET"
                        action="{{ route('diploma-blanks.list-by-import', $importId) }}">
                    @else
                        <form class="search-form" method="GET" action="{{ route('diploma-blanks.index') }}">
                @endif

                <div class="form-grid">
                    <div class="form-field">
                        <label for="serial_number" class="field-label">Số Serial</label>
                        <input type="text" id="serial_number" name="serial_number" class="field-input"
                            placeholder="Nhập số serial" value="{{ request('serial_number') }}">
                    </div>

                    @if (!$currentImport)
                        <div class="form-field">
                            <label for="type_id" class="field-label">Loại phôi văn bằng</label>
                            <select id="type_id" name="type_id" class="field-select">
                                <option value="">-- Tất cả loại phôi --</option>
                                @foreach ($diplomaBlankTypes as $type)
                                    <option value="{{ $type->type_id }}"
                                        {{ request('type_id') == $type->type_id ? 'selected' : '' }}>
                                        {{ $type->type_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="form-field">
                        <label for="status" class="field-label">Trạng thái</label>
                        <select id="status" name="status" class="field-select">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="InStock" {{ request('status') == 'InStock' ? 'selected' : '' }}>Trong kho
                            </option>
                            <option value="Issued" {{ request('status') == 'Issued' ? 'selected' : '' }}>Đã cấp
                            </option>
                            <option value="Recalled" {{ request('status') == 'Recalled' ? 'selected' : '' }}>Thu hồi
                            </option>
                            <option value="Damaged" {{ request('status') == 'Damaged' ? 'selected' : '' }}>Hư hỏng
                            </option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="import_date_from" class="field-label">Ngày nhập từ</label>
                        <input type="date" id="import_date_from" name="import_date_from" class="field-input"
                            value="{{ request('import_date_from') }}">
                    </div>

                    <div class="form-field">
                        <label for="import_date_to" class="field-label">Ngày nhập đến</label>
                        <input type="date" id="import_date_to" name="import_date_to" class="field-input"
                            value="{{ request('import_date_to') }}">
                    </div>
                </div>

                <div class="search-actions">
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    @if (isset($importId))
                        <a href="{{ route('diploma-blanks.list-by-import', $importId) }}" class="btn-reset">
                            <i class="fas fa-undo"></i> Đặt lại
                        </a>
                    @else
                        <a href="{{ route('diploma-blanks.index') }}" class="btn-reset">
                            <i class="fas fa-undo"></i> Đặt lại
                        </a>
                    @endif
                </div>
                </form>
            </div>

            <!-- Results Table -->
            <div class="results-section">
                @if ($diplomaBlanks->total() > 0)
                    <div class="results-header">
                        <h3 class="results-title">
                            Tìm thấy {{ $diplomaBlanks->total() }} phôi văn bằng
                            @if ($currentImport)
                                từ Import #{{ $currentImport->id }}
                            @endif
                        </h3>
                    </div>

                    <!-- Include table component -->
                    <x-diploma-blanks.table :diplomaBlanks="$diplomaBlanks" :importId="$importId ?? null" :damageReasons="$damageReasons" />
                @else
                    <div class="empty-results">
                        <div class="empty-icon">
                            <i class="fas fa-search-minus"></i>
                        </div>
                        <h3>Không tìm thấy kết quả</h3>
                        <p>Không có phôi văn bằng nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                        @if ($currentImport && $currentImport->status->value == 0)
                            <p class="text-info">
                                <i class="fas fa-info-circle"></i>
                                Import này đang ở trạng thái PENDING. Các phôi sẽ được tạo tự động trong vài phút.
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </main>

    @if ($currentImport)
        <style>
            .import-info {
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                padding: 1rem;
            }

            .info-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1rem;
            }

            .info-item {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .info-item label {
                font-weight: 600;
                color: #495057;
                font-size: 0.875rem;
            }

            .info-item span {
                color: #212529;
            }

            .status-badge {
                display: inline-block;
                padding: 0.25rem 0.75rem;
                border-radius: 0.375rem;
                font-size: 0.75rem;
                font-weight: 500;
            }

            /* Modal Styles */
            .field-group {
                display: flex;
                flex-direction: column;
            }

            .field-label {
                font-weight: 600;
                color: #374151;
                margin-bottom: 0.5rem;
            }

            .field-label.required::after {
                content: ' *';
                color: #ef4444;
            }

            .field-input {
                padding: 0.75rem;
                border: 1px solid #d1d5db;
                border-radius: 0.5rem;
                font-size: 1rem;
                transition: border-color 0.2s;
            }

            .field-input:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            .btn-secondary {
                background-color: #6b7280;
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 0.5rem;
                text-decoration: none;
                border: none;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                font-weight: 600;
                transition: all 0.2s;
            }

            .btn-secondary:hover {
                background-color: #4b5563;
                color: white;
                text-decoration: none;
            }
        </style>

        @if ($currentImport && $currentImport->status === \App\Enums\ImportStatus::COMPLETED)
            <!-- Update Import Modal -->
            <div id="updateImportModal"
                class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
                <div class="mx-4 w-full max-w-lg rounded-lg bg-white p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-edit mr-2 text-blue-600"></i>
                            Cập nhật Import #{{ $currentImport->id }}
                        </h3>
                        <button type="button" onclick="closeUpdateImportModal()"
                            class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <form id="updateImportForm" method="POST"
                        action="{{ route('diploma-blank-import.update', $currentImport->id ?? 0) }}">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="field-group">
                                    <label for="update_prefix" class="field-label">Tiền tố</label>
                                    <input type="text" name="prefix" id="update_prefix" class="field-input"
                                        value="{{ $currentImport->prefix }}" placeholder="VD: VB">
                                </div>

                                <div class="field-group">
                                    <label for="update_suffix" class="field-label">Hậu tố</label>
                                    <input type="text" name="suffix" id="update_suffix" class="field-input"
                                        value="{{ $currentImport->suffix }}" placeholder="VD: 2024">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="field-group">
                                    <label for="update_from_number" class="field-label required">Số bắt đầu</label>
                                    <input type="text" name="from_number" id="update_from_number" class="field-input"
                                        value="{{ $currentImport->from_number }}" placeholder="VD: 001" required>
                                </div>

                                <div class="field-group">
                                    <label for="update_to_number" class="field-label required">Số kết thúc</label>
                                    <input type="text" name="to_number" id="update_to_number" class="field-input"
                                        value="{{ $currentImport->to_number }}" placeholder="VD: 100" required>
                                </div>
                            </div>

                            <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                                <div class="flex">
                                    <i class="fas fa-exclamation-triangle mr-2 mt-1 text-yellow-600"></i>
                                    <div class="text-sm text-yellow-800">
                                        <p class="mb-1 font-medium">Lưu ý quan trọng:</p>
                                        <ul class="list-inside list-disc space-y-1">
                                            <li>Nếu tăng số lượng: Sẽ thêm phôi mới từ số cuối cùng đã xử lý</li>
                                            <li>Nếu giảm số lượng: Sẽ xóa các phôi chưa sử dụng (chỉ status IN_STOCK)</li>
                                            <li>Thay đổi prefix/suffix sẽ cập nhật tất cả phôi hiện có</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3 border-t pt-4">
                            <button type="button" onclick="closeUpdateImportModal()"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-gray-600 hover:bg-gray-50">
                                <i class="fas fa-times mr-2"></i>Hủy
                            </button>
                            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                                onclick="return confirmUpdate()">
                                <i class="fas fa-save mr-2"></i>Cập nhật
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function openUpdateImportModal() {
                    document.getElementById('updateImportModal').classList.remove('hidden');
                    document.getElementById('updateImportModal').classList.add('flex');
                }

                function closeUpdateImportModal() {
                    document.getElementById('updateImportModal').classList.add('hidden');
                    document.getElementById('updateImportModal').classList.remove('flex');
                }

                function confirmUpdate() {
                    const prefix = document.getElementById('update_prefix').value;
                    const suffix = document.getElementById('update_suffix').value;
                    const fromNumber = document.getElementById('update_from_number').value;
                    const toNumber = document.getElementById('update_to_number').value;

                    const message = `Bạn có chắc chắn muốn cập nhật import với thông tin sau?\n\n` +
                        `Tiền tố: ${prefix || '(trống)'}\n` +
                        `Hậu tố: ${suffix || '(trống)'}\n` +
                        `Từ số: ${fromNumber}\n` +
                        `Đến số: ${toNumber}\n\n` +
                        `Lưu ý: Quá trình cập nhật sẽ chạy trong background và có thể mất vài phút.`;

                    return confirm(message);
                }

                // Close modal when clicking outside

                // Close modal when clicking outside
                document.addEventListener('click', function(event) {
                    const modal = document.getElementById('updateImportModal');
                    if (event.target === modal) {
                        closeUpdateImportModal();
                    }
                });

                @if ($currentImport && $currentImport->status === \App\Enums\ImportStatus::PROCESSING)
                    // Auto refresh khi import đang processing
                    setTimeout(() => {
                        window.location.reload();
                    }, 10000); // Refresh mỗi 10 giây
                @endif
            </script>
        @endif
        </script>
    @endif
@endsection
