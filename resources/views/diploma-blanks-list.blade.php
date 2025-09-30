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
                                    <span class="status-badge {{ $currentImport->status->getBadgeClass() }}">
                                        {{ $currentImport->status->getLabel() }}
                                    </span>
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
                <form class="search-form" method="GET" action="{{ route('diploma-blanks.index') }}">
                    @if ($currentImport)
                        <input type="hidden" name="import_id" value="{{ $currentImport->id }}">
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
                        <a href="{{ route('diploma-blanks.index') }}{{ $currentImport ? '?import_id=' . $currentImport->id : '' }}"
                            class="btn-reset">
                            <i class="fas fa-undo"></i> Đặt lại
                        </a>
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
                    <x-diploma-blanks.table :diplomaBlanks="$diplomaBlanks" />
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
        </style>
    @endif
@endsection
