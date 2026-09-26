@if ($paginator->hasPages())
    <div class="pagination-wrapper">
        <div class="pagination-summary">
            Menampilkan <strong>{{ $paginator->firstItem() }}</strong> &ndash; <strong>{{ $paginator->lastItem() }}</strong> dari <strong>{{ $paginator->total() }}</strong> data
        </div>

        <nav class="pagination-nav" role="navigation" aria-label="Navigasi Halaman">
            {{-- Tombol Sebelumnya (Previous) --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-btn disabled" aria-disabled="true" aria-label="Halaman Sebelumnya">
                    <x-icon name="chevron-left" size="15" />
                    <span>Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn" rel="prev" aria-label="Halaman Sebelumnya">
                    <x-icon name="chevron-left" size="15" />
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
                    <x-icon name="chevron-right" size="15" />
                </a>
            @else
                <span class="pagination-btn disabled" aria-disabled="true" aria-label="Halaman Selanjutnya">
                    <span>Selanjutnya</span>
                    <x-icon name="chevron-right" size="15" />
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
