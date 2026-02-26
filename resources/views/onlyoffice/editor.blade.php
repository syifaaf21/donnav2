@extends('layouts.app')

@section('title', $file->display_name)

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400&display=swap');

    :root {
        --bg:      #0f1117;
        --surface: #181c27;
        --border:  #272d3d;
        --accent:  #4f8ef7;
        --text:    #e8eaf0;
        --muted:   #6b7490;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
    }

    .page { max-width: 720px; margin: 0 auto; padding: 48px 24px 80px; }

    .back-link {
        display: inline-flex; align-items: center; gap: 6px;
        color: var(--muted); font-size: 13px; font-weight: 500;
        text-decoration: none; margin-bottom: 32px;
        transition: color .15s;
    }
    .back-link:hover { color: var(--text); }

    /* ── FILE CARD ── */
    .file-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
    }
    .file-card__hero {
        background: linear-gradient(135deg, #1a2035 0%, #1e2d50 100%);
        border-bottom: 1px solid var(--border);
        padding: 32px 28px;
        display: flex; align-items: center; gap: 18px;
    }
    .file-icon {
        width: 52px; height: 52px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-family: 'DM Mono', monospace; font-weight: 500;
        flex-shrink: 0;
        background: rgba(79,142,247,.2); color: var(--accent);
        border: 1px solid rgba(79,142,247,.3);
    }
    .file-name {
        font-size: 18px; font-weight: 600; margin-bottom: 5px;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .file-path {
        font-size: 12px; color: var(--muted);
        font-family: 'DM Mono', monospace;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }

    /* ── META ── */
    .file-meta {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 0;
        border-bottom: 1px solid var(--border);
    }
    .meta-item {
        padding: 18px 28px;
        border-right: 1px solid var(--border);
    }
    .meta-item:last-child { border-right: none; }
    .meta-label {
        font-size: 11px; font-weight: 600; text-transform: uppercase;
        letter-spacing: .08em; color: var(--muted); margin-bottom: 6px;
    }
    .meta-value { font-size: 14px; font-weight: 500; }
    .meta-value.mono { font-family: 'DM Mono', monospace; font-size: 12px; color: var(--muted); }

    .badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 20px;
        font-size: 12px; font-weight: 500;
    }
    .badge--active  { background: rgba(39,174,96,.15);  color: #4ccc80; }
    .badge--pending { background: rgba(245,166,35,.15); color: #f5c842; }
    .badge--ok      { background: rgba(79,142,247,.15); color: #7ab3f7; }
    .badge__dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

    /* ── NOTICE ── */
    .notice {
        margin: 20px 28px 0;
        padding: 12px 16px; border-radius: 10px;
        font-size: 13px; line-height: 1.6;
        display: flex; gap: 10px; align-items: flex-start;
        background: rgba(79,142,247,.08);
        border: 1px solid rgba(79,142,247,.18);
        color: #94b8f7;
    }

    /* ── ACTIONS ── */
    .file-actions {
        padding: 24px 28px;
        display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
    }

    .btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 20px; border-radius: 10px;
        font-size: 14px; font-weight: 500;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer; text-decoration: none;
        border: 1px solid transparent;
        transition: all .2s; white-space: nowrap;
    }
    .btn--primary {
        background: var(--accent); color: #fff; border-color: var(--accent);
        font-size: 15px; padding: 12px 24px;
    }
    .btn--primary:hover { background: #3a7de8; }
    .btn--secondary {
        background: rgba(79,142,247,.1); color: var(--accent);
        border-color: rgba(79,142,247,.25);
    }
    .btn--secondary:hover { background: rgba(79,142,247,.2); border-color: var(--accent); }
    .btn--ghost {
        background: transparent; color: var(--muted); border-color: var(--border);
    }
    .btn--ghost:hover { background: rgba(255,255,255,.05); color: var(--text); }
    .btn:disabled { opacity: .5; cursor: not-allowed; pointer-events: none; }
    .btn.loading svg { animation: spin .8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── TOAST ── */
    .toast {
        position: fixed; bottom: 24px; right: 24px; z-index: 999;
        padding: 12px 18px; border-radius: 10px;
        font-size: 13px; font-weight: 500;
        display: flex; align-items: center; gap: 8px;
        opacity: 0; transform: translateY(8px);
        transition: opacity .25s, transform .25s; pointer-events: none;
    }
    .toast.show { opacity: 1; transform: translateY(0); }
    .toast--success { background: rgba(39,174,96,.95); color: #fff; }
    .toast--error   { background: rgba(224,82,82,.95); color: #fff; }
</style>
@endpush

@section('content')
<div class="page">

    <a href="{{ route('editor.index') }}" class="back-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
        Kembali ke Daftar File
    </a>

    <div class="file-card">

        {{-- HERO --}}
        <div class="file-card__hero">
            <div class="file-icon">{{ strtoupper($file->extension ?: '—') }}</div>
            <div style="min-width:0;flex:1">
                <div class="file-name" title="{{ $file->display_name }}">{{ $file->display_name }}</div>
                <div class="file-path">{{ $file->file_path }}</div>
            </div>
        </div>

        {{-- META --}}
        <div class="file-meta">
            <div class="meta-item">
                <div class="meta-label">Status</div>
                <div class="meta-value">
                    @if($file->is_active && !$file->marked_for_deletion_at)
                        <span class="badge badge--active"><span class="badge__dot"></span> Aktif</span>
                    @else
                        <span class="badge" style="background:rgba(93,107,138,.15);color:#8a96b3"><span class="badge__dot"></span> Nonaktif</span>
                    @endif
                </div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Approval</div>
                <div class="meta-value">
                    @if($file->pending_approval)
                        <span class="badge badge--pending"><span class="badge__dot"></span> Pending</span>
                    @else
                        <span class="badge badge--active"><span class="badge__dot"></span> Approved</span>
                    @endif
                </div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Dibuat</div>
                <div class="meta-value" style="font-size:13px">
                    {{ $file->created_at?->format('d M Y') ?? '—' }}
                </div>
            </div>
            <div class="meta-item">
                <div class="meta-label">DocSpace ID</div>
                <div class="meta-value mono">{{ $file->docspace_file_id ?? 'Belum diupload' }}</div>
            </div>
        </div>

        {{-- NOTICE --}}
        <div class="notice">
            <span style="flex-shrink:0">ℹ️</span>
            <span>
                Editor akan terbuka di <strong>tab baru</strong>. Setelah selesai mengedit dan menyimpan di DocSpace,
                kembali ke halaman ini lalu klik <strong>Simpan ke Laravel</strong> untuk menyinkronkan perubahan ke server.
            </span>
        </div>

        {{-- ACTIONS --}}
        <div class="file-actions">

            @if($file->docspace_file_id)
                {{-- Buka editor: login dulu via popup, lalu buka doceditor --}}
                <button class="btn btn--primary" id="btnOpen" onclick="openEditor()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    Buka Editor
                </button>

                {{-- Sync ke Laravel --}}
                <button class="btn btn--secondary" id="btnSync" onclick="syncFile()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="23 4 23 10 17 10"/>
                        <polyline points="1 20 1 14 7 14"/>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                    </svg>
                    Simpan ke Laravel
                </button>

                {{-- Re-upload --}}
                <button class="btn btn--ghost" id="btnReupload" onclick="reuploadFile()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="16 16 12 12 8 16"/>
                        <line x1="12" y1="12" x2="12" y2="21"/>
                        <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                    </svg>
                    Re-upload
                </button>

            @else
                {{-- Belum ada di DocSpace: upload dulu --}}
                <button class="btn btn--primary" id="btnUpload" onclick="uploadAndOpen()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="16 16 12 12 8 16"/>
                        <line x1="12" y1="12" x2="12" y2="21"/>
                        <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                    </svg>
                    Upload & Buka Editor
                </button>
            @endif

        </div>
    </div>
</div>

<div class="toast" id="toast"></div>
@endsection

@push('scripts')
<script>
    const LOGIN_URL    = @json($loginUrl);
    const EDITOR_URL   = @json($docEditorUrl);

    // Login via iframe tersembunyi, lalu buka doceditor di tab baru
    async function openEditor() {
        const btn = document.getElementById('btnOpen');
        btn.disabled = true;
        btn.classList.add('loading');
        btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Membuka...`;

        // Jika ada loginUrl, set cookie dulu via iframe
        if (LOGIN_URL) {
            await new Promise(resolve => {
                const frame = document.createElement('iframe');
                frame.src = LOGIN_URL;
                frame.style.cssText = 'position:fixed;width:1px;height:1px;opacity:0;pointer-events:none;';
                const timer = setTimeout(resolve, 3000); // max tunggu 3 detik
                frame.onload = () => { clearTimeout(timer); setTimeout(resolve, 300); };
                document.body.appendChild(frame);
                // Hapus frame setelah selesai
                setTimeout(() => frame.remove(), 4000);
            });
        }

        // Buka editor di tab baru
        window.open(EDITOR_URL, '_blank');

        // Reset tombol
        btn.disabled = false;
        btn.classList.remove('loading');
        btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg> Buka Editor`;
    }
    async function syncFile() {
        const btn = document.getElementById('btnSync');
        btn.disabled = true;
        btn.classList.add('loading');
        btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Menyimpan...`;

        try {
            const res  = await fetch('{{ route("editor.sync", $file->id) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await res.json();
            showToast(data.success ? 'success' : 'error', data.message);
        } catch {
            showToast('error', 'Gagal menghubungi server');
        } finally {
            btn.disabled = false;
            btn.classList.remove('loading');
            btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Simpan ke Laravel`;
        }
    }

    async function reuploadFile() {
        if (!confirm('Re-upload akan menghapus file lama di DocSpace dan mengupload ulang. Lanjutkan?')) return;

        const btn = document.getElementById('btnReupload');
        btn.disabled = true;
        btn.classList.add('loading');
        btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg> Mengupload...`;

        try {
            const res  = await fetch('{{ route("editor.reupload", $file->id) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                showToast('success', 'Re-upload berhasil! Halaman akan direfresh...');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('error', data.message);
            }
        } catch {
            showToast('error', 'Gagal menghubungi server');
        } finally {
            btn.disabled = false;
            btn.classList.remove('loading');
            btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg> Re-upload`;
        }
    }

    async function uploadAndOpen() {
        const btn = document.getElementById('btnUpload');
        btn.disabled = true;
        btn.classList.add('loading');
        btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg> Mengupload...`;

        try {
            const res  = await fetch('{{ route("editor.reupload", $file->id) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                showToast('success', 'Upload berhasil! Halaman akan direfresh...');
                setTimeout(() => location.reload(), 1200);
            } else {
                showToast('error', data.message);
                btn.disabled = false;
                btn.classList.remove('loading');
            }
        } catch {
            showToast('error', 'Gagal menghubungi server');
            btn.disabled = false;
            btn.classList.remove('loading');
        }
    }

    function showToast(type, msg) {
        const t = document.getElementById('toast');
        t.className = `toast toast--${type} show`;
        t.textContent = msg;
        setTimeout(() => t.classList.remove('show'), 3500);
    }
</script>
@endpush
