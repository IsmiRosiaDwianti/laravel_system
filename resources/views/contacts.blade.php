@extends('layouts.app')

@section('content')

<h1>Contacts WhatsApp</h1>

<br>

<a href="{{ route('contacts.create') }}"
   style="
   background:#2563eb;
   color:white;
   padding:10px 15px;
   border-radius:5px;
   text-decoration:none;">
   + Tambah Kontak
</a>

<br><br>

@if(session('success'))

<div style="
background:#d1fae5;
padding:10px;
border-radius:5px;
margin-bottom:15px;">

    {{ session('success') }}

</div>

@endif

<div class="table-container">

<table>

<thead>

<tr>
    <th>Nama</th>
    <th>Nomor</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>

</thead>

<tbody>

@forelse($contacts as $contact)

<tr>

    <td>{{ $contact->name }}</td>

    <td>{{ $contact->phone }}</td>

    <td>

        @if($contact->is_active)

            <span class="badge badge-green">
                Aktif
            </span>

        @else

            <span class="badge badge-red">
                Nonaktif
            </span>

        @endif

    </td>

    <td>

        <form
            action="{{ route('contacts.destroy',$contact->id) }}"
            method="POST">

            @csrf
            @method('DELETE')

            <button
                onclick="return confirm('Hapus kontak?')">

                Hapus

            </button>

        </form>

    </td>

</tr>

@empty

<tr>

<td colspan="4">
    Belum ada kontak
</td>

</tr>

@endforelse

</tbody>

</table>

</div>

@endsection