@extends('layouts.app')

@section('content')

<h1>Monitoring Service</h1>

@if(session('success'))
    <div style="
        background:#d1fae5;
        color:#065f46;
        padding:10px;
        margin-bottom:15px;
        border-radius:5px;">
        {{ session('success') }}
    </div>
@endif

<a href="{{ route('services.create') }}"
   style="
   background:#2563eb;
   color:white;
   padding:10px 15px;
   text-decoration:none;
   border-radius:5px;">
    + Tambah Service
</a>

<br><br>

<table border="1"
       cellpadding="10"
       cellspacing="0"
       width="100%">

    <tr>
        <th>ID</th>
        <th>Nama</th>
        <th>Target</th>
        <th>Type</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>

    @forelse($services as $service)

        <tr>

            <td>{{ $service->id }}</td>

            <td>{{ $service->name }}</td>

            <td>{{ $service->target }}</td>

            <td>{{ strtoupper($service->type) }}</td>

            <td>

                @if($service->last_status == 'UP')

                    <span style="
                        background:#10b981;
                        color:white;
                        padding:5px 10px;
                        border-radius:20px;">
                        UP
                    </span>

                @elseif($service->last_status == 'WARNING')

                    <span style="
                        background:#f59e0b;
                        color:white;
                        padding:5px 10px;
                        border-radius:20px;">
                        WARNING
                    </span>

                @elseif($service->last_status == 'DOWN')

                    <span style="
                        background:#ef4444;
                        color:white;
                        padding:5px 10px;
                        border-radius:20px;">
                        DOWN
                    </span>

                @else

                    <span style="
                        background:#94a3b8;
                        color:white;
                        padding:5px 10px;
                        border-radius:20px;">
                        UNKNOWN
                    </span>

                @endif

            </td>

            <td>

                <a href="{{ route('services.edit', $service->id) }}"
                   style="
                   background:orange;
                   color:white;
                   padding:5px 10px;
                   text-decoration:none;
                   border-radius:3px;">
                    Edit
                </a>

                <form action="{{ route('services.destroy', $service->id) }}"
                      method="POST"
                      style="display:inline;">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            onclick="return confirm('Hapus service ini?')"
                            style="
                            background:red;
                            color:white;
                            border:none;
                            padding:5px 10px;
                            border-radius:3px;
                            cursor:pointer;">
                        Hapus
                    </button>

                </form>

            </td>

        </tr>

    @empty

        <tr>
            <td colspan="6" align="center">
                Belum ada service
            </td>
        </tr>

    @endforelse

</table>

@endsection