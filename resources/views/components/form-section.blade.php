@props([
    'title',
    'subtitle' => null,
])

<section class="em-form-section">
    <div class="em-form-section__header">
        <h2>{{ $title }}</h2>
        @if ($subtitle)
            <p>{{ $subtitle }}</p>
        @endif
    </div>
    <div class="em-form-section__body">
        {{ $slot }}
    </div>
</section>
