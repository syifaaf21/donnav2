@extends('layouts.app')

{{-- @section('title', $file->display_name) --}}
@section('title','Edit File Online')
@section('subtitle', 'Review and manage documents hierarchy.')

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
        display: inline-flex; align-items: center; gap: 8px;
        color: var(--text); font-size: 13px; font-weight: 600;
        text-decoration: none; margin-bottom: 32px;
        transition: color .15s, background .15s, transform .08s;
        background: rgba(255,255,255,0.04);
        padding: 8px 10px; border-radius: 8px; border: 1px solid rgba(15,23,42,0.04);
        box-shadow: 0 6px 18px rgba(2,6,23,0.02);
        z-index: 5;
    }
    .back-link:hover { color: var(--text); transform: translateY(-1px); background: rgba(255,255,255,0.06); }

    /* ── FILE CARD ── */
    .file-card {
        background: #ffffff;
        border: 1px solid #e6e9ef;
        border-radius: 16px;
        overflow: hidden;
        color: #0f172a;
        max-width: 820px;
        margin: 0 auto;
    }
    .file-card__hero {
        background: #f8fafc;
        border-bottom: 1px solid #e6e9ef;
        padding: 28px 24px;
        display: flex; align-items: center; gap: 16px;
    }
    .file-icon {
        width: 52px; height: 52px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-family: 'DM Mono', monospace; font-weight: 600;
        flex-shrink: 0;
        background: #eef4ff; color: #1e3a8a;
        border: 1px solid rgba(30,58,138,.08);
    }
    .file-name {
        font-size: 18px; font-weight: 700; margin-bottom: 4px; color: #0f172a;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .file-path {
        font-size: 12px; color: #64748b;
        font-family: 'DM Mono', monospace;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }

    /* ── META ── */
    .file-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(220px, 1fr));
        gap: 0;
        border-bottom: 1px solid #eef2f6;
        align-items: start;
    }
    .meta-item {
        padding: 16px 20px;
        border-right: 1px solid #f1f5f9;
    }
    .meta-item:last-child { border-right: none; }
    .meta-label {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: #64748b; margin-bottom: 6px;
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
        margin: 18px 24px 0;
        padding: 12px 14px; border-radius: 8px;
        font-size: 13px; line-height: 1.6;
        display: flex; gap: 10px; align-items: flex-start;
        background: #eef6ff;
        border: 1px solid #e6f0ff;
        color: #0f172a;
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

    /* Notes modal: light card style matching other app modals */
    .notes-modal { display: none; position: fixed; inset: 0; z-index: 9998; align-items: center; justify-content: center; }
    .notes-modal.show { display: flex; }
    .notes-modal__backdrop { position: absolute; inset: 0; background: rgba(10,12,20,0.25); backdrop-filter: blur(2px); }
    .notes-modal__dialog { position: relative; width: 760px; max-width: calc(100% - 56px); background: #ffffff; color: #1f2937; border: 1px solid rgba(15,23,42,0.06); border-radius: 12px; padding: 18px; box-shadow: 0 12px 30px rgba(16,24,40,0.12); transform: translateY(-6px) scale(.995); opacity: 0; transition: transform .18s ease, opacity .18s ease; }
    .notes-modal.show .notes-modal__dialog { transform: translateY(0) scale(1); opacity: 1; }
    .notes-modal__header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
    .notes-modal__header h3 { margin:0; font-size:18px; color:#0f172a; font-weight:700; }
    .notes-modal__meta { font-size:13px; color:#64748b; margin-top:6px }
    .notes-modal__body { margin-top:12px; }
    .notes-modal__footer { margin-top: 14px; display:flex; justify-content:flex-end; gap:10px; }
    .notes-modal .btn--ghost { background: #f8fafc; border:1px solid rgba(2,6,23,0.04); color: #475569; }
    .notes-modal textarea { width:100%; min-height:140px; resize:vertical; padding:14px; border-radius:8px; border:1px solid #e6e9ef; background: #fbfdff; color:#0f172a; font-size:14px; }
    .notes-modal textarea::placeholder { color: #94a3b8; }
    .notes-close { background:#f1f5f9; border:0; color:#475569; cursor:pointer; padding:6px; border-radius:999px; box-shadow: 0 2px 6px rgba(2,6,23,0.04); }
    .notes-close:hover { background: #eef2f6; color:#0f172a; }
</style>
@endpush

@section('content')
<div class="page">

    @php
        $currentUser = auth()->user();
        $roles = $currentUser?->roles?->pluck('name')->map(fn($v) => strtolower($v))->toArray() ?? [];
        $isAdmin = in_array('admin', $roles, true) || in_array('super admin', $roles, true);
    @endphp
    <a href="{{ $isAdmin ? route('editor.index') : route('document-review.index') }}" class="back-link">
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
                    @php
                        $mapping = $file->mapping;
                        $statusName = strtolower($mapping?->status?->name ?? '');
                        $statusClass = match ($statusName) {
                            'approved' => 'inline-block px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded',
                            'need review' => 'inline-block px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded',
                            'rejected' => 'inline-block px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded',
                            default => 'inline-block px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded',
                        };
                    @endphp
                    <span class="{{ $statusClass }}">{{ ucwords($statusName ?: '-') }}</span>
                </div>
            </div>
            {{-- Approval column hidden as requested --}}
            <div class="meta-item">
                <div class="meta-label">Dibuat</div>
                <div class="meta-value" style="font-size:13px">
                    {{ $file->updated_at?->format('d M Y') ?? '—' }}
                </div>
            </div>
        </div>

        {{-- NOTICE --}}
        <div class="notice">
            <span style="flex-shrink:0">ℹ️</span>
            <span>
                Editor akan terbuka di <strong>tab baru</strong> ketika Anda klik tombol <strong>Buka Editor</strong>. Setelah selesai mengedit dan menyimpan di DocSpace,
                kembali ke halaman ini lalu klik <strong>Simpan ke Laravel</strong> untuk menyinkronkan perubahan ke server.
            </span>
        </div>

        {{-- ACTIONS --}}
        <div class="file-actions">

            @if($file->docspace_file_id)
                {{-- Buka editor: login dulu via popup, lalu buka doceditor --}}
                <button type="button" class="btn btn--primary" id="btnOpen" onclick="openEditor()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    Buka Editor
                </button>

                {{-- Sync ke Laravel --}}
                <button type="button" class="btn btn--secondary" id="btnSync" onclick="syncFile()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="23 4 23 10 17 10"/>
                        <polyline points="1 20 1 14 7 14"/>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                    </svg>
                    Simpan ke Laravel
                </button>

                {{-- Re-upload (only when mapping status != 'need review') --}}
                @php
                    $mapping = $file->mapping;
                    $mappingStatus = strtolower($mapping?->status?->name ?? '');
                @endphp
                @if($mappingStatus !== 'need review')
                    <button type="button" class="btn btn--ghost" id="btnReupload" onclick="reuploadFile()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="16 16 12 12 8 16"/>
                            <line x1="12" y1="12" x2="12" y2="21"/>
                            <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                        </svg>
                        Re-upload
                    </button>
                @endif

            @else
                {{-- Belum ada di DocSpace: upload dulu --}}
                <button type="button" class="btn btn--primary" id="btnUpload" onclick="uploadAndOpen()">
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

<!-- Notes modal -->
<div id="notesModal" class="notes-modal" aria-hidden="true">
    <div class="notes-modal__backdrop"></div>
    <div class="notes-modal__dialog" role="dialog" aria-modal="true">
            <div class="notes-modal__header">
                <div>
                    <h3>Revision Notes</h3>
                    <div>
                    <p class="text-sm text-yellow-800 font-semibold mb-1">Tips!</p>
                    <ul class="text-xs text-yellow-700 leading-relaxed list-disc ms-4">
                        <li>Pengisian notes dilakukan khusus untuk perubahan yang terjadi pada file tersebut secara internal, bukan akibat referensi atau relasi dengan dokumen lain.
                        </li>
                        <li>Jika tidak ada perubahan internal pada file, biarkan notes kosong dan klik tombol "Simpan & Sinkron".</li>
                    </ul>
                </div>
                </div>
                <div style="display:flex;gap:8px;align-items:center">
                    <button id="notesClose" class="notes-close" aria-label="Tutup">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            <div class="notes-modal__body">
                <textarea id="notesTextarea" rows="6" placeholder="Tuliskan catatan revisi..."></textarea>
            </div>
            <span class="notes-modal__meta">Catatan revisi (opsional) — boleh dikosongkan</span>
            <div class="notes-modal__footer">
                <button class="btn btn--ghost" id="notesCancel">Batal</button>
                <button class="btn btn--primary" id="notesSubmit">Simpan & Sinkron</button>
            </div>
    </div>
</div>
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
        // minta revision notes via modal (wajib)
        const notes = await openNotesModal();
        if (notes === null) {
            // user batal
            btn.disabled = false;
            btn.classList.remove('loading');
            btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Simpan ke Laravel`;
            return;
        }

        try {
            const res  = await fetch('{{ route("editor.sync", $file->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ notes })
            });
            const data = await res.json();
            showToast(data.success ? 'success' : 'error', data.message);
            if (data.success) {
                // refresh to show updated data after successful sync
                setTimeout(() => location.reload(), 900);
            }
        } catch (err) {
            console.error(err);
            showToast('error', 'Gagal menghubungi server');
        } finally {
            btn.disabled = false;
            btn.classList.remove('loading');
            btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Simpan ke Laravel`;
        }
    }

    async function reuploadFile() {
        if (!confirm('Pilih file pengganti untuk diupload. Lanjutkan?')) return;

        // create invisible file input
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.pdf,.doc,.docx,.xls,.xlsx';
        input.style.display = 'none';

        input.addEventListener('change', async function () {
            const file = input.files[0];
            if (!file) return;

            const btn = document.getElementById('btnReupload');
            btn.disabled = true;
            btn.classList.add('loading');
            btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg> Mengupload...`;

            try {
                const form = new FormData();
                form.append('replacement_file', file);
                form.append('_token', '{{ csrf_token() }}');

                const res = await fetch('{{ route("editor.reupload", $file->id) }}', {
                    method: 'POST',
                    body: form,
                    headers: { 'Accept': 'application/json' }
                });

                const data = await res.json();
                if (data.success) {
                    showToast('success', 'Re-upload berhasil! Halaman akan direfresh...');
                    setTimeout(() => location.reload(), 1400);
                } else {
                    showToast('error', data.message || 'Re-upload gagal');
                }
            } catch (err) {
                console.error(err);
                showToast('error', 'Gagal menghubungi server');
            } finally {
                btn.disabled = false;
                btn.classList.remove('loading');
                btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg> Re-upload`;
                input.remove();
            }
        });

        document.body.appendChild(input);
        input.click();
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

    function openNotesModal() {
            return new Promise(resolve => {
                const modal = document.getElementById('notesModal');
                const textarea = document.getElementById('notesTextarea');
                const btnCancel = document.getElementById('notesCancel');
                const btnSubmit = document.getElementById('notesSubmit');
                const btnClose = document.getElementById('notesClose');
                const backdrop = modal.querySelector('.notes-modal__backdrop');

                const cleanup = (value) => {
                    modal.classList.remove('show');
                    btnCancel.removeEventListener('click', onCancel);
                    btnSubmit.removeEventListener('click', onSubmit);
                    btnClose.removeEventListener('click', onCancel);
                    backdrop.removeEventListener('click', onCancel);
                    document.removeEventListener('keydown', onKey);
                    resolve(value);
                };

                const onCancel = () => cleanup(null);
                const onSubmit = () => {
                    const v = textarea.value.trim();
                    // notes are optional; allow empty string
                    cleanup(v);
                };
                const onKey = (e) => { if (e.key === 'Escape') onCancel(); };

                textarea.value = '';
                modal.classList.add('show');
                setTimeout(() => textarea.focus(), 80);

                btnCancel.addEventListener('click', onCancel);
                btnSubmit.addEventListener('click', onSubmit);
                btnClose.addEventListener('click', onCancel);
                backdrop.addEventListener('click', onCancel);
                document.addEventListener('keydown', onKey);
            });
    }
</script>
@endpush
