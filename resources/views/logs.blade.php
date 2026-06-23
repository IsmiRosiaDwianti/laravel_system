@extends('layouts.app')

@section('content')

<h1>Monitoring Logs</h1>

<br>

<div class="table-container">

<table>

<thead>

<tr>
    <th>Waktu</th>
    <th>Service</th>
    <th>Status</th>
    <th>Code</th>
    <th>Response</th>
    <th>Message</th>
</tr>

</thead>

<tbody>

@forelse($logs as $log)

<tr>

    <td>
        {{ $log->created_at }}
    </td>

    <td>
        {{ $log->service->name ?? '-' }}
    </td>

    <td>

        @if($log->status=='UP')

            <span class="badge badge-green">
                UP
            </span>

        @elseif($log->status=='WARNING')

            <span class="badge badge-orange">
                WARNING
            </span>

        @else

            <span class="badge badge-red">
                DOWN
            </span>

        @endif

    </td>

    <td>
        {{ $log->response_code }}
    </td>

    <td>
        {{ $log->response_time }} s
    </td>

    <td>
        {{ $log->message }}
    </td>

</tr>

@empty

<tr>

<td colspan="6">
Belum ada log
</td>

</tr>

@endforelse

</tbody>

</table>

<br>

{{ $logs->links() }}

</div>

@endsection