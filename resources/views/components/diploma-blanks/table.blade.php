<div class="diploma-blanks-table-container">
    <table class="diploma-blanks-data-table">
        <thead>
            <tr class="table-header-row">
                <th class="table-header-cell">Số seri</th>
                <th class="table-header-cell">Loại phôi</th>
                <th class="table-header-cell">Trạng thái</th>
                <th class="table-header-cell">Ngày nhập</th>
                <th class="table-header-cell">Ngày cấp</th>
                <th class="table-header-cell">Ngày thu hồi</th>
                <th class="table-header-cell">Lý do cấp</th>
                <th class="table-header-cell">Lý do thu hồi</th>
                <th class="table-header-cell">Hành động</th>
            </tr>
        </thead>
        <tbody class="table-body">
            <tr id="loading" class="loading-overlay hidden">
                <td colspan="9" class="loading-cell">
                    <div class="spinner"></div>
                    <span class="loading-text">Đang tải dữ liệu...</span>
                </td>
            </tr>
            @forelse($diplomaBlanks as $index => $blank)
                <tr class="table-row" data-blank-id="{{ $blank->diploma_blank_id }}" onclick="toggleRowHighlight(this)">
                    <td class="table-cell">
                        <span class="serial-number">{{ $blank->serial_number }}</span>
                    </td>
                    <td class="table-cell">
                        @if ($blank->type)
                            <div class="type-info">
                                <span class="type-name">{{ $blank->type->type_name }}</span>
                                <small
                                    class="type-description text-muted d-block">{{ $blank->type->description ?? '' }}</small>
                            </div>
                        @else
                            <span class="text-muted">Chưa xác định</span>
                        @endif
                    </td>
                    <td class="table-cell">
                        <span
                            class="badge @if ($blank->status === 'InStock') badge-primary
                            @elseif($blank->status === 'Issued') badge-success
                            @elseif($blank->status === 'Damaged') badge-danger
                            @else badge-warning @endif">
                            @switch($blank->status)
                                @case('InStock')
                                    Trong kho
                                @break

                                @case('Issued')
                                    Đã cấp
                                @break

                                @case('Damaged')
                                    Hư hỏng
                                @break

                                @case('Recalled')
                                    Thu hồi
                                @break

                                @default
                                    {{ $blank->status }}
                            @endswitch
                        </span>
                    </td>
                    <td class="table-cell">
                        @if ($blank->import_date)
                            <span class="import-date">{{ $blank->import_date->format('d/m/Y') }}</span>
                        @else
                            <span class="text-muted">--</span>
                        @endif
                    </td>
                    <td class="table-cell">
                        @if ($blank->issue_date)
                            <span class="issue-date">{{ $blank->issue_date->format('d/m/Y') }}</span>
                        @else
                            <span class="text-muted">--</span>
                        @endif
                    </td>
                    <td class="table-cell">
                        @if ($blank->recall_date)
                            <span class="recall-date">{{ $blank->recall_date->format('d/m/Y') }}</span>
                        @else
                            <span class="text-muted">--</span>
                        @endif
                    </td>
                    <td class="table-cell">
                        <div class="issue-reason">
                            @if ($blank->issue_reason)
                                <span class="reason-text">{{ Str::limit($blank->issue_reason, 30) }}</span>
                                @if (strlen($blank->issue_reason) > 30)
                                    <i class="fas fa-info-circle text-info ml-1" title="{{ $blank->issue_reason }}"></i>
                                @endif
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </div>
                    </td>
                    <td class="table-cell">
                        <div class="recall-reason">
                            @if ($blank->recall_reason)
                                <span class="reason-text">{{ Str::limit($blank->recall_reason, 30) }}</span>
                                @if (strlen($blank->recall_reason) > 30)
                                    <i class="fas fa-info-circle text-info ml-1"
                                        title="{{ $blank->recall_reason }}"></i>
                                @endif
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </div>
                    </td>
                    <td class="table-cell">
                        <div class="action-buttons">
                            <button class="btn btn-table btn-sm" title="Xem chi tiết">
                                <i class="fas fa-eye"></i> Xem
                            </button>

                            @if ($blank->status === 'InStock')
                                <button class="btn btn-table btn-sm btn-success" title="Cấp phôi">
                                    <i class="fas fa-paper-plane"></i> Cấp
                                </button>
                            @elseif ($blank->status === 'Issued')
                                <button class="btn btn-table btn-sm btn-warning" title="Thu hồi phôi">
                                    <i class="fas fa-undo"></i> Thu hồi
                                </button>
                            @endif

                            @if ($blank->status !== 'Damaged')
                                <button class="btn btn-table btn-sm btn-danger" title="Báo hư hỏng">
                                    <i class="fas fa-exclamation-triangle"></i> Hư hỏng
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-4 text-center">
                            <div class="empty-state">
                                <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Không tìm thấy phôi văn bằng nào</p>
                                <small class="text-muted">Hãy thử điều chỉnh bộ lọc tìm kiếm hoặc thêm phôi mới</small>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Custom Pagination Section -->
    <div class="diploma-blanks-pagination-wrapper">
        <x-pagination.custom :paginator="$diplomaBlanks" item-name="phôi văn bằng" label="Diploma Blanks Pagination Navigation"
            :per-page-options="[5, 10, 15, 25, 50]" />
    </div>
