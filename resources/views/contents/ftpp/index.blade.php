@extends('layouts.app')
@section('title', 'FTPP')
@section('content')
    <div x-data="ftppApp()" class="container mx-auto my-2 px-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

            {{-- LEFT SIDE --}}
            <div class="lg:col-span-3 bg-white border border-gray-100 rounded-2xl shadow-sm p-4 overflow-auto h-[90vh]">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-lg font-semibold text-gray-700">FTPP List</h2>
                    <button @click="createNew()" class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700">
                        + Add
                    </button>
                </div>

                <ul>
                    @foreach ($findings as $item)
                        <li @click="loadForm({{ $item->id }})" class="cursor-pointer px-2 py-2 border-b hover:bg-blue-50"
                            :class="selectedId === {{ $item->id }} ? 'bg-blue-100' : ''">
                            <div class="font-semibold text-sm text-gray-700">
                                {{ $item->registration_number }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $item->department->name ?? '-' }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- RIGHT SIDE --}}
            <div class="lg:col-span-9 bg-white border border-gray-100 rounded-2xl shadow-sm p-3 overflow-auto h-[90vh]">
                <template x-if="!formLoaded">
                    <div class="text-center text-gray-400 mt-20">
                        Choose FTPP from the list or click add
                    </div>
                </template>

                <template x-if="formLoaded">
                    <form action="{{ route('ftpp.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- HEADER --}}
                        <div class="text-center font-bold text-lg mb-2">FORM TINDAKAN PERBAIKAN DAN PENCEGAHAN TEMUAN AUDIT
                        </div>

                        {{-- AUDITOR INPUT --}}
                        @include('contents.ftpp.partials.auditor-input')
                        {{-- END AUDITOR INPUT --}}

                        {{-- AUDITEE INPUT --}}
                        @include('contents.ftpp.partials.auditee-input')

                        {{-- AUDITOR VERIFY --}}
                        @include('contents.ftpp.partials.auditor-verification')
                        {{-- END AUDITOR VERIFY --}}

                        {{-- STATUS --}}
                        <div class="flex justify-between mt-3">
                            <div class="text-lg font-bold">
                                Status:
                                <span :class="form.status_id == 1 ? 'text-red-500' : 'text-green-600'">
                                    <span x-text="form.status_id == 1 ? 'OPEN' : 'CLOSE'"></span>
                                </span>
                            </div>
                            <button type="submit" class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700">
                                Simpan
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        const klausulData = @json($klausuls);
    </script>

    <script>
        function ftppApp() {
            return {
                formLoaded: false,
                selectedId: null,
                form: {
                    status_id: 6,
                },

                createNew() {
                    this.selectedId = null;
                    this.form = {
                        status_id: 1,
                        klausul_list: []
                    };
                    this.formLoaded = true;
                },

                async loadForm(id) {
                    const res = await fetch(`/ftpp/${id}`);
                    this.form = await res.json();
                    this.formLoaded = true;
                },
            }
        }
    </script>
@endpush
