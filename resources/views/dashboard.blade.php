@extends('layouts.app')

@section('content')

<div style="display:grid;
grid-template-columns:repeat(4,1fr);
gap:20px;">

    <div style="
    background:white;
    padding:20px;
    border-radius:15px;">
        <h3>Total Service</h3>
        <h1>0</h1>
    </div>

    <div style="
    background:white;
    padding:20px;
    border-radius:15px;">
        <h3>UP</h3>
        <h1>0</h1>
    </div>

    <div style="
    background:white;
    padding:20px;
    border-radius:15px;">
        <h3>WARNING</h3>
        <h1>0</h1>
    </div>

    <div style="
    background:white;
    padding:20px;
    border-radius:15px;">
        <h3>DOWN</h3>
        <h1>0</h1>
    </div>

</div>

@endsection