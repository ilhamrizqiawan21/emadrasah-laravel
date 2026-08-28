@props([
    'title',
    'subtitle' => null,
    'createRoute' => null,
    'createLabel' => null,
    'icon' => 'fa-plus',
])

<div class="em-page-header">
    <div>
        <h1 class="em-page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="em-page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($createRoute && $createLabel)
        <a href="{{ $createRoute }}" class="btn btn-primary em-page-action">
            <i class="fas {{ $icon }}"></i>
            <span>{{ $createLabel }}</span>
        </a>
    @endif

    {{ $slot }}
</div>
