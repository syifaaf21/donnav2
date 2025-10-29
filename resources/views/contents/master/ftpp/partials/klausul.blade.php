{{-- Klausul Tabs --}}
<div class="flex justify-between items-center mb-2">
    <h2 class="text-lg font-semibold text-gray-700">Klausul</h2>
    <button id="btn-add-klausul" data-bs-toggle="modal" data-bs-target="#modalAddKlausul"
        class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 flex items-center gap-1">
        <i class="bi bi-plus"></i> Add Klausul
    </button>
</div>

@if ($klausuls->count() > 0)
    {{-- Tabs --}}
    <div class="border-b border-gray-200 mb-3">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="klausulTabs">
            @foreach ($klausuls as $index => $klausul)
                <li class="me-2">
                    <button
                        class="inline-block p-2 border-b-2 {{ $index === 0 ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-blue-600 hover:border-blue-600' }}"
                        data-tab="tab-{{ $klausul->id }}">
                        {{ $klausul->name }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Tab Content --}}
    @foreach ($klausuls as $index => $klausul)
        <div id="tab-{{ $klausul->id }}" class="tab-content {{ $index === 0 ? '' : 'hidden' }}">
            @if ($klausul->headKlausul->count() > 0)
                <div class="shadow rounded-lg border border-gray-200">
                    <table class="min-w-full text-sm text-left text-gray-600">
                        <thead class="bg-gray-100 text-gray-700 text-xs uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-2 text-center w-12">No</th>
                                <th class="px-4 py-2 w-28">Code</th>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2 text-center w-36">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($klausul->headKlausul as $i => $head)
                                <tr class="hover:bg-gray-50 cursor-pointer head-row"
                                    data-collapse-target="subKlausul-{{ $head->id }}">
                                    <td class="px-4 py-2 text-gray-700">
                                        <div class="flex items-center gap-1">
                                            <i data-feather="chevron-right"
                                                class="w-4 h-4 rotate-icon transition-transform"></i>{{ $i + 1 }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $head->code }}</td>
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $head->name }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <div class="flex justify-center gap-2">
                                            <button data-head-id="{{ $head->id }}"
                                                data-head-name="{{ $head->name }}"
                                                data-sub='@json($head->subKlausul)'
                                                class="btn-edit-klausul bg-blue-600 hover:bg-blue-700 text-white p-1.5 rounded transition">
                                                <i data-feather="edit" class="w-4 h-4"></i>
                                            </button>
                                            |
                                            <form action="{{ route('master.ftpp.klausul.destroy', $head->id) }}"
                                                method="POST" class="delete-form inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="bg-red-600 hover:bg-red-700 text-white p-1.5 rounded transition">
                                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Sublist dropdown (bukan tr lagi) --}}
                                @if ($head->subKlausul->count() > 0)
                                    <tr>
                                        <td colspan="4" class="p-0 border-t-0">
                                            <div id="subKlausul-{{ $head->id }}" class="sublist bg-gray-200"
                                                data-open="false">
                                                <div class="p-4">
                                                    <h6 class="text-sm font-semibold text-gray-600 mb-3">
                                                        Sub Klausul ({{ $head->subKlausul->count() }})
                                                    </h6>
                                                    <div class="flex flex-col gap-2">
                                                        @foreach ($head->subKlausul as $sub)
                                                            <div
                                                                class="border border-gray-200 bg-white p-3 rounded-lg shadow-sm flex justify-between items-center">
                                                                <div>
                                                                    <div class="font-semibold text-gray-800">
                                                                        {{ $sub->code }}</div>
                                                                    <div class="text-sm text-gray-500">
                                                                        {{ $sub->name }}</div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>

                    </table>
                </div>
            @else
                <p class="text-gray-500 italic">No Head Klausul data.</p>
            @endif
        </div>
    @endforeach
@else
    <div class="text-center text-gray-500 py-5 italic">No Klausul data available.</div>
@endif

@include('contents.master.ftpp.partials.modal_add_klausul')
@include('contents.master.ftpp.partials.modal_edit_klausul')

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- Tab Switching ---
            document.querySelectorAll('#klausulTabs button').forEach(btn => {
                btn.addEventListener('click', () => {
                    const target = btn.dataset.tab;
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.add(
                        'hidden'));
                    document.getElementById(target).classList.remove('hidden');

                    document.querySelectorAll('#klausulTabs button').forEach(b => {
                        b.classList.remove('border-blue-600', 'text-blue-600');
                        b.classList.add('border-transparent', 'text-gray-500');
                    });
                    btn.classList.add('border-blue-600', 'text-blue-600');
                    btn.classList.remove('border-transparent', 'text-gray-500');
                });
            });

            // --- Inisialisasi sublist ---
            document.querySelectorAll('.sublist').forEach(el => {
                el.style.transition = 'max-height 0.3s ease';
                el.style.overflow = 'hidden';
                el.style.maxHeight = '0px'; // tertutup di awal
            });

            // --- Dropdown Head Klausul ---
            document.querySelectorAll('.head-row').forEach(row => {
                row.addEventListener('click', (e) => {
                    console.log('clicked:', row.dataset.collapseTarget);
                    // hindari klik pada tombol Edit/Delete
                    if (e.target.closest('button') || e.target.closest('form')) return;

                    const targetId = row.dataset.collapseTarget;
                    const sublist = document.getElementById(targetId);
                    const icon = row.querySelector('.rotate-icon');

                    if (!sublist) return;

                    const isOpen = sublist.getAttribute('data-open') === 'true';

                    // Tutup semua sublist lain dalam tab yang sama
                    row.closest('tbody').querySelectorAll('.sublist').forEach(other => {
                        if (other !== sublist) {
                            other.style.maxHeight = '0px';
                            other.setAttribute('data-open', 'false');
                            const otherIcon = other.closest('tr').previousElementSibling
                                ?.querySelector('.rotate-icon');
                            if (otherIcon) otherIcon.style.transform = 'rotate(0deg)';
                        }
                    });

                    if (isOpen) {
                        // Tutup dropdown aktif
                        sublist.style.maxHeight = '0px';
                        sublist.setAttribute('data-open', 'false');
                        if (icon) icon.style.transform = 'rotate(0deg)';
                    } else {
                        // Buka dropdown
                        sublist.style.maxHeight = sublist.scrollHeight + 'px';
                        sublist.setAttribute('data-open', 'true');
                        if (icon) icon.style.transform = 'rotate(90deg)';
                    }
                });
            });

            // --- Render feather icons setelah DOM siap ---
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // --- DELETE HEAD & SUB (Confirm) ---
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', e => {
                    e.preventDefault();
                    if (confirm('Delete this Head Klausul and all its Sub Klausuls?')) form
                        .submit();
                });
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .sublist {
            transition: max-height 0.3s ease;
            overflow: hidden;
        }

        .rotate-icon {
            transition: transform 0.3s ease;
        }
    </style>
@endpush
