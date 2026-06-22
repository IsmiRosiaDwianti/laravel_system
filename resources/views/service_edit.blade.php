@extends('layouts.app')

@section('content')

<h1>Edit Service</h1>

<form method="POST"
      action="{{ route('services.update',$service->id) }}">

    @csrf
    @method('PUT')

    <p>
        Nama Service
        <br>
        <input type="text"
               name="name"
               value="{{ $service->name }}"
               required>
    </p>

    <p>
        Target
        <br>
        <input type="text"
               name="target"
               value="{{ $service->target }}"
               required>
    </p>

    <p>
        Type
        <br>

        <select name="type">

            <option value="http"
                {{ $service->type == 'http' ? 'selected' : '' }}>
                HTTP
            </option>

            <option value="ping"
                {{ $service->type == 'ping' ? 'selected' : '' }}>
                PING
            </option>

        </select>

    </p>

    <button type="submit">
        Update
    </button>

</form>

@endsection