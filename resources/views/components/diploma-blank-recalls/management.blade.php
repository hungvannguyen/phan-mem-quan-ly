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
                <h1 class="page-title">Danh sách Phôi đã Thu hồi</h1>
                <p class="page-subtitle">Quản lý và theo dõi các phôi văn bằng đã được thu hồi</p>
            </div>

            <!-- Statistics Cards -->
            <div class="recalled-list-statistics">
                <div class="stat-card stat-total">
                    <div class="stat-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ number_format($statistics['total_recalled']) }}</h3>
                        <p>Tổng phôi đã thu hồi</p>
                    </div>
                </div>

                <div class="stat-card stat-today">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ number_format($statistics['recalled_today']) }}</h3>
                        <p>Thu hồi hôm nay</p>
                    </div>
                </div>

                <div class="stat-card stat-month">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ number_format($statistics['recalled_this_month']) }}</h3>
                        <p>Thu hồi tháng này</p>
                    </div>
                </div>
            </div>

            <!-- Search Form -->
            <div class="search-card">
                <form class="search-form" method="GET" action="{{ route('diploma-blank-recalls.management') }}">
                    <div class="form-grid">
                        <div class="field-group">
                            <label for="serial_number" class="field-label">Số Serial</label>
                            <input type="text" id="serial_number" name="serial_number" class="field-input"
                                placeholder="Nhập số serial phôi" value="{{ request('serial_number') }}">
                        </div>

                        <div class="field-group">
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

                        <div class="field-group">
                            <label for="recall_reason" class="field-label">Lý do thu hồi</label>
                            <input type="text" id="recall_reason" name="recall_reason" class="field-input"
                                placeholder="Nhập lý do thu hồi" value="{{ request('recall_reason') }}">
                        </div>

                        <x-vietnamese-date-input id="recall_date_from" name="recall_date_from" label="Ngày thu hồi từ"
                            :required="false" value="{{ request('recall_date_from') }}" />

                        <x-vietnamese-date-input id="recall_date_to" name="recall_date_to" label="Ngày thu hồi đến"
                            :required="false" value="{{ request('recall_date_to') }}" />
                    </div>

                    <!-- Search Actions -->
                    <div class="search-actions">
                        <button type="submit" class="btn-search">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <a href="{{ route('diploma-blank-recalls.management') }}" class="btn-reset">
                            <i class="fas fa-refresh"></i> Xóa bộ lọc
                        </a>
                    </div>
                </form>
            </div>

            <!-- Action Buttons -->`
            <div class="page-actions">
                <a href="{{ route('diploma-blank-recalls.index') }}" class="action-btn action-btn-warning">
                    <i class="fas fa-undo"></i> Thu hồi phôi mới
                </a>
                <a href="{{ route('diploma-blank-import.index') }}" class="action-btn action-btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại quản lý
                </a>
                <button type="button" class="action-btn action-btn-success" onclick="exportRecalledBlanks()">
                    <i class="fas fa-file-excel"></i> Xuất Excel
                </button>
            </div>
        </div>

        <div class="table-section">
            <div class="table-wrapper">
                @include('components.diploma-blank-recalls.table')
            </div>
        </div>
    </main>

    <!-- Modal hiển thị lý do thu hồi đầy đủ -->
    <div class="modal fade recalled-list-modal" id="reasonModal" tabindex="-1" aria-labelledby="reasonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reasonModalLabel">Lý do thu hồi phôi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="fullReasonContent" class="full-reason-content"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Styles are now handled by SCSS in resources/scss/components/_recalled-list.scss --}}

    <script>
        function changePerPage(perPage) {
            const url = new URL(window.location);
            url.searchParams.set('per_page', perPage);
            url.searchParams.delete('page'); // Reset to first page
            window.location.href = url.toString();
        }

        function showFullReason(diplomaBlankId, reason) {
            const contentDiv = document.getElementById('fullReasonContent');
            contentDiv.innerHTML = reason.replace(/\n/g, '<br>');

            const modal = new bootstrap.Modal(document.getElementById('reasonModal'));
            modal.show();
        }

        function exportRecalledBlanks() {
            // Placeholder for export functionality
            alert('Chức năng xuất Excel sẽ được triển khai sau.');
        }
    </script>
@endsection
