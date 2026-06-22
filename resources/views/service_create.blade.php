@extends('layouts.app')

@section('content')

<h1>Tambah Service</h1>

<form method="POST" action="{{ route('services.store') }}">

    @csrf

    <p>
        Nama Service
        <br>
        <input type="text" name="name" required>
    </p>

    <p>
        Target URL / IP
        <br>
        <input type="text" name="target" required>
    </p>

    <p>
        Type
        <br>
        <select name="type">

            <option value="http">
                HTTP
            </option>

            <option value="ping">
                PING
            </option>

        </select>
    </p>

    <button type="submit">
        Simpan
    </button>

</form>

@endsection