@extends('layouts.app')

@section('title', 'Dokumen')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&family=DM+Mono:wght@400;500&display=swap');

    body { font-family: 'DM Sans', sans-serif; }
    .font-mono-dm { font-family: 'DM Mono', monospace; }

    .search-input {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236b7490' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 12px center;
        padding-left: 38px;
    }

    @keyframes spin { to { transform: rotate(360deg); } }
    .animate-spin-custom { animation: spin .8s linear infinite; }

    .btn-open-loading svg { animation: spin .8s linear infinite; }

    tr:hover td { background-color: rgba(79,142,247,0.06); }
</style>
@endpush

@section('content')
<div class="max-w-[1200px] mx-auto px-6 pt-10 pb-20">

    {{-- Header --}}
    <div class="flex items-end justify-between gap-4 mb-9 flex-wrap">
        <div>
            <h1 class="text-[28px] font-semibold tracking-tight leading-tight">
                Dokumen <span class="text-[#4f8ef7]">Files</span>
            </h1>
            <p class="text-[13px] text-[#6b7490] mt-1">Kelola dan buka dokumen dari database</p>
        </div>
        <form method="GET" action="{{ route('editor.index') }}" class="flex gap-2.5 items-center w-80">
            <input
                type="text"
                name="search"
                class="search-input flex-1 bg-[#181c27] border border-[#272d3d] rounded-[10px] py-2.5 pr-3.5 text-[#e8eaf0] font-[DM_Sans,sans-serif] text-sm outline-none transition-colors duration-200 placeholder-[#6b7490] focus:border-[#4f8ef7]"
                placeholder="Cari nama file..."
                value="{{ $search ?? '' }}"
                autocomplete="off"
            >
        </form>
    </div>

    {{-- Stats Strip --}}
    <div class="flex gap-3 mb-6 flex-wrap">
        <div class="bg-[#181c27] border border-[#272d3d] rounded-lg px-3.5 py-2 text-[13px] text-[#6b7490] flex items-center gap-1.5">
            Total &nbsp;<strong class="text-[#e8eaf0] font-semibold">{{ $files->total() }}</strong>&nbsp; file
        </div>
        @if($search)
        <div class="bg-[#181c27] border border-[#272d3d] rounded-lg px-3.5 py-2 text-[13px] text-[#6b7490] flex items-center gap-1.5">
            Hasil pencarian: &nbsp;<strong class="text-[#e8eaf0] font-semibold">"{{ $search }}"</strong>
        </div>
        @endif
    </div>

    {{-- Table --}}
    <div class="bg-[#181c27] border border-[#272d3d] rounded-[14px] overflow-hidden">
        @if($files->count() > 0)
        <table class="w-full border-collapse">
            <thead>
                <tr>
                    <th class="px-[18px] py-3.5 text-left text-[11px] font-semibold uppercase tracking-[.08em] text-[#6b7490] border-b border-[#272d3d] bg-white/[.02] whitespace-nowrap">#</th>
                    <th class="px-[18px] py-3.5 text-left text-[11px] font-semibold uppercase tracking-[.08em] text-[#6b7490] border-b border-[#272d3d] bg-white/[.02] whitespace-nowrap">Nama File</th>
                    <th class="px-[18px] py-3.5 text-left text-[11px] font-semibold uppercase tracking-[.08em] text-[#6b7490] border-b border-[#272d3d] bg-white/[.02] whitespace-nowrap">Status</th>
                    <th class="px-[18px] py-3.5 text-left text-[11px] font-semibold uppercase tracking-[.08em] text-[#6b7490] border-b border-[#272d3d] bg-white/[.02] whitespace-nowrap">Approval</th>
                    <th class="px-[18px] py-3.5 text-left text-[11px] font-semibold uppercase tracking-[.08em] text-[#6b7490] border-b border-[#272d3d] bg-white/[.02] whitespace-nowrap">Dibuat</th>
                    <th class="px-[18px] py-3.5 text-left text-[11px] font-semibold uppercase tracking-[.08em] text-[#6b7490] border-b border-[#272d3d] bg-white/[.02] whitespace-nowrap">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($files as $file)
                <tr class="border-b border-[#272d3d] last:border-b-0 transition-colors duration-150 cursor-default">
                    <td class="px-[18px] py-3.5 text-[13px] align-middle text-[#6b7490] font-mono-dm">
                        {{ $loop->iteration + ($files->currentPage() - 1) * $files->perPage() }}
                    </td>
                    <td class="px-[18px] py-3.5 text-sm align-middle">
                        <div class="flex items-center gap-3 min-w-0">
                            {{-- File Icon --}}
                            @php
                                $iconClasses = match($file->file_type) {
                                    'word'       => 'bg-[rgba(43,124,211,0.18)] text-[#4fa3e8]',
                                    'excel'      => 'bg-[rgba(32,114,69,0.18)] text-[#4caf7d]',
                                    'powerpoint' => 'bg-[rgba(196,62,28,0.18)] text-[#e57f5c]',
                                    'pdf'        => 'bg-[rgba(228,76,58,0.18)] text-[#e57c70]',
                                    'image'      => 'bg-[rgba(155,89,182,0.18)] text-[#c07be0]',
                                    default      => 'bg-[rgba(93,107,138,0.18)] text-[#8a96b3]',
                                };
                            @endphp
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center text-[11px] font-mono-dm font-medium flex-shrink-0 tracking-[.02em] {{ $iconClasses }}">
                                {{ strtoupper($file->extension ?: '—') }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-medium overflow-hidden text-ellipsis whitespace-nowrap max-w-[360px] text-[#e8eaf0]">{{ $file->display_name }}</div>
                                <div class="text-[11.5px] text-[#6b7490] font-mono-dm mt-0.5 overflow-hidden text-ellipsis whitespace-nowrap max-w-[360px]">{{ $file->file_path }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-[18px] py-3.5 text-sm align-middle">
                        @if($file->is_active && !$file->marked_for_deletion_at)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11.5px] font-medium bg-[rgba(39,174,96,0.15)] text-[#4ccc80]">
                                <span class="w-[5px] h-[5px] rounded-full bg-current"></span> Aktif
                            </span>
                        @elseif($file->marked_for_deletion_at)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11.5px] font-medium bg-[rgba(93,107,138,0.15)] text-[#8a96b3]">
                                <span class="w-[5px] h-[5px] rounded-full bg-current"></span> Dihapus
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11.5px] font-medium bg-[rgba(93,107,138,0.15)] text-[#8a96b3]">
                                <span class="w-[5px] h-[5px] rounded-full bg-current"></span> Nonaktif
                            </span>
                        @endif
                    </td>
                    <td class="px-[18px] py-3.5 text-sm align-middle">
                        @if($file->pending_approval)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11.5px] font-medium bg-[rgba(245,166,35,0.15)] text-[#f5c842]">
                                <span class="w-[5px] h-[5px] rounded-full bg-current"></span> Pending
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11.5px] font-medium bg-[rgba(39,174,96,0.15)] text-[#4ccc80]">
                                <span class="w-[5px] h-[5px] rounded-full bg-current"></span> Approved
                            </span>
                        @endif
                    </td>
                    <td class="px-[18px] py-3.5 align-middle text-[#6b7490] text-[13px] whitespace-nowrap">
                        {{ $file->created_at ? $file->created_at->format('d M Y') : '—' }}
                    </td>
                    <td class="px-[18px] py-3.5 align-middle">
                        @if($file->docspace_file_id)
                            <button
                                class="inline-flex items-center gap-1.5 px-3.5 py-[7px] bg-[#1e3a6e] text-[#4f8ef7] border border-[rgba(79,142,247,0.3)] rounded-lg text-[13px] font-medium cursor-pointer whitespace-nowrap transition-all duration-200 hover:bg-[rgba(79,142,247,0.3)] hover:border-[#4f8ef7] hover:text-white"
                                onclick="openEditor(this, '{{ $file->docspace_file_id }}')"
                            >
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                    <polyline points="15 3 21 3 21 9"/>
                                    <line x1="10" y1="14" x2="21" y2="3"/>
                                </svg>
                                Buka
                            </button>
                        @else
                            <a
                                href="{{ route('editor.show', $file->id) }}"
                                class="inline-flex items-center gap-1.5 px-3.5 py-[7px] bg-[#1e3a6e] text-[#4f8ef7] border border-[rgba(79,142,247,0.3)] rounded-lg text-[13px] font-medium whitespace-nowrap transition-all duration-200 hover:bg-[rgba(79,142,247,0.3)] hover:border-[#4f8ef7] hover:text-white no-underline"
                            >
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="16 16 12 12 8 16"/>
                                    <line x1="12" y1="12" x2="12" y2="21"/>
                                    <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                                </svg>
                                Upload
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-center py-20 px-6 text-[#6b7490]">
            <div class="text-5xl mb-4 opacity-40">📂</div>
            <div class="text-lg font-medium text-[#e8eaf0] mb-2">
                {{ $search ? 'Tidak ada file yang cocok' : 'Belum ada file' }}
            </div>
            <p class="text-sm">
                {{ $search ? 'Coba kata kunci lain.' : 'Belum ada dokumen di database.' }}
            </p>
        </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if($files->hasPages())
    <div class="flex items-center justify-between mt-6 flex-wrap gap-3">
        <div class="text-[13px] text-[#6b7490]">
            Menampilkan {{ $files->firstItem() }}–{{ $files->lastItem() }} dari {{ $files->total() }} file
        </div>
        <div class="flex gap-1.5">
            @if($files->onFirstPage())
                <span class="inline-flex items-center justify-center w-[34px] h-[34px] rounded-lg text-[13px] font-medium border border-[#272d3d] bg-[#181c27] text-[#272d3d] opacity-50">‹</span>
            @else
                <a href="{{ $files->previousPageUrl() }}" class="inline-flex items-center justify-center w-[34px] h-[34px] rounded-lg text-[13px] font-medium no-underline border border-[#272d3d] bg-[#181c27] text-[#6b7490] transition-all duration-150 hover:bg-[#1e3a6e] hover:text-[#4f8ef7] hover:border-[#4f8ef7]">‹</a>
            @endif

            @foreach($files->getUrlRange(max(1, $files->currentPage()-2), min($files->lastPage(), $files->currentPage()+2)) as $page => $url)
                @if($page == $files->currentPage())
                    <span class="inline-flex items-center justify-center w-[34px] h-[34px] rounded-lg text-[13px] font-medium bg-[#4f8ef7] text-white border border-[#4f8ef7]">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="inline-flex items-center justify-center w-[34px] h-[34px] rounded-lg text-[13px] font-medium no-underline border border-[#272d3d] bg-[#181c27] text-[#6b7490] transition-all duration-150 hover:bg-[#1e3a6e] hover:text-[#4f8ef7] hover:border-[#4f8ef7]">{{ $page }}</a>
                @endif
            @endforeach

            @if($files->hasMorePages())
                <a href="{{ $files->nextPageUrl() }}" class="inline-flex items-center justify-center w-[34px] h-[34px] rounded-lg text-[13px] font-medium no-underline border border-[#272d3d] bg-[#181c27] text-[#6b7490] transition-all duration-150 hover:bg-[#1e3a6e] hover:text-[#4f8ef7] hover:border-[#4f8ef7]">›</a>
            @else
                <span class="inline-flex items-center justify-center w-[34px] h-[34px] rounded-lg text-[13px] font-medium border border-[#272d3d] bg-[#181c27] text-[#272d3d] opacity-50">›</span>
            @endif
        </div>
    </div>
    @endif

</div>

{{-- Loading Overlay --}}
<div
    class="hidden fixed inset-0 z-[9999] bg-[rgba(15,17,23,0.85)] backdrop-blur-sm items-center justify-center flex-col gap-4"
    id="openOverlay"
    style="display:none;"
>
    <div class="w-10 h-10 border-[3px] border-[#272d3d] border-t-[#4f8ef7] rounded-full animate-spin-custom"></div>
    <div class="text-sm text-[#e8eaf0]" id="overlayText">Membuka editor...</div>
</div>

@endsection

@push('scripts')
<script>
    const DOCSPACE_URL = @json(rtrim(config('onlyoffice.docspace_url') ?? 'https://docspace-2zlw5p.onlyoffice.com', '/'));
    const TOKEN_URL    = @json(route('editor.token'));

    async function openEditor(btn, fileId) {
        const overlay = document.getElementById('openOverlay');
        const overlayText = document.getElementById('overlayText');
        overlay.style.display = 'flex';
        overlayText.textContent = 'Mengautentikasi...';

        try {
            const res = await fetch(TOKEN_URL, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept': 'application/json',
                }
            });
            const { token } = await res.json();

            overlayText.textContent = 'Menyiapkan sesi...';
            if (token) {
                await loginViaIframe(DOCSPACE_URL + '/login?token=' + encodeURIComponent(token));
            }

            overlayText.textContent = 'Membuka editor...';
            const editorUrl = DOCSPACE_URL + '/doceditor?fileId=' + fileId + '&editorType=desktop&editorGoBack=false';
            window.open(editorUrl, '_blank');

        } catch (e) {
            console.error(e);
            alert('Gagal membuka editor: ' + e.message);
        } finally {
            overlay.style.display = 'none';
        }
    }

    function loginViaIframe(url) {
        return new Promise(resolve => {
            const frame = document.createElement('iframe');
            frame.src = url;
            frame.style.cssText = 'position:fixed;width:1px;height:1px;opacity:0;pointer-events:none;';
            const timer = setTimeout(resolve, 3000);
            frame.onload = () => { clearTimeout(timer); setTimeout(resolve, 400); };
            document.body.appendChild(frame);
            setTimeout(() => frame.remove(), 4500);
        });
    }
</script>
@endpush
