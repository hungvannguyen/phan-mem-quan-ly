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
                <h1 class="page-title">Lịch sử xuất phôi</h1>
                <p class="page-subtitle">Quản lý và theo dõi lịch sử xuất phôi văn bằng</p>
            </div>

            <!-- Action Buttons -->
            <div class="page-actions">
                <a href="{{ route('diploma-blank-import.index') }}" class="action-btn action-btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại quản lý phôi
                </a>
            </div>
        </div>

        <div class="table-section">
            <div class="table-wrapper">
                @if ($exports->count() > 0)
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã xuất</th>
                                <th>Loại phôi</th>
                                <th>Khóa/Năm TN</th>
                                <th>Quyết định</th>
                                <th>Ngày ban hành</th>
                                <th>Số lượng</th>
                                <th>Người xuất</th>
                                <th>Ngày xuất</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($exports as $export)
                                <tr>
                                    <td>
                                        <span
                                            class="export-id">EX{{ str_pad($export->export_id, 6, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td>{{ $export->type->type_name ?? 'N/A' }}</td>
                                    <td>
                                        @if ($export->course)
                                            {{ $export->course }} /
                                        @endif
                                        {{ $export->graduation_year }}
                                    </td>
                                    <td>{{ $export->decision_number }}</td>
                                    <td>{{ $export->issue_date ? $export->issue_date->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $export->quantity_exported }}/{{ $export->quantity_requested }}
                                        </span>
                                    </td>
                                    <td>{{ $export->exportedBy->name ?? 'N/A' }}</td>
                                    <td>{{ $export->export_date ? $export->export_date->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('diploma-blank-exports.show', $export->export_id) }}"
                                                class="btn btn-sm btn-info" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    @if ($exports->hasPages())
                        <div class="pagination-wrapper">
                            {{ $exports->links() }}
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <h3>Chưa có lịch sử xuất phôi</h3>
                        <p>Chưa có bản ghi xuất phôi nào trong hệ thống.</p>
                        <a href="{{ route('diploma-blank-import.index') }}" class="btn btn-primary">
                            Quay lại quản lý phôi
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <style>
        .export-id {
            font-weight: bold;
            color: #007bff;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
            color: #dee2e6;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #495057;
        }

        .empty-state p {
            margin-bottom: 20px;
            font-size: 16px;
        }
    </style>
@endsection
