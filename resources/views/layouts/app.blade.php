<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring System</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            margin:0;
            background:#f5f5f5;
        }

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

        .sidebar a{
            display:block;
            color:white;
            text-decoration:none;
            padding:10px 0;
        }

        .sidebar a:hover{
            color:#60a5fa;
        }

        .main{
            margin-left:250px;
            padding:30px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>DISKOMINFOTIK</h2>

    <a href="{{ route('dashboard') }}">Dashboard</a>

    <a href="{{ route('services') }}">Monitoring Service</a>

    <a href="{{ route('smoke') }}">Smoke Detector</a>

    <a href="{{ route('contacts') }}">Contacts</a>
</div>

<div class="main">
    @yield('content')
</div>

</body>
</html>