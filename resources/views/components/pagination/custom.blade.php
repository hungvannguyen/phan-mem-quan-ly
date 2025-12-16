{{--
    Custom Pagination Component

    Props:
    - $paginator: The Laravel paginator instance
    - $label: Label for accessibility (default: 'Pagination Navigation')
    - $itemName: Name of the items being paginated (default: 'bản ghi')
    - $showPerPageSelector: Whether to show per-page selector (default: true)
    - $perPageOptions: Array of per-page options (default: [5, 10, 15, 25, 50])
--}}

@props([
    'paginator',
    'label' => 'Pagination Navigation',
    'itemName' => 'bản ghi',
    'showPerPageSelector' => true,
    'perPageOptions' => [5, 10, 15, 25, 50],
])

@if ($paginator->hasPages() || $showPerPageSelector)
    <div class="pagination-wrapper">
        {{-- Pagination Info --}}
        <div class="pagination-info">
            <span class="pagination-text">
                Hiển thị {{ $paginator->firstItem() ?? 0 }} đến {{ $paginator->lastItem() ?? 0 }}
                trong tổng số {{ $paginator->total() }} {{ $itemName }}
            </span>
        </div>

        {{-- Pagination Controls --}}
        @if ($paginator->hasPages())
            <div class="pagination-controls">
                <nav class="custom-pagination" role="navigation" aria-label="{{ $label }}">
                    <div class="pagination-container">
                        {{-- Previous Page Link --}}
                        @if ($paginator->onFirstPage())
                            <span class="pagination-btn pagination-btn-disabled" aria-disabled="true"
                                aria-label="Previous">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>

                            </span>
                        @else
                            <button type="button" class="pagination-btn pagination-btn-active"
                                data-url="{{ $paginator->previousPageUrl() }}" rel="prev"
                                aria-label="Go to previous page">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" />
                                </svg>
                            </button>
                        @endif

                        {{-- First Page --}}
                        @if ($paginator->currentPage() > 3)
                            <button type="button" class="pagination-btn pagination-btn-active"
                                data-url="{{ $paginator->url(1) }}" aria-label="Go to page 1">1</button>
                            @if ($paginator->currentPage() > 4)
                                <span class="pagination-dots" aria-hidden="true">...</span>
                            @endif
                        @endif

                        {{-- Page Numbers --}}
                        @for ($i = max(1, $paginator->currentPage() - 2); $i <= min($paginator->lastPage(), $paginator->currentPage() + 2); $i++)
                            @if ($i == $paginator->currentPage())
                                <span class="pagination-btn pagination-btn-current" aria-current="page"
                                    aria-label="Page {{ $i }}, current">{{ $i }}</span>
                            @else
                                <button type="button" class="pagination-btn pagination-btn-active"
                                    data-url="{{ $paginator->url($i) }}"
                                    aria-label="Go to page {{ $i }}">{{ $i }}</button>
                            @endif
                        @endfor

                        {{-- Last Page --}}
                        @if ($paginator->currentPage() < $paginator->lastPage() - 2)
                            @if ($paginator->currentPage() < $paginator->lastPage() - 3)
                                <span class="pagination-dots" aria-hidden="true">...</span>
                            @endif
                            <button type="button" class="pagination-btn pagination-btn-active"
                                data-url="{{ $paginator->url($paginator->lastPage()) }}"
                                aria-label="Go to page {{ $paginator->lastPage() }}">{{ $paginator->lastPage() }}</button>
                        @endif

                        {{-- Next Page Link --}}
                        @if ($paginator->hasMorePages())
                            <button type="button" class="pagination-btn pagination-btn-active"
                                data-url="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Go to next page">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>

                            </button>
                        @else
                            <span class="pagination-btn pagination-btn-disabled" aria-disabled="true" aria-label="Next">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                                </svg>

                            </span>
                        @endif
                    </div>
                </nav>
            </div>
        @endif

        {{-- Per Page Selector --}}
        @if ($showPerPageSelector)
            <div class="pagination-per-page">
                <div class="per-page-selector">
                    <label for="per-page-select" class="per-page-label">Hiển thị:</label>
                    <select id="per-page-select" class="per-page-select" aria-label="Items per page">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}"
                                {{ request('per_page', 15) == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const perPageSelect = document.getElementById('per-page-select');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('per_page', this.value);
                    url.searchParams.delete('page'); // Reset to first page when changing per_page
                    window.location.href = url.toString();
                });
            }

            // Handle pagination button clicks
            const paginationBtns = document.querySelectorAll('.pagination-btn-active[data-url]');
            paginationBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetUrl = new URL(this.dataset.url);
                    const currentUrl = new URL(window.location.href);

                    // Preserve ALL current query parameters except 'page'
                    currentUrl.searchParams.forEach((value, key) => {
                        if (key !== 'page') {
                            targetUrl.searchParams.set(key, value);
                        }
                    });

                    window.location.href = targetUrl.toString();
                });
            });
        });
    </script>
@endif
