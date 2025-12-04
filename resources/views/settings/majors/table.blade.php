<div class="data-table-container">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="th">STT</th>
                    <th class="th">Tên ngành đào tạo</th>
                    <th class="th">Mã ngành</th>
                    <th class="th">Số sinh viên</th>
                    <th class="th">Ngày tạo</th>
                    <th class="th">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($majors as $index => $major)
                    <tr class="table-row" onclick="toggleRowHighlight(this)">
                        <td class="td">{{ $majors->firstItem() + $index }}</td>
                        <td class="td">
                            <div class="major-info">
                                <span class="major-name">{{ $major->major_name }}</span>
                            </div>
                        </td>
                        <td class="td">
                            <span class="major-code-badge">{{ $major->major_code }}</span>
                        </td>
                        <td class="td">
                            <span class="student-count">{{ number_format($major->students()->count()) }}</span>
                        </td>
                        <td class="td">
                            <span class="date-text">{{ $major->created_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="td">
                            <div class="action-buttons">
                                <a href="{{ route('settings.majors.edit', $major->major_id) }}"
                                    class="btn-action btn-view" title="Chỉnh sửa">
                                    Sửa
                                </a>

                                <form action="{{ route('settings.majors.destroy', $major->major_id) }}" method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Bạn có chắc chắn muốn xóa ngành đào tạo này không?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete" title="Xóa"
                                        {{ $major->students()->count() > 0 ? 'disabled' : '' }}>
                                        Xóa
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="td">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <h3 class="empty-title">Không có dữ liệu</h3>
                                <p class="empty-message">Hệ thống chưa có ngành đào tạo nào được thiết lập.</p>
                                <div class="empty-actions">
                                    <a href="{{ route('settings.majors.create') }}" class="btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Thêm ngành mới
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Custom Pagination Section -->
<div class="table-pagination-wrapper">
    <x-pagination.custom :paginator="$majors" item-name="ngành đào tạo" label="Majors Pagination Navigation"
        :per-page-options="[5, 10, 15, 25, 50]" />
</div>

<style>
    .major-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .major-name {
        font-weight: 500;
        color: #1a1a1a;
    }

    .major-code-badge {
        display: inline-block;
        padding: 0.35rem 0.85rem;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
        font-family: 'Courier New', monospace;
        box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
    }

    .student-count {
        font-weight: 600;
        color: #10b981;
        font-size: 1.05rem;
    }

    .date-text {
        color: #6b7280;
        font-size: 0.95rem;
    }
</style>
