@if ($paginator->hasPages())
    <div class="pagination-wrapper">
        <div class="pagination-summary">
            Menampilkan <strong>{{ $paginator->firstItem() }}</strong> &ndash; <strong>{{ $paginator->lastItem() }}</strong> dari <strong>{{ $paginator->total() }}</strong> data
        </div>

        <nav class="pagination-nav" role="navigation" aria-label="Navigasi Halaman">
            {{-- Tombol Sebelumnya (Previous) --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-btn disabled" aria-disabled="true" aria-label="Halaman Sebelumnya">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn" rel="prev" aria-label="Halaman Sebelumnya">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Sebelumnya</span>
                </a>
            @endif

            {{-- Nomor Halaman (Page Numbers) --}}
            <div class="pagination-pages">
                @foreach ($elements as $element)
                    {{-- Separator Titik Tiga (...) --}}
                    @if (is_string($element))
                        <span class="pagination-ellipsis" aria-disabled="true">{{ $element }}</span>
                    @endif

                    {{-- Daftar Link Halaman --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="pagination-page active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="pagination-page" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Tombol Selanjutnya (Next) --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn" rel="next" aria-label="Halaman Selanjutnya">
                    <span>Selanjutnya</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            @else
                <span class="pagination-btn disabled" aria-disabled="true" aria-label="Halaman Selanjutnya">
                    <span>Selanjutnya</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </span>
            @endif
        </nav>
    </div>
@elseif ($paginator->total() > 0)
    <div class="pagination-wrapper pagination-single-page">
        <div class="pagination-summary">
            Menampilkan seluruh <strong>{{ $paginator->total() }}</strong> data
        </div>
    </div>
@endif
