@if (session('success') || session('status'))
    <div class="alert alert-success" data-autohide role="status">{{ session('success') ?? session('status') }}</div>
@endif
@if (session('warning'))
    <div class="alert alert-warning" role="alert">{{ session('warning') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-error" role="alert">{{ session('error') }}</div>
@endif
