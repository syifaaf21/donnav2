@extends('layouts.app')

@section('title', 'Document Approval')
@section('subtitle', 'Documents waiting for approval')

@section('content')
<div class="px-6 py-4">

    <div class="flex flex-col lg:flex-row justify-between gap-4 mb-6">
        <h2 class="text-xl font-bold">Approval Queue</h2>

        <form method="GET" class="flex flex-wrap gap-2">
            <select name="department_id" class="rounded-lg border-gray-300">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>

            <input type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search document..."
                class="rounded-lg border-gray-300 px-3">

            <button class="px-4 py-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600">
                Filter
            </button>
        </form>
    </div>

    {{-- 🔥 Reuse table --}}
    @include('contents.document-control.partials.department-details', [
        'department' => null,
        'approvalMode' => true
    ])

</div>
@endsection
