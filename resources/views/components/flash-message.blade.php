@if (session('success'))
    <div class="flashMessage alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow"
        role="alert" style="z-index: 1050; min-width: 250px;">
        {{ session('success') }}
        <!-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> -->

    </div>
@endif

@if (session('error'))
    <div class="flashMessage alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow"
        role="alert" style="z-index: 1050; min-width: 250px;">
        {{ session('error') }}
        <!-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> -->
    </div>
@endif

@if ($errors->any())
    <div class="flashMessage alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow"
        role="alert" style="z-index: 1050; min-width: 300px;">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<script>
    // Auto-hide semua flash messages setelah 5 detik
    setTimeout(() => {
        document.querySelectorAll('.flashMessage').forEach(flash => {
            let bsAlert = new bootstrap.Alert(flash);
            bsAlert.close();
        });
    }, 5000);
</script>

