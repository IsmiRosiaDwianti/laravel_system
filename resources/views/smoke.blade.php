@extends('layouts.app')

@section('content')

<h1 style="margin-bottom:20px;">
    Smoke Detector Monitoring
</h1>

{{-- Statistik --}}
<div style="
display:grid;
grid-template-columns:repeat(4,1fr);
gap:20px;
margin-bottom:20px;">

    <div style="
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 2px 10px rgba(0,0,0,.05);">

        <h3>Total Device</h3>
        <h1>{{ $totalDevice }}</h1>

    </div>

    <div style="
    background:#dcfce7;
    padding:20px;
    border-radius:15px;">

        <h3>Online</h3>
        <h1>{{ $online }}</h1>

    </div>

    <div style="
    background:#fee2e2;
    padding:20px;
    border-radius:15px;">

        <h3>Offline</h3>
        <h1>{{ $offline }}</h1>

    </div>

    <div style="
    background:#fef3c7;
    padding:20px;
    border-radius:15px;">

        <h3>Danger</h3>
        <h1>{{ $danger }}</h1>

    </div>

</div>

{{-- Grafik --}}
<div style="
background:white;
padding:20px;
border-radius:15px;
margin-bottom:20px;
box-shadow:0 2px 10px rgba(0,0,0,.05);">

    <h2 style="margin-bottom:20px;">
        Grafik Nilai Asap (Realtime)
    </h2>

    <canvas id="smokeChart"></canvas>

</div>

{{-- Tabel Device --}}
<div style="
background:white;
padding:20px;
border-radius:15px;
box-shadow:0 2px 10px rgba(0,0,0,.05);">

    <h2 style="margin-bottom:20px;">
        Status Device
    </h2>

    <table
        width="100%"
        cellpadding="10"
        cellspacing="0">

        <thead>

            <tr style="background:#f8fafc;">

                <th>Device</th>
                <th>Lokasi</th>
                <th>Nilai Asap</th>
                <th>Status Asap</th>
                <th>Status Device</th>
                <th>Last Seen</th>

            </tr>

        </thead>

        <tbody>

            @forelse($devices as $device)

                <tr>

                    <td>
                        {{ $device->name }}
                    </td>

                    <td>
                        {{ $device->location }}
                    </td>

                    <td>
                        {{ $device->smoke_value }}
                    </td>

                    <td>

                        @if($device->status == 'DANGER')

                            <span style="
                            background:#ef4444;
                            color:white;
                            padding:5px 10px;
                            border-radius:20px;">
                                DANGER
                            </span>

                        @else

                            <span style="
                            background:#22c55e;
                            color:white;
                            padding:5px 10px;
                            border-radius:20px;">
                                NORMAL
                            </span>

                        @endif

                    </td>

                    <td>

                        @if($device->device_status == 'ONLINE')

                            <span style="
                            background:#22c55e;
                            color:white;
                            padding:5px 10px;
                            border-radius:20px;">
                                ONLINE
                            </span>

                        @else

                            <span style="
                            background:#ef4444;
                            color:white;
                            padding:5px 10px;
                            border-radius:20px;">
                                OFFLINE
                            </span>

                        @endif

                    </td>

                    <td>
                        {{ $device->last_seen_at }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6" align="center">
                        Belum ada device
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const labels = [
    @foreach($chartLogs as $log)
        '{{ $log->created_at->format("H:i:s") }}',
    @endforeach
];

const values = [
    @foreach($chartLogs as $log)
        {{ $log->smoke_value }},
    @endforeach
];

new Chart(
    document.getElementById('smokeChart'),
    {
        type: 'line',

        data: {
            labels: labels,

            datasets: [{
                label: 'Nilai Asap',
                data: values,
                tension: 0.4
            }]
        },

        options: {
            responsive: true,

            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    }
);

setTimeout(function(){

    location.reload();

},30000);

</script>

@endsection