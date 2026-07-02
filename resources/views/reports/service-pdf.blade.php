<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Monitoring Service</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 11px;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
        }
        .header .title {
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header .subtitle {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            background-color: #ecf0f1;
            padding: 5px 10px;
            margin-bottom: 10px;
            border-left: 4px solid #3498db;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            padding: 3px 10px 3px 0;
            font-weight: bold;
            width: 150px;
            color: #555;
        }
        .info-value {
            display: table-cell;
            padding: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 10px;
        }
        table th {
            background-color: #2c3e50;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tr:hover {
            background-color: #f5f5f5;
        }
        .status-up {
            color: #27ae60;
            font-weight: bold;
        }
        .status-warning {
            color: #f39c12;
            font-weight: bold;
        }
        .status-down {
            color: #e74c3c;
            font-weight: bold;
        }
        .status-unknown {
            color: #95a5a6;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-up {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-down {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-unknown {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #7f8c8d;
        }
        .stat-box {
            display: inline-block;
            width: 19%;
            margin-right: 1%;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .stat-box .number {
            font-size: 18px;
            font-weight: bold;
        }
        .stat-box .label {
            font-size: 9px;
            color: #7f8c8d;
            margin-top: 2px;
        }
        .stat-box-up .number { color: #27ae60; }
        .stat-box-warning .number { color: #f39c12; }
        .stat-box-down .number { color: #e74c3c; }
        .stat-box-total .number { color: #2c3e50; }
        .page-break {
            page-break-before: always;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <div class="title">LAPORAN MONITORING SERVICE</div>
        <div class="subtitle">
            Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} | 
            Laporan Service: {{ $reportData['service']['name'] }}
        </div>
    </div>

    <!-- SERVICE INFORMATION -->
    <div class="section">
        <div class="section-title">INFORMASI SERVICE</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nama Service</div>
                <div class="info-value">{{ $reportData['service']['name'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Target</div>
                <div class="info-value">{{ $reportData['service']['target'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tipe</div>
                <div class="info-value">{{ strtoupper($reportData['service']['type']) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status Terakhir</div>
                <div class="info-value">
                    <span class="status-{{ strtolower($reportData['service']['last_status']) }}">
                        {{ $reportData['service']['last_status'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- PERIOD INFORMATION -->
    <div class="section">
        <div class="section-title">PERIODE LAPORAN</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Dari Tanggal</div>
                <div class="info-value">{{ $reportData['period']['date_from'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Sampai Tanggal</div>
                <div class="info-value">{{ $reportData['period']['date_to'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total Hari</div>
                <div class="info-value">{{ $reportData['period']['total_days'] }} hari</div>
            </div>
        </div>
    </div>

    <!-- STATISTICS -->
    <div class="section">
        <div class="section-title">STATISTIK</div>
        <div style="text-align: center; margin-bottom: 15px;">
            <div class="stat-box stat-box-total">
                <div class="number">{{ $reportData['statistics']['total_checks'] }}</div>
                <div class="label">Total Check</div>
            </div>
            <div class="stat-box stat-box-up">
                <div class="number">{{ $reportData['statistics']['up_count'] }}</div>
                <div class="label">UP</div>
            </div>
            <div class="stat-box stat-box-warning">
                <div class="number">{{ $reportData['statistics']['warning_count'] }}</div>
                <div class="label">WARNING</div>
            </div>
            <div class="stat-box stat-box-down">
                <div class="number">{{ $reportData['statistics']['down_count'] }}</div>
                <div class="label">DOWN</div>
            </div>
            <div class="stat-box stat-box-total">
                <div class="number">{{ $reportData['statistics']['uptime_percentage'] }}%</div>
                <div class="label">Uptime</div>
            </div>
        </div>

        <table>
            <tr>
                <td><strong>Rata-rata Response Time</strong></td>
                <td>{{ $reportData['statistics']['avg_response_time'] }} s</td>
            </tr>
            <tr>
                <td><strong>Response Time Tercepat</strong></td>
                <td>{{ $reportData['statistics']['min_response_time'] }} s</td>
            </tr>
            <tr>
                <td><strong>Response Time Terlambat</strong></td>
                <td>{{ $reportData['statistics']['max_response_time'] }} s</td>
            </tr>
        </table>
    </div>

    <!-- CRITICAL DATES -->
    @if(!empty($reportData['critical_dates']))
        <div class="section">
            <div class="section-title">TANGGAL KRITIS</div>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th style="text-align: center;">Total Check</th>
                        <th style="text-align: center;">UP</th>
                        <th style="text-align: center;">WARNING</th>
                        <th style="text-align: center;">DOWN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['critical_dates'] as $date => $data)
                        <tr>
                            <td>{{ $date }}</td>
                            <td style="text-align: center;">{{ $data['total_checks'] }}</td>
                            <td style="text-align: center; color: #27ae60;">{{ $data['up_count'] }}</td>
                            <td style="text-align: center; color: #f39c12;">{{ $data['warning_count'] }}</td>
                            <td style="text-align: center; color: #e74c3c;">{{ $data['down_count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- VULNERABLE HOURS -->
    @if(!empty($reportData['vulnerable_hours']))
        <div class="section">
            <div class="section-title">JAM RAWAN</div>
            <table>
                <thead>
                    <tr>
                        <th>Jam</th>
                        <th style="text-align: center;">Total Masalah</th>
                        <th style="text-align: center;">DOWN</th>
                        <th style="text-align: center;">WARNING</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['vulnerable_hours'] as $hour => $data)
                        <tr>
                            <td>{{ $hour }}:00 - {{ $hour }}:59</td>
                            <td style="text-align: center;">{{ $data['total_issues'] }}</td>
                            <td style="text-align: center; color: #e74c3c;">{{ $data['down_count'] }}</td>
                            <td style="text-align: center; color: #f39c12;">{{ $data['warning_count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- RECENT DOWNS -->
    @if($reportData['recent_downs']->isNotEmpty())
        <div class="section">
            <div class="section-title">5 KEJADIAN DOWN TERAKHIR</div>
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pesan</th>
                        <th style="text-align: center;">Response Code</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['recent_downs'] as $down)
                        <tr>
                            <td>{{ $down['time'] }}</td>
                            <td>{{ $down['message'] }}</td>
                            <td style="text-align: center;">{{ $down['response_code'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- DETAIL LOGS -->
    <div class="section page-break">
        <div class="section-title">DETAIL LOG</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Tanggal/Waktu</th>
                    <th style="width: 10%; text-align: center;">Status</th>
                    <th style="width: 12%; text-align: center;">Response Code</th>
                    <th style="width: 12%; text-align: center;">Response Time (s)</th>
                    <th style="width: 51%;">Message</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['logs'] as $log)
                    <tr>
                        <td>{{ $log['date'] }}</td>
                        <td style="text-align: center;">
                            <span class="badge badge-{{ strtolower($log['status']) }}">
                                {{ $log['status'] }}
                            </span>
                        </td>
                        <td style="text-align: center;">{{ $log['response_code'] }}</td>
                        <td style="text-align: center;">{{ $log['response_time'] }}</td>
                        <td>{{ $log['message'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        Laporan ini dibuat secara otomatis oleh Sistem Monitoring Service<br>
        Halaman {{ $page ?? 1 }} dari {{ $pages ?? 1 }}
    </div>
</body>
</html>