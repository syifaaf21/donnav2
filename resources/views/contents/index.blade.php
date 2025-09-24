@extends('layouts.app')

@section('content')
<a href="{{ route('documents.index') }}">Documents</a>
<a href="{{ route('user.index') }}">Users</a>
<a href="{{ route('part_numbers.index') }}">Part Numbers</a>
@endsection
