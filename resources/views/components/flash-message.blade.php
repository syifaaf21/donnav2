 @if (session('success'))
        <div id="flashMessage"
            class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow" role="alert"
            style="z-index: 1050; min-width: 250px;">
            {{ session('success') }}
            {{-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> --}}
        </div>
    @endif

    @if (session('error'))
        <div id="flashMessage"
            class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow" role="alert"
            style="z-index: 1050; min-width: 250px;">
            {{ session('error') }}
            {{-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> --}}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
