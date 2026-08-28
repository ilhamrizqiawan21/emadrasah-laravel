@props([
    'column',
    'label',
])

@php
    $active = request('sort') === $column;
    $nextDirection = $active && request('direction') === 'asc' ? 'desc' : 'asc';
    $icon = $active
        ? (request('direction') === 'asc' ? 'fa-sort-up' : 'fa-sort-down')
        : 'fa-sort';
    $query = array_merge(request()->query(), [
        'sort' => $column,
        'direction' => $nextDirection,
        'page' => null,
    ]);
@endphp

<a href="{{ url()->current() }}?{{ http_build_query(array_filter($query, fn ($value) => filled($value))) }}" class="em-sort-link">
    <span>{{ $label }}</span>
    <i class="fas {{ $icon }}"></i>
</a>
