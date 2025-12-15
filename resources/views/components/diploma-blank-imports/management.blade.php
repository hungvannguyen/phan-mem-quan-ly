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

        <div class="form-section">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Quản lý Nhập Phôi Văn bằng</h1>
                <p class="page-subtitle">Tìm kiếm và quản lý lịch sử nhập phôi văn bằng vào hệ thống</p>
            </div>

            <!-- Search Form -->
            <div class="search-card">
                <form class="search-form" method="GET" action="{{ route('diploma-blank-import.index') }}">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="document_reference" class="field-label">Số văn bản</label>
                            <input type="text" id="document_reference" name="document_reference" class="field-input"
                                placeholder="Nhập số văn bản" value="{{ request('document_reference') }}">
                        </div>

                        <div class="form-field">
                            <label for="type_id" class="field-label">Loại phôi văn bằng</label>
                            <select id="type_id" name="type_id" class="field-select">
                                <option value="">-- Tất cả loại phôi --</option>
                                @if (isset($diplomaBlankTypes))
                                    @foreach ($diplomaBlankTypes as $type)
                                        <option value="{{ $type->type_id }}"
                                            {{ request('type_id') == $type->type_id ? 'selected' : '' }}>
                                            {{ $type->type_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="status" class="field-label">Trạng thái nhập</label>
                            <select id="status" name="status" class="field-select">
                                <option value="">-- Tất cả trạng thái --</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>
                                    Chờ xử lý
                                </option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>
                                    Đang xử lý
                                </option>
                                <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>
                                    Hoàn thành
                                </option>
                                <option value="3" {{ request('status') === '3' ? 'selected' : '' }}>
                                    Lỗi
                                </option>
                            </select>
                        </div>

                        <x-vietnamese-date-input id="import_date_from" name="import_date_from" label="Ngày nhập từ"
                            :required="false" value="{{ request('import_date_from') }}" />

                        <x-vietnamese-date-input id="import_date_to" name="import_date_to" label="Ngày nhập đến"
                            :required="false" value="{{ request('import_date_to') }}" />

                        <x-vietnamese-date-input id="issue_date_from" name="issue_date_from" label="Ngày ban hành từ"
                            :required="false" value="{{ request('issue_date_from') }}" />
                    </div>

                    <!-- Search Actions -->
                    <div class="search-actions">
                        <button type="submit" class="btn-search" id="search-btn" disabled>
                            Tìm kiếm
                        </button>
                        <a href="{{ route('diploma-blank-import.index') }}" class="btn-reset">
                            Xóa bộ lọc
                        </a>
                    </div>
                </form>
            </div>

            <!-- Action Buttons -->
            <div class="page-actions">
                <a href="{{ route('diploma-blank-import.create') }}" class="action-btn action-btn-primary">
                    Nhập phôi mới
                </a>
                <button type="button" class="action-btn action-btn-warning">
                    Xuất phôi
                </button>
                <a href="{{ route('diploma-blank-exports.index') }}" class="action-btn action-btn-info">
                    Lịch sử xuất phôi
                </a>
                <a href="{{ route('diploma-blank-recalls.index') }}" class="action-btn action-btn-danger">
                    Thu hồi phôi
                </a>
                <a href="{{ route('diploma-blank-recalls.management') }}" class="action-btn action-btn-secondary">
                    Phôi đã thu hồi
                </a>
                {{-- <button type="button" class="action-btn action-btn-success">
                    Xuất Excel
                </button>
                <button type="button" class="action-btn action-btn-secondary">
                    Đồng bộ dữ liệu
                </button> --}}
            </div>
        </div>

        <div class="table-section">
            <div class="table-wrapper" id="table-data">
                @include('components.diploma-blank-imports.table')
            </div>
        </div>
    </main>

    {{-- Include Modal xuất phôi --}}
    @include('components.diploma-blank-exports.modal')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get form elements
            const searchForm = document.querySelector('.search-form');
            const searchBtn = document.getElementById('search-btn');
            const formFields = [
                'document_reference',
                'type_id',
                'status',
                'import_date_from',
                'import_date_to',
                'issue_date_from'
            ];

            // Function to check if form has any data
            function validateForm() {
                let hasData = false;

                formFields.forEach(fieldName => {
                    const field = document.querySelector(`[name="${fieldName}"]`);
                    if (field && field.value && field.value.trim() !== '') {
                        hasData = true;
                    }
                });

                // Enable/disable search button
                if (hasData) {
                    searchBtn.disabled = false;
                    searchBtn.classList.remove('btn-search-disabled');
                    searchBtn.classList.add('btn-search-enabled');
                } else {
                    searchBtn.disabled = true;
                    searchBtn.classList.add('btn-search-disabled');
                    searchBtn.classList.remove('btn-search-enabled');
                }
            }

            // Add event listeners to all form fields
            formFields.forEach(fieldName => {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    // Handle different input types
                    if (field.type === 'select-one') {
                        field.addEventListener('change', validateForm);
                    } else {
                        field.addEventListener('input', validateForm);
                        field.addEventListener('change', validateForm);
                    }
                }
            });

            // Initial validation on page load
            validateForm();

            // Prevent form submission if no data
            searchForm.addEventListener('submit', function(e) {
                if (searchBtn.disabled) {
                    e.preventDefault();
                    alert('Vui lòng nhập ít nhất một tiêu chí tìm kiếm!');
                    return false;
                }
            });
        });
    </script>
@endsection
