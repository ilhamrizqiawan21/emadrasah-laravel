@if (session('success'))
    <div class="em-alert em-alert-success alert-dismissible fade show" role="alert">
        <div class="em-alert-icon"><i class="fas fa-circle-check"></i></div>
        <div class="em-alert-content">{{ session('success') }}</div>
        <button type="button" class="btn-close btn-sm ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="em-alert em-alert-danger alert-dismissible fade show" role="alert">
        <div class="em-alert-icon"><i class="fas fa-circle-xmark"></i></div>
        <div class="em-alert-content">{{ session('error') }}</div>
        <button type="button" class="btn-close btn-sm ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="em-alert em-alert-danger alert-dismissible fade show" role="alert">
        <div class="em-alert-icon"><i class="fas fa-triangle-exclamation"></i></div>
        <div class="em-alert-content">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="btn-close btn-sm ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif