{{--
    Pagination Component Usage Examples

    This file demonstrates how to use the custom pagination component
    with different configurations and data types.
--}}

{{-- Basic Usage --}}
<x-pagination.custom :paginator="$students" />

{{-- With Custom Item Name --}}
<x-pagination.custom :paginator="$users" item-name="người dùng" />

{{-- With Custom Per-Page Options --}}
<x-pagination.custom :paginator="$products" item-name="sản phẩm" :per-page-options="[10, 20, 50, 100]" />

{{-- Without Per-Page Selector --}}
<x-pagination.custom :paginator="$orders" item-name="đơn hàng" :show-per-page-selector="false" />

{{-- With Custom Label for Accessibility --}}
<x-pagination.custom :paginator="$invoices" item-name="hóa đơn" label="Invoice List Pagination" />

{{-- Full Configuration Example --}}
<x-pagination.custom :paginator="$reports" item-name="báo cáo" label="Reports Pagination Navigation" :show-per-page-selector="true"
    :per-page-options="[5, 15, 30, 60]" />

{{-- Usage with Different Styling (add CSS classes to wrapper) --}}
<div class="pagination-wrapper pagination-dark">
    <x-pagination.custom :paginator="$notifications" item-name="thông báo" />
</div>

<div class="pagination-wrapper pagination-minimal">
    <x-pagination.custom :paginator="$comments" item-name="bình luận" :show-per-page-selector="false" />
</div>

<div class="pagination-wrapper pagination-compact">
    <x-pagination.custom :paginator="$tags" item-name="thẻ" :per-page-options="[20, 50, 100]" />
</div>

{{--
    CSS Classes Available:
    - .pagination-wrapper (base styling)
    - .pagination-dark (dark theme)
    - .pagination-minimal (minimal styling)
    - .pagination-compact (smaller size)

    You can also create custom themes by extending these classes
    or by creating new wrapper classes.
--}}
