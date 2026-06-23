<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring System DISKOMINFOTIK</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:Arial, sans-serif;
            background:#f1f5f9;
        }

        /* ======================
           SIDEBAR
        ====================== */

        .sidebar{
            position:fixed;
            left:0;
            top:0;
            width:250px;
            height:100vh;
            background:#1e293b;
            color:white;
            padding:20px;
        }

        .sidebar h2{
            margin-bottom:30px;
        }

        .sidebar a{
            display:block;
            color:white;
            text-decoration:none;
            padding:12px 0;
        }

        .sidebar a:hover{
            color:#60a5fa;
        }

        /* ======================
           MAIN CONTENT
        ====================== */

        .main{
            margin-left:250px;
            padding:30px;
        }

        /* ======================
           DASHBOARD CARD
        ====================== */

        .dashboard-cards{
            display:grid;
            grid-template-columns:repeat(4,1fr);
            gap:20px;
            margin-bottom:25px;
        }

        .card{
            padding:25px;
            border-radius:15px;
            color:white;
            box-shadow:0 5px 15px rgba(0,0,0,0.1);
        }

        .card h3{
            margin:0;
            font-size:16px;
        }

        .card h1{
            margin-top:15px;
            font-size:42px;
        }

        .card-blue{
            background:#2563eb;
        }

        .card-green{
            background:#16a34a;
        }

        .card-orange{
            background:#ea580c;
        }

        .card-red{
            background:#dc2626;
        }

        /* ======================
           TABLE
        ====================== */

        .table-container{
            background:white;
            padding:20px;
            border-radius:15px;
            box-shadow:0 5px 15px rgba(0,0,0,0.05);
        }

        .table-container table{
            width:100%;
            border-collapse:collapse;
        }

        .table-container th{
            background:#f8fafc;
            padding:12px;
            text-align:left;
        }

        .table-container td{
            padding:12px;
            border-bottom:1px solid #e5e7eb;
        }

        /* ======================
           STATUS BADGE
        ====================== */

        .badge{
            padding:6px 12px;
            border-radius:999px;
            color:white;
            font-size:12px;
            font-weight:bold;
        }

        .badge-green{
            background:#16a34a;
        }

        .badge-orange{
            background:#ea580c;
        }

        .badge-red{
            background:#dc2626;
        }

        .badge-gray{
            background:#6b7280;
        }

        /* ======================
           RESPONSIVE
        ====================== */

        @media(max-width:900px){

            .dashboard-cards{
                grid-template-columns:repeat(2,1fr);
            }

        }

        @media(max-width:600px){

            .sidebar{
                width:100%;
                height:auto;
                position:relative;
            }

            .main{
                margin-left:0;
            }

            .dashboard-cards{
                grid-template-columns:1fr;
            }

        }

    </style>
</head>

<body>

<div class="sidebar">

    <h2>DISKOMINFOTIK</h2>

    <a href="{{ route('dashboard') }}">
        Dashboard
    </a>

    <a href="{{ route('services') }}">
        Monitoring Service
    </a>

    <a href="{{ route('smoke') }}">
        Smoke Detector
    </a>

    <a href="{{ route('contacts') }}">
        Contacts
    </a>

    <a href="{{ route('logs') }}">
        Monitoring Logs
    </a>

</div>

<div class="main">

    @yield('content')

</div>

</body>
</html>