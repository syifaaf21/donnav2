@extends('layouts.app')

@section('title', 'Dokumen')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&family=DM+Mono:wght@400;500&display=swap');

    :root {
        --bg:        #0f1117;
        --surface:   #181c27;
        --border:    #272d3d;
        --accent:    #4f8ef7;
        --accent-dim:#1e3a6e;
        --text:      #e8eaf0;
        --muted:     #6b7490;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
    }

    .doc-page { max-width: 1200px; margin: 0 auto; padding: 40px 24px 80px; }

    .doc-header {
        display: flex; align-items: flex-end;
        justify-content: space-between; gap: 16px;
        margin-bottom: 36px; flex-wrap: wrap;
    }
    .doc-header__title { font-size: 28px; font-weight: 600; letter-spacing: -0.5px; line-height: 1.1; }
    .doc-header__title span { color: var(--accent); }
    .doc-header__sub { font-size: 13px; color: var(--muted); margin-top: 4px; }

    .search-bar { display: flex; gap: 10px; align-items: center; flex: 0 0 auto; width: 320px; }
    .search-bar__input {
        flex: 1; background: var(--surface); border: 1px solid var(--border);
        border-radius: 10px; padding: 10px 14px 10px 38px;
        color: var(--text); font-family: 'DM Sans', sans-serif; font-size: 14px;
        outline: none; transition: border-color .2s;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236b7490' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: 12px center;
    }
    .search-bar__input:focus { border-color: var(--accent); }
    .search-bar__input::placeholder { color: var(--muted); }

    .stats-strip { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
    .stat-chip {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 8px; padding: 8px 14px; font-size: 13px;
        color: var(--muted); display: flex; align-items: center; gap: 6px;
    }
    .stat-chip strong { color: var(--text); font-weight: 600; }

    .doc-table-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
    .doc-table { width: 100%; border-collapse: collapse; }
    .doc-table thead th {
        padding: 14px 18px; text-align: left; font-size: 11px; font-weight: 600;
        text-transform: uppercase; letter-spacing: .08em; color: var(--muted);
        border-bottom: 1px solid var(--border); background: rgba(255,255,255,.02); white-space: nowrap;
    }
    .doc-table tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
    .doc-table tbody tr:last-child { border-bottom: none; }
    .doc-table tbody tr:hover { background: rgba(79,142,247,.06); }
    .doc-table tbody td { padding: 14px 18px; font-size: 14px; vertical-align: middle; }

    .file-cell { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .file-icon {
        width: 36px; height: 36px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-family: 'DM Mono', monospace; font-weight: 500;
        flex-shrink: 0; letter-spacing: .02em;
    }
    .file-icon--word       { background: rgba(43,124,211,.18); color: #4fa3e8; }
    .file-icon--excel      { background: rgba(32,114,69,.18);  color: #4caf7d; }
    .file-icon--powerpoint { background: rgba(196,62,28,.18);  color: #e57f5c; }
    .file-icon--pdf        { background: rgba(228,76,58,.18);  color: #e57c70; }
    .file-icon--image      { background: rgba(155,89,182,.18); color: #c07be0; }
    .file-icon--other      { background: rgba(93,107,138,.18); color: #8a96b3; }

    .file-name { font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 360px; }
    .file-path { font-size: 11.5px; color: var(--muted); font-family: 'DM Mono', monospace; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 360px; }

    .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 20px; font-size: 11.5px; font-weight: 500; }
    .badge--active   { background: rgba(39,174,96,.15);  color: #4ccc80; }
    .badge--pending  { background: rgba(245,166,35,.15); color: #f5c842; }
    .badge--inactive { background: rgba(93,107,138,.15); color: #8a96b3; }
    .badge__dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

    .date-cell { color: var(--muted); font-size: 13px; white-space: nowrap; }

    /* Tombol buka editor */
    .btn-open {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px; background: var(--accent-dim); color: var(--accent);
        border: 1px solid rgba(79,142,247,.3); border-radius: 8px;
        font-size: 13px; font-weight: 500; font-family: 'DM Sans', sans-serif;
        cursor: pointer; text-decoration: none;
        transition: background .2s, border-color .2s; white-space: nowrap;
    }
    .btn-open:hover { background: rgba(79,142,247,.3); border-color: var(--accent); color: #fff; }
    .btn-open:disabled { opacity: .5; cursor: not-allowed; }
    .btn-open.loading svg { animation: spin .8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    .empty-state { text-align: center; padding: 80px 24px; color: var(--muted); }
    .empty-state__icon { font-size: 48px; margin-bottom: 16px; opacity: .4; }
    .empty-state__title { font-size: 18px; font-weight: 500; color: var(--text); margin-bottom: 8px; }

    .pagination-wrap { display: flex; align-items: center; justify-content: space-between; margin-top: 24px; flex-wrap: wrap; gap: 12px; }
    .pagination-info { font-size: 13px; color: var(--muted); }
    .pagination-links { display: flex; gap: 6px; }
    .pagination-links a, .pagination-links span {
        display: inline-flex; align-items: center; justify-content: center;
        width: 34px; height: 34px; border-radius: 8px; font-size: 13px; font-weight: 500;
        text-decoration: none; transition: background .15s, color .15s; border: 1px solid transparent;
    }
    .pagination-links a { color: var(--muted); border-color: var(--border); background: var(--surface); }
    .pagination-links a:hover { background: var(--accent-dim); color: var(--accent); border-color: var(--accent); }
    .pagination-links span.active { background: var(--accent); color: #fff; border-color: var(--accent); }
    .pagination-links span.disabled { color: var(--border); background: var(--surface); border-color: var(--border); opacity: .5; }

    /* Loading overlay */
    .open-overlay {
        display: none; position: fixed; inset: 0; z-index: 9999;
        background: rgba(15,17,23,.85); backdrop-filter: blur(4px);
        align-items: center; justify-content: center; flex-direction: column; gap: 16px;
    }
    .open-overlay.show { display: flex; }
    .open-overlay__spinner {
        width: 40px; height: 40px;
        border: 3px solid #272d3d; border-top-color: #4f8ef7;
        border-radius: 50%; animation: spin .8s linear infinite;
    }
    .open-overlay__text { font-size: 14px; color: #e8eaf0; }
</style>
@endpush

@section('content')
<div class="doc-page">

    <div class="doc-header">
        <div>
            <div class="doc-header__title">Dokumen <span>Files</span></div>
            <div class="doc-header__sub">Kelola dan buka dokumen dari database</div>
        </div>
        <form method="GET" action="{{ route('editor.index') }}" class="search-bar">
            <input type="text" name="search" class="search-bar__input"
                placeholder="Cari nama file..." value="{{ $search ?? '' }}" autocomplete="off">
        </form>
    </div>

    <div class="stats-strip">
        <div class="stat-chip">Total &nbsp;<strong>{{ $files->total() }}</strong>&nbsp; file</div>
        @if($search)
        <div class="stat-chip">Hasil pencarian: &nbsp;<strong>"{{ $search }}"</strong></div>
        @endif
    </div>

    <div class="doc-table-wrap">
        @if($files->count() > 0)
        <table class="doc-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama File</th>
                    <th>Status</th>
                    <th>Approval</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($files as $file)
                <tr>
                    <td style="color:var(--muted);font-size:13px;font-family:'DM Mono',monospace;">
                        {{ $loop->iteration + ($files->currentPage() - 1) * $files->perPage() }}
                    </td>
                    <td>
                        <div class="file-cell">
                            <div class="file-icon file-icon--{{ $file->file_type }}">
                                {{ strtoupper($file->extension ?: '—') }}
                            </div>
                            <div>
                                <div class="file-name">{{ $file->display_name }}</div>
                                <div class="file-path">{{ $file->file_path }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($file->is_active && !$file->marked_for_deletion_at)
                            <span class="badge badge--active"><span class="badge__dot"></span> Aktif</span>
                        @elseif($file->marked_for_deletion_at)
                            <span class="badge badge--inactive"><span class="badge__dot"></span> Dihapus</span>
                        @else
                            <span class="badge badge--inactive"><span class="badge__dot"></span> Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        @if($file->pending_approval)
                            <span class="badge badge--pending"><span class="badge__dot"></span> Pending</span>
                        @else
                            <span class="badge badge--active"><span class="badge__dot"></span> Approved</span>
                        @endif
                    </td>
                    <td class="date-cell">
                        {{ $file->created_at ? $file->created_at->format('d M Y') : '—' }}
                    </td>
                    <td>
                        @if($file->docspace_file_id)
                            {{-- File sudah di DocSpace: langsung buka editor --}}
                            <button
                                class="btn-open"
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
                            {{-- Belum di DocSpace: arahkan ke halaman detail untuk upload --}}
                            <a href="{{ route('editor.show', $file->id) }}" class="btn-open">
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
        <div class="empty-state">
            <div class="empty-state__icon">📂</div>
            <div class="empty-state__title">
                {{ $search ? 'Tidak ada file yang cocok' : 'Belum ada file' }}
            </div>
            <p style="font-size:14px;">
                {{ $search ? 'Coba kata kunci lain.' : 'Belum ada dokumen di database.' }}
            </p>
        </div>
        @endif
    </div>

    @if($files->hasPages())
    <div class="pagination-wrap">
        <div class="pagination-info">
            Menampilkan {{ $files->firstItem() }}–{{ $files->lastItem() }} dari {{ $files->total() }} file
        </div>
        <div class="pagination-links">
            @if($files->onFirstPage())
                <span class="disabled">‹</span>
            @else
                <a href="{{ $files->previousPageUrl() }}">‹</a>
            @endif
            @foreach($files->getUrlRange(max(1, $files->currentPage()-2), min($files->lastPage(), $files->currentPage()+2)) as $page => $url)
                @if($page == $files->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
            @if($files->hasMorePages())
                <a href="{{ $files->nextPageUrl() }}">›</a>
            @else
                <span class="disabled">›</span>
            @endif
        </div>
    </div>
    @endif

</div>

{{-- Loading overlay --}}
<div class="open-overlay" id="openOverlay">
    <div class="open-overlay__spinner"></div>
    <div class="open-overlay__text" id="overlayText">Membuka editor...</div>
</div>

@endsection

@push('scripts')
<script>
    const DOCSPACE_URL = @json(rtrim(config('onlyoffice.docspace_url') ?? 'https://docspace-2zlw5p.onlyoffice.com', '/'));
    const TOKEN_URL    = @json(route('editor.token'));

    async function openEditor(btn, fileId) {
        // Tampilkan overlay loading
        const overlay = document.getElementById('openOverlay');
        const overlayText = document.getElementById('overlayText');
        overlay.classList.add('show');
        overlayText.textContent = 'Mengautentikasi...';

        try {
            // 1. Ambil token dari Laravel
            const res = await fetch(TOKEN_URL, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept': 'application/json',
                }
            });
            const { token } = await res.json();

            // 2. Set cookie via iframe login
            overlayText.textContent = 'Menyiapkan sesi...';
            if (token) {
                await loginViaIframe(DOCSPACE_URL + '/login?token=' + encodeURIComponent(token));
            }

            // 3. Buka editor di tab baru
            overlayText.textContent = 'Membuka editor...';
            const editorUrl = DOCSPACE_URL + '/doceditor?fileId=' + fileId + '&editorType=desktop&editorGoBack=false';
            window.open(editorUrl, '_blank');

        } catch (e) {
            console.error(e);
            alert('Gagal membuka editor: ' + e.message);
        } finally {
            overlay.classList.remove('show');
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
