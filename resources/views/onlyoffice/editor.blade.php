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

    .oo-editor-page,
    .oo-editor-page * {
        box-sizing: border-box;
        font-family: 'DM Sans', sans-serif;
    }

    .oo-editor-page {
        width: 100%;
        max-width: none;
        margin: 0 auto;
        padding: 24px 8px 40px;
        color: var(--text);
    }

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
        width: 100%;
        margin: 0;
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
    .hidden-sync { display: none !important; }

    /* ── TOAST ── */
    .toast {
        position: fixed; top: 24px; right: 24px; z-index: 999;
        padding: 12px 18px; border-radius: 10px;
        font-size: 13px; font-weight: 500;
        display: flex; align-items: center; gap: 8px;
        opacity: 0; transform: translateY(8px);
        transition: opacity .25s, transform .25s; pointer-events: none;
    }
    .toast.show { opacity: 1; transform: translateY(0); }
    .toast--success { background: rgba(39,174,96,.95); color: #fff; }
    .toast--error   { background: rgba(224,82,82,.95); color: #fff; }

    /* Notes modal: modern, cleaner structure */
    .notes-modal { display: none; position: fixed; inset: 0; z-index: 9998; align-items: center; justify-content: center; }
    .notes-modal.show { display: flex; }
    .notes-modal__backdrop { position: absolute; inset: 0; background: rgba(2, 6, 23, 0.45); backdrop-filter: blur(4px); }
    .notes-modal__dialog {
        position: relative;
        width: 860px;
        max-width: calc(100% - 44px);
        max-height: calc(100vh - 48px);
        overflow: hidden;
        background: #ffffff;
        color: #1f2937;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 28px 64px rgba(15, 23, 42, 0.22);
        transform: translateY(-8px) scale(.992);
        opacity: 0;
        transition: transform .18s ease, opacity .18s ease;
    }
    .notes-modal.show .notes-modal__dialog { transform: translateY(0) scale(1); opacity: 1; }
    .notes-modal__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }
    .notes-modal__header h3 { margin: 0; font-size: 20px; color: #0f172a; font-weight: 700; }
    .notes-modal__sub { margin-top: 4px; font-size: 13px; color: #64748b; }
    .notes-modal__content { padding: 16px 20px 14px; max-height: calc(100vh - 210px); overflow-y: auto; }
    .notes-modal__footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 20px 16px;
        border-top: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .notes-modal .btn--ghost { background: #f8fafc; border: 1px solid #dbe4ef; color: #475569; }
    .notes-modal .btn--primary { box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25); }

    .notes-tips {
        border: 1px solid #fde68a;
        background: linear-gradient(180deg, #fffdf4 0%, #fff9e6 100%);
        border-radius: 10px;
        padding: 12px 13px;
    }
    .notes-tips__title { margin: 0 0 6px 0; font-size: 13px; font-weight: 700; color: #92400e; }
    .notes-tips__list { margin: 0; padding-left: 17px; font-size: 12.5px; line-height: 1.55; color: #a16207; }

    .notes-field { margin-top: 14px; }
    .notes-field__label { display: block; margin: 0 0 7px 0; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #475569; }
    .notes-modal textarea {
        width: 100%;
        min-height: 135px;
        resize: vertical;
        padding: 12px 13px;
        border-radius: 10px;
        border: 1px solid #dbe4ef;
        background: #fbfdff;
        color: #0f172a;
        font-size: 14px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .notes-modal textarea:focus { outline: none; border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.14); }
    .notes-modal textarea::placeholder { color: #94a3b8; }
    .notes-modal__meta { font-size: 12px; color: #64748b; margin-top: 6px; }

    .dept-checklist {
        border: 1px solid #dbe4ef;
        border-radius: 10px;
        background: #fbfdff;
        max-height: 186px;
        overflow-y: auto;
        padding: 8px;
    }
    .dept-checklist__item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 10px;
        border-radius: 8px;
        cursor: pointer;
        border: 1px solid transparent;
        transition: background-color .12s ease, border-color .12s ease;
    }
    .dept-checklist__item:hover { background: #eff6ff; border-color: #dbeafe; }
    .dept-checklist__item.is-hidden { display: none; }
    .dept-checklist__item input { width: 15px; height: 15px; accent-color: #2563eb; }
    .dept-checklist__name { color: #1e293b; font-size: 14px; }
    .dept-checklist__empty { color:#64748b; font-size:13px; padding: 10px; }
    .dept-controls {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 0 0 8px 0;
    }
    .dept-controls__left {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .dept-control-btn {
        border: 1px solid #dbe4ef;
        border-radius: 999px;
        padding: 5px 10px;
        background: #fff;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        line-height: 1;
    }
    .dept-control-btn:hover {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .dept-selected-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 12px;
        font-weight: 700;
        color: #1d4ed8;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
    }
    .dept-search {
        width: 100%;
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        padding: 8px 10px;
        font-size: 13px;
        color: #0f172a;
        background: #fff;
        margin-bottom: 8px;
    }
    .dept-search:focus {
        outline: none;
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.14);
    }
    .dept-no-result {
        display: none;
        color: #64748b;
        font-size: 12px;
        padding: 8px 2px;
    }

    .notes-close {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #64748b;
        cursor: pointer;
        padding: 6px;
        border-radius: 999px;
        box-shadow: 0 2px 8px rgba(2, 6, 23, 0.05);
    }
    .notes-close:hover { background: #f8fafc; color: #0f172a; }

    @media (max-width: 768px) {
        .oo-editor-page {
            padding: 16px 12px 28px;
        }

        .file-card__hero {
            padding: 18px 14px;
            gap: 12px;
        }

        .file-name {
            font-size: 16px;
        }

        .file-meta {
            grid-template-columns: 1fr;
        }

        .meta-item {
            border-right: none;
            border-bottom: 1px solid #f1f5f9;
        }

        .meta-item:last-child {
            border-bottom: none;
        }

        .file-actions {
            padding: 16px 14px;
        }

        .notes-modal__dialog {
            max-width: calc(100% - 16px);
            max-height: calc(100vh - 16px);
        }

        .notes-modal__header,
        .notes-modal__content,
        .notes-modal__footer {
            padding-left: 14px;
            padding-right: 14px;
        }

        .notes-modal__header h3 {
            font-size: 18px;
        }
    }
</style>
@endpush

@section('content')
<div class="oo-editor-page">

    @php
        $currentUser = auth()->user();
        $roles = $currentUser?->roles?->pluck('name')->map(fn($v) => strtolower($v))->toArray() ?? [];
        $isAdmin = in_array('admin', $roles, true) || in_array('super admin', $roles, true);
        $isSupervisor = in_array('supervisor', $roles, true);
        $isDeptHead = in_array('dept head', $roles, true) || in_array('department head', $roles, true);
        $hideSyncButton = $isSupervisor || $isDeptHead;
        $syncDepartments = $allDepartments ?? collect();
    @endphp
    <a href="{{ $isAdmin ? route('editor.index') : route('document-review.index') }}" class="back-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
        Back to the file list
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
                            'need check by supervisor' => 'inline-block px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded',
                            'need approval by dept head' => 'inline-block px-2 py-1 text-xs font-semibold text-purple-800 bg-purple-100 rounded',
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
                Editor akan terbuka di <strong>tab baru</strong> ketika Anda klik tombol <strong>Open Editor</strong>. Setelah selesai mengedit dan menyimpan di DocSpace,
                @if($hideSyncButton)
                    perubahan akan disinkronkan melalui proses <strong>Approve</strong> di halaman Approval Queue.
                @else
                    kembali ke halaman ini lalu klik <strong>Save and Sync</strong> untuk menyinkronkan perubahan ke server.
                @endif
            </span>
        </div>

        {{-- ACTIONS --}}
        <div class="file-actions">
            @php
                $mapping = $file->mapping;
                $mappingStatus = strtolower($mapping?->status?->name ?? '');
            @endphp

            @if($file->docspace_file_id)
                @if($mappingStatus !== 'need review')
                    {{-- Buka editor: login dulu via popup, lalu buka doceditor --}}
                    <button type="button" class="btn btn--primary" id="btnOpen" onclick="openEditor()">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                        Open Editor
                    </button>

                    @if(!$hideSyncButton)
                        {{-- Sync ke Laravel --}}
                        <button type="button" class="btn btn--secondary hidden-sync" id="btnSync" onclick="syncFile()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="23 4 23 10 17 10"/>
                                <polyline points="1 20 1 14 7 14"/>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                            </svg>
                            Save and Sync
                        </button>
                    @endif
                @endif

                {{-- Re-upload (only when mapping status != 'need review') --}}
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
                    Upload & Open Editor
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
                    <div class="notes-modal__sub">Lengkapi catatan revisi dan tentukan department tujuan sebelum sinkronisasi.</div>
                </div>
                <div style="display:flex;gap:8px;align-items:center">
                    <button id="notesClose" class="notes-close" aria-label="Tutup">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            <div class="notes-modal__content">
                <div class="notes-tips">
                    <p class="notes-tips__title">Tips</p>
                    <ul class="notes-tips__list">
                        <li>Isi notes jika ada perubahan internal pada file.</li>
                        <li>Jika perubahan hanya karena relasi file lain, notes boleh dikosongkan.</li>
                    </ul>
                </div>

                <div class="notes-field">
                    <label class="notes-field__label" for="notesTextarea">Revision Notes</label>
                    <textarea id="notesTextarea" rows="6" placeholder="Tuliskan catatan revisi..."></textarea>
                    <span class="notes-modal__meta">Catatan revisi bersifat opsional.</span>
                </div>

                <div class="notes-field">
                    <label class="notes-field__label">Department Tujuan</label>
                    <div class="notes-modal__meta" style="margin:0 0 8px 0">Pilih 1 atau lebih departmen untuk dikirim notifikasi perubahan</div>

                    <div class="dept-controls">
                        <div class="dept-controls__left">
                            <button type="button" class="dept-control-btn" id="deptSelectAllBtn">Pilih Semua</button>
                            <button type="button" class="dept-control-btn" id="deptClearBtn">Reset</button>
                        </div>
                        <span class="dept-selected-pill" id="deptSelectedCount">0 dipilih</span>
                    </div>

                    <input
                        type="text"
                        id="deptSearchInput"
                        class="dept-search"
                        placeholder="Cari department..."
                        autocomplete="off"
                    >

                    <div class="dept-checklist" id="notesDepartmentChecklist">
                        @forelse($syncDepartments as $department)
                            <label class="dept-checklist__item">
                                <input
                                    type="checkbox"
                                    class="notes-department-checkbox"
                                    value="{{ (int) $department->id }}"
                                    data-default-checked="0"
                                >
                                <span class="dept-checklist__name">{{ $department->name }}</span>
                            </label>
                        @empty
                            <div class="dept-checklist__empty">Tidak ada department yang dapat dipilih.</div>
                        @endforelse
                    </div>
                    <div class="dept-no-result" id="deptNoResult">Department tidak ditemukan untuk kata kunci tersebut.</div>
                </div>
            </div>
            <div class="notes-modal__footer">
                <button class="btn btn--ghost" id="notesCancel">Batal</button>
                <button class="btn btn--primary" id="notesSubmit">Save & Sync</button>
            </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const LOGIN_URL    = @json($loginUrl);
    const EDITOR_URL   = @json($docEditorUrl);
    const SYNC_STATUS_URL = @json(route('editor.sync-status', $file->id));

    async function refreshSyncButtonVisibility() {
        const btn = document.getElementById('btnSync');
        if (!btn) return;

        try {
            const res = await fetch(SYNC_STATUS_URL, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (data.success && data.hasChanges) {
                btn.classList.remove('hidden-sync');
            } else {
                btn.classList.add('hidden-sync');
            }
        } catch (err) {
            console.error('Gagal cek status sinkronisasi', err);
            // Keep hidden on error to avoid false-positive sync action.
            btn.classList.add('hidden-sync');
        }
    }

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
        btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg> Open Editor`;
    }
    async function syncFile() {
        const btn = document.getElementById('btnSync');
        btn.disabled = true;
        btn.classList.add('loading');
        btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Menyimpan...`;

        const formData = await openNotesModal();
        if (formData === null) {
            // user batal
            btn.disabled = false;
            btn.classList.remove('loading');
            btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Save and Sync`;
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
                body: JSON.stringify({
                    notes: formData.notes,
                    department_ids: formData.departmentIds,
                })
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
            btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Save and Sync`;
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

    document.addEventListener('DOMContentLoaded', () => {
        refreshSyncButtonVisibility();

        window.addEventListener('focus', refreshSyncButtonVisibility);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) refreshSyncButtonVisibility();
        });

        setInterval(refreshSyncButtonVisibility, 15000);


    });

    function openNotesModal() {
            return new Promise(resolve => {
                const modal = document.getElementById('notesModal');
                const textarea = document.getElementById('notesTextarea');
                const btnCancel = document.getElementById('notesCancel');
                const btnSubmit = document.getElementById('notesSubmit');
                const btnClose = document.getElementById('notesClose');
                const backdrop = modal.querySelector('.notes-modal__backdrop');
                const departmentChecks = Array.from(modal.querySelectorAll('.notes-department-checkbox'));
                const deptSearchInput = document.getElementById('deptSearchInput');
                const deptSelectAllBtn = document.getElementById('deptSelectAllBtn');
                const deptClearBtn = document.getElementById('deptClearBtn');
                const deptSelectedCount = document.getElementById('deptSelectedCount');
                const deptNoResult = document.getElementById('deptNoResult');
                const deptItems = Array.from(modal.querySelectorAll('.dept-checklist__item'));

                const updateSelectedCount = () => {
                    const total = departmentChecks.filter(el => el.checked).length;
                    if (deptSelectedCount) {
                        deptSelectedCount.textContent = `${total} dipilih`;
                    }
                };

                const applyDepartmentFilter = () => {
                    const q = (deptSearchInput?.value || '').toLowerCase().trim();
                    let visibleCount = 0;

                    deptItems.forEach(item => {
                        const name = (item.querySelector('.dept-checklist__name')?.textContent || '').toLowerCase();
                        const match = q === '' || name.includes(q);
                        item.classList.toggle('is-hidden', !match);
                        if (match) visibleCount += 1;
                    });

                    if (deptNoResult) {
                        deptNoResult.style.display = visibleCount === 0 && deptItems.length > 0 ? 'block' : 'none';
                    }
                };

                const onDepartmentChange = () => updateSelectedCount();

                const cleanup = (value) => {
                    modal.classList.remove('show');
                    btnCancel.removeEventListener('click', onCancel);
                    btnSubmit.removeEventListener('click', onSubmit);
                    btnClose.removeEventListener('click', onCancel);
                    backdrop.removeEventListener('click', onCancel);
                    document.removeEventListener('keydown', onKey);
                    departmentChecks.forEach(el => el.removeEventListener('change', onDepartmentChange));
                    deptSearchInput?.removeEventListener('input', applyDepartmentFilter);
                    deptSelectAllBtn?.removeEventListener('click', onSelectAll);
                    deptClearBtn?.removeEventListener('click', onClear);
                    resolve(value);
                };

                const onCancel = () => cleanup(null);
                const onSubmit = () => {
                    const notesValue = textarea.value.trim();
                    const selectedDepartmentIds = departmentChecks
                        .filter(el => el.checked)
                        .map(el => Number(el.value))
                        .filter(v => Number.isInteger(v) && v > 0);

                    cleanup({
                        notes: notesValue,
                        departmentIds: selectedDepartmentIds,
                    });
                };
                const onKey = (e) => { if (e.key === 'Escape') onCancel(); };
                const onSelectAll = () => {
                    departmentChecks.forEach(el => {
                        const wrapper = el.closest('.dept-checklist__item');
                        if (!wrapper || !wrapper.classList.contains('is-hidden')) {
                            el.checked = true;
                        }
                    });
                    updateSelectedCount();
                };
                const onClear = () => {
                    departmentChecks.forEach(el => {
                        el.checked = false;
                    });
                    updateSelectedCount();
                };

                textarea.value = '';
                departmentChecks.forEach(el => {
                    el.checked = false;
                });
                if (deptSearchInput) {
                    deptSearchInput.value = '';
                }
                applyDepartmentFilter();
                updateSelectedCount();
                modal.classList.add('show');
                setTimeout(() => textarea.focus(), 80);

                btnCancel.addEventListener('click', onCancel);
                btnSubmit.addEventListener('click', onSubmit);
                btnClose.addEventListener('click', onCancel);
                backdrop.addEventListener('click', onCancel);
                document.addEventListener('keydown', onKey);
                departmentChecks.forEach(el => el.addEventListener('change', onDepartmentChange));
                deptSearchInput?.addEventListener('input', applyDepartmentFilter);
                deptSelectAllBtn?.addEventListener('click', onSelectAll);
                deptClearBtn?.addEventListener('click', onClear);
            });
    }
</script>
@endpush
