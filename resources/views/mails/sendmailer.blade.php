@extends('mails.layout')

@section('content')

<h1>{{ $data['title'] }}</h1>
<h4 style="color: white important!">Hi, {{ $data['emailto'] }}</h4>

Terima kasih telah mendaftarkan diri kamu.
<br>
<div style="color: white important!">
    {!! $data['body'] !!}
</div>


@endsection

@section('footer')
<p style='text-align:center'>
    Salam hangat, Team Ayoscan App
</p>
@endsection
