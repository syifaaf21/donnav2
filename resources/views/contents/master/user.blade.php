{{-- @extends('layouts.app')

@section('content') --}}
<h1>Daftar Users</h1>

<a href="{{ route('users.create') }}">Add</a>

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; margin-top: 10px;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>NPK</th>
            <th>Email</th>
            <th>Role</th>
            <th>Department</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->npk }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->role->name ?? '-' }}</td>
            <td>{{ $user->department->name ?? '-' }}</td>
            <td>
                <a href="{{ route('users.edit', $user->id) }}">Edit</a> |
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background:none; border:none; color:red; cursor:pointer; padding:0;">Hapus</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
{{-- @endsection --}}
