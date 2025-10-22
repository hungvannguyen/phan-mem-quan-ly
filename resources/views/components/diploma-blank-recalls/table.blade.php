{{-- Table component for recalled diploma blanks --}}
<div class="data-table-container">
    <!-- Table Header with Results Info -->
    <div class="table-header">
        <div class="table-title">
            Hiển thị {{ $recalledBlanks->firstItem() ?? 0 }} - {{ $recalledBlanks->lastItem() ?? 0 }}
            của {{ number_format($recalledBlanks->total()) }} phôi đã thu hồi
        </div>

        <div class="table-stats">
            <div class="stat-item">
                <label>Hiển thị:</label>
                <select onchange="changePerPage(this.value)" class="ml-2 rounded border px-2 py-1">
                    <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
        </div>
    </div>

    @if ($recalledBlanks->count() > 0)
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="th">#</th>
                        <th class="th">Số Serial</th>
                        <th class="th">Loại phôi</th>
                        <th class="th">Thông tin sinh viên</th>
                        <th class="th">Ngày cấp</th>
                        <th class="th">Ngày thu hồi</th>
                        <th class="th">Lý do thu hồi</th>
                        <th class="th">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recalledBlanks as $index => $blank)
                        <tr class="table-row">
                            <td class="td">{{ $recalledBlanks->firstItem() + $index }}</td>
                            <td class="td">
                                <span class="serial-range">
                                    <span class="range-text">{{ $blank->serial_number }}</span>
                                </span>
                            </td>
                            <td class="td">
                                <span class="type-name">{{ $blank->type->type_name ?? 'N/A' }}</span>
                            </td>
                            <td class="td">
                                @if ($blank->degree)
                                    <div class="table-student-info">
                                        <div class="document-ref">{{ $blank->degree->student_name }}</div>
                                        <div class="student-details">
                                            <small class="date-text">
                                                Ngành: {{ $blank->degree->major->major_name ?? 'N/A' }}<br>
                                                Năm TN: {{ $blank->degree->graduation_year }}
                                            </small>
                                        </div>
                                    </div>
                                @else
                                    <span class="date-text">Chưa có thông tin</span>
                                @endif
                            </td>
                            <td class="td">
                                @if ($blank->issue_date)
                                    <span class="document-ref">{{ $blank->issue_date->format('d/m/Y') }}</span>
                                    <small class="date-text block">{{ $blank->issue_date->format('H:i') }}</small>
                                @else
                                    <span class="date-text">N/A</span>
                                @endif
                            </td>
                            <td class="td">
                                @if ($blank->recall_date)
                                    <span class="document-ref">{{ $blank->recall_date->format('d/m/Y') }}</span>
                                    <small class="date-text block">{{ $blank->recall_date->format('H:i') }}</small>
                                @else
                                    <span class="date-text">N/A</span>
                                @endif
                            </td>
                            <td class="td">
                                @if ($blank->recall_reason)
                                    <div class="table-recall-reason" title="{{ $blank->recall_reason }}">
                                        {{ Str::limit($blank->recall_reason, 100) }}
                                        @if (strlen($blank->recall_reason) > 100)
                                            <button type="button" class="btn-view btn-action"
                                                onclick="showFullReason('{{ $blank->diploma_blank_id }}', '{{ addslashes($blank->recall_reason) }}')">
                                                Xem thêm
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <span class="date-text">N/A</span>
                                @endif
                            </td>
                            <td class="td">
                                <span class="status-badge status-failed">
                                    {{ $blank->status->getLabel() }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="table-pagination-wrapper">
            {{ $recalledBlanks->appends(request()->query())->links() }}
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-search"></i>
            </div>
            <h3 class="empty-title">Không tìm thấy phôi đã thu hồi</h3>
            <p class="empty-message">Không có phôi nào khớp với tiêu chí tìm kiếm của bạn.</p>
            <div class="empty-actions">
                <a href="{{ route('diploma-blank-recalls.management') }}" class="btn-primary">
                    <i class="fas fa-refresh"></i> Xem tất cả
                </a>
            </div>
        </div>
    @endif
</div>
