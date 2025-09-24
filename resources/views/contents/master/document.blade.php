{{-- @extends('layouts.app')

@section('content') --}}
<div>
    <h1>Document Mappings</h1>

    <a href="{{ route('document-mappings.create') }}">Add New Document</a>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>No.</th>
                <th>Document</th>
                <th>Type</th>
                <th>Part Number</th>
                <th>Document Number</th>
                <th>Status</th>
                <th>Upload By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documentMappings as $dm)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $dm->document->name ?? '-' }}</td>
                <td>{{ ucfirst($dm->type) }}</td>
                <td>{{ $dm->partNumber->number ?? '-' }}</td>
                <td>{{ $dm->document_number }}</td>
                <td>{{ $dm->status->name ?? '-' }}</td>
                <td>{{ $dm->user->name ?? '-' }}</td>
                <td>
                    <form action="{{ route('document-mappings.destroy', $dm->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{-- @endsection --}}
