@extends('layouts.app')

@section('title', 'Document Files')

@section('content')
<div class="p-4">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-medium">Document Files</h2>
        <a href="{{ route('editor.index') }}" class="btn btn-sm">Open Editor</a>
    </div>

    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full divide-y">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Path</th>
                    <th class="px-4 py-2 text-left">Uploaded</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y">
                @foreach($files as $file)
                <tr>
                    <td class="px-4 py-2">{{ $loop->iteration }}</td>
                    <td class="px-4 py-2">{{ $file->original_name }}</td>
                    <td class="px-4 py-2">{{ $file->file_path }}</td>
                    <td class="px-4 py-2">{{ optional($file->created_at)->toDayDateTimeString() }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="text-blue-600 mr-3">View</a>
                        <a href="{{ route('editor.index', ['file_id' => $file->id]) }}" class="text-green-600">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn { background: #0ea5a4; color: white; padding: .4rem .75rem; border-radius: .5rem }
</style>
@endpush
