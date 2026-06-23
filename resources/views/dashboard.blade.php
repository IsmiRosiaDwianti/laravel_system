@extends('layouts.app')

@section('content')

<div style="
display:grid;
grid-template-columns:repeat(4,1fr);
gap:20px;
margin-bottom:25px;">

    <div style="
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 2px 10px rgba(0,0,0,.05);">

        <h3>Total Service</h3>
        <h1>{{ $total }}</h1>

    </div>

    <div style="
    background:#dcfce7;
    padding:20px;
    border-radius:15px;">

        <h3>UP</h3>
        <h1>{{ $up }}</h1>

    </div>

    <div style="
    background:#fef3c7;
    padding:20px;
    border-radius:15px;">

        <h3>WARNING</h3>
        <h1>{{ $warning }}</h1>

    </div>

    <div style="
    background:#fee2e2;
    padding:20px;
    border-radius:15px;">

        <h3>DOWN</h3>
        <h1>{{ $down }}</h1>

    </div>

</div>

<!-- LINE CHART -->

<div style="
background:white;
padding:20px;
border-radius:15px;
margin-bottom:25px;
box-shadow:0 2px 10px rgba(0,0,0,.05);">

    <h2 style="margin-bottom:20px;">
        Response Time Monitoring
    </h2>

    <canvas id="responseChart"></canvas>

</div>

<!-- TABEL -->

<div style="
background:white;
padding:20px;
border-radius:15px;
box-shadow:0 2px 10px rgba(0,0,0,.05);">

    <h2 style="margin-bottom:20px;">
        Status Service Terbaru
    </h2>

    <table
        width="100%"
        cellpadding="10"
        cellspacing="0">

        <thead>

            <tr style="background:#f8fafc;">

                <th>ID</th>
                <th>Nama Service</th>
                <th>Target</th>
                <th>Status</th>
                <th>Response Time</th>

            </tr>

        </thead>

        <tbody>

            @forelse($latestServices as $service)

                <tr>

                    <td>{{ $service->id }}</td>

                    <td>{{ $service->name }}</td>

                    <td>{{ $service->target }}</td>

                    <td>

                        @if($service->last_status == 'UP')

                            <span style="
                            background:#22c55e;
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
                        {{ $service->last_response_time ?? 0 }} s
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="5" align="center">
                        Belum ada service
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const ctx = document.getElementById('responseChart');

new Chart(ctx, {

    type: 'line',

    data: {

        labels: @json($chartLabels),

        datasets: [{

            label: 'Response Time (s)',

            data: @json($chartTimes),

            borderColor: '#2563eb',

            backgroundColor: 'rgba(37,99,235,0.15)',

            borderWidth: 3,

            tension: 0.4,

            fill: true

        }]

    },

    options: {

        responsive: true,

        plugins: {

            legend: {

                display: true

            }

        },

        scales: {

            y: {

                beginAtZero: true,

                title: {

                    display: true,

                    text: 'Detik'

                }

            }

        }

    }

});

setTimeout(function(){

    location.reload();

},30000);

</script>

@endsection