@foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $messageKey => $alertClass)
    @if (session($messageKey))
        <div class="container">
            <div class="page-inner pt-1 pb-0">
                <div class="alert alert-{{ $alertClass }} alert-dismissible fade show mb-1 py-2" role="alert">
                    {{ session($messageKey) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif
@endforeach

@if (isset($errors) && $errors->any())
    <div class="container">
        <div class="page-inner pt-1 pb-0">
            <div class="alert alert-danger alert-dismissible fade show mb-1 py-2" role="alert">
                <strong>{{ $errors->first() }}</strong>
                @if ($errors->count() > 1)
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach ($errors->all() as $error)
                            @continue($loop->first)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    window.setTimeout(function () {
        document.querySelectorAll('.alert.alert-success').forEach(function (alert) {
            if (window.bootstrap && window.bootstrap.Alert) {
                window.bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        });
    }, 5000);
});
</script>