@extends('mails.layout')

@section('content')

<h1>{{ $data['title'] }}</h1>
<h4>Hi, {{ $data['emailto'] }}</h4>

<div class="body-email">
    {!! $data['body'] !!}
</div>

@endsection

@section('footer')
<p style='text-align:center'>
    Salam hangat, Team Ayoscan App
</p>
@endsection
