@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-outline" style="opacity: 0.5; cursor: default;">
                <i class="ph ph-caret-left"></i> Previous
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline">
                <i class="ph ph-caret-left"></i> Previous
            </a>
        @endif

        <span style="font-size: 0.85rem; color: var(--text-secondary);">
            Page {{ $paginator->currentPage() }}
        </span>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-outline">
                Next <i class="ph ph-caret-right"></i>
            </a>
        @else
            <span class="btn btn-outline" style="opacity: 0.5; cursor: default;">
                Next <i class="ph ph-caret-right"></i>
            </span>
        @endif
    </nav>
@endif
