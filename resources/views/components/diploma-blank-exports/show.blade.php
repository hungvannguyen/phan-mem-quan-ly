@extends('layouts.default')

@section('content')
    <main class="management-page">
        <div class="form-section">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Chi tiết xuất phôi #EX{{ str_pad($export->export_id, 6, '0', STR_PAD_LEFT) }}</h1>
                <p class="page-subtitle">Thông tin chi tiết về lần xuất phôi</p>
            </div>

            <!-- Action Buttons -->
            <div class="page-actions">
                <a href="{{ route('diploma-blank-exports.index') }}" class="action-btn action-btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại danh sách
                </a>
                <button type="button" class="action-btn action-btn-info" onclick="window.print()">
                    <i class="fas fa-print"></i> In báo cáo
                </button>
            </div>

            <!-- Export Information -->
            <div class="export-show-info-card">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Mã xuất phôi:</label>
                            <span class="info-export-id">EX{{ str_pad($export->export_id, 6, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="info-group">
                            <label>Loại văn bằng:</label>
                            <span>{{ $export->type->type_name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-group">
                            <label>Khóa học:</label>
                            <span>{{ $export->course ?? 'N/A' }}</span>
                        </div>
                        <div class="info-group">
                            <label>Năm tốt nghiệp:</label>
                            <span>{{ $export->graduation_year }}</span>
                        </div>
                        <div class="info-group">
                            <label>Quyết định tốt nghiệp:</label>
                            <span>{{ $export->decision_number }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Ngày ban hành:</label>
                            <span>{{ $export->issue_date ? $export->issue_date->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="info-group">
                            <label>Số lượng yêu cầu:</label>
                            <span class="badge badge-primary">{{ $export->quantity_requested }}</span>
                        </div>
                        <div class="info-group">
                            <label>Số lượng đã xuất:</label>
                            <span class="badge badge-success">{{ $export->quantity_exported }}</span>
                        </div>
                        <div class="info-group">
                            <label>Người xuất:</label>
                            <span>{{ $export->exportedBy->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-group">
                            <label>Ngày xuất:</label>
                            <span>{{ $export->export_date ? $export->export_date->format('d/m/Y H:i:s') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                @if ($export->notes)
                    <div class="info-group full-width">
                        <label>Ghi chú:</label>
                        <div class="info-notes-content">{{ $export->notes }}</div>
                    </div>
                @endif
            </div>

            <!-- Export Ranges -->
            <div class="export-show-ranges-card">
                <h5>Dải Serial đã xuất</h5>
                @if (is_array($export->export_ranges) && count($export->export_ranges) > 0)
                    <div class="ranges-list">
                        @foreach ($export->export_ranges as $index => $range)
                            <div class="ranges-item">
                                <div class="ranges-info">
                                    <span class="ranges-serial">{{ $range['from_serial'] }} -
                                        {{ $range['to_serial'] }}</span>
                                    <span class="ranges-count">({{ $range['count'] ?? 0 }} phôi)</span>
                                </div>
                                <div class="ranges-index">Dải {{ $index + 1 }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">Không có thông tin dải serial</p>
                @endif
            </div>
        </div>

        <!-- Detailed List of Exported Blanks -->
        <div class="table-section">
            <h5>Danh sách phôi đã xuất</h5>
            <div class="table-wrapper">
                @if ($export->diplomaBlanks->count() > 0)
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Số serial</th>
                                <th>Loại phôi</th>
                                <th>Ngày nhập</th>
                                <th>Ngày xuất</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($export->diplomaBlanks as $index => $blank)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="serial-number">{{ $blank->serial_number }}</span>
                                    </td>
                                    <td>{{ $blank->type->type_name ?? 'N/A' }}</td>
                                    <td>{{ $blank->import_date ? $blank->import_date->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $blank->issue_date ? $blank->issue_date->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        <span class="status-badge {{ $blank->status->getBadgeClass() }}">
                                            {{ $blank->status->getLabel() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <p class="text-muted">Không có danh sách phôi chi tiết</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    {{-- Styles are now handled by SCSS in resources/scss/components/_export-show.scss --}}
@endsection
