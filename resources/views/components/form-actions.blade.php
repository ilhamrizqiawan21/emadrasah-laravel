@props([
    'backUrl',
    'submitLabel' => 'Simpan',
    'submitIcon' => 'fa-save',
    'confirm' => null,
])

<div class="em-form-actions">
    <a href="{{ $backUrl }}" class="btn btn-light border">
        Batal
    </a>
    <button type="submit" class="btn btn-primary" @if ($confirm) data-confirm="{{ $confirm }}" @endif>
        <i class="fas {{ $submitIcon }}"></i>
        <span>{{ $submitLabel }}</span>
    </button>
</div>
