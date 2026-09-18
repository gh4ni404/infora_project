@props([
    'paginator',
])

@if ($paginator->hasPages() || $paginator->total() > 0)
    <div class="table-footer">
        {{ $paginator->links() }}
    </div>
@endif