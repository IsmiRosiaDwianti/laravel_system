@extends('layouts.app')

@section('content')

<h1>Tambah Kontak WhatsApp</h1>

<br>

<form action="{{ route('contacts.store') }}"
      method="POST">

    @csrf

    <label>Nama</label>

    <br>

    <input
        type="text"
        name="name"
        required
        style="
        width:100%;
        padding:10px;">

    <br><br>

    <label>Nomor WhatsApp</label>

    <br>

    <input
        type="text"
        name="phone"
        placeholder="628xxxxxxxxxx"
        required
        style="
        width:100%;
        padding:10px;">

    <br><br>

    <button
        style="
        background:#16a34a;
        color:white;
        border:none;
        padding:10px 20px;
        border-radius:5px;">

        Simpan

    </button>

</form>

@endsection