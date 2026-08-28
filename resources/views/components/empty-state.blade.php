@props([
    'title',
    'description' => null,
    'icon' => 'fa-inbox',
    'colspan' => 1,
])

<tr>
    <td colspan="{{ $colspan }}" class="em-empty-cell">
        <div class="em-empty-state">
            <i class="fas {{ $icon }}"></i>
            <strong>{{ $title }}</strong>
            @if ($description)
                <span>{{ $description }}</span>
            @endif
        </div>
    </td>
</tr>
