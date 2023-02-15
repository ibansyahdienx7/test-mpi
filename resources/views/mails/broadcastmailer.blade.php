@extends('mails.layout')

@section('content_email')
    <div style="margin-top: 30px !important;margin-bottom: 20px">

        <div
            style="margin-top: 20px; padding: 20px 20px; border-bottom: 2px solid #fff;background: #F2F2F2; color: #5B5B5B">
            <h4 style="text-align:center; font-size: 18px; color: #000"> {{ $data['title'] }}
            </h4>
            <p style="text-align:left; color: #000; font-size:16px">
                Halo Kakak, <strong>{{ $data['emailto'] }}</strong>
            </p>
            <hr />
            <p style="color: #000 !important; text-align:left; font-size:16px">
                Terima kasih sudah menyempatkan waktu untuk melakukan konfirmasi kehadiran Ulang Tahun Gladisa Almahyra Putri 😚. Semoga kakak, om, tante sehat selalu ya,
                sampai bertemu nanti di hari Sabtu, 04 Februari 2023.
            </p>
            <p style="color: #000 !important; text-align:left; font-size:16px">
                Untuk melakukan konfirmasi bisa klik link berikut ya kak :

                <center>
                <a href="{{ route('souvenir', $data['token']) }}" class="btn-custom">
                    Konfirmasi Kehadiran
                </a>
                </center>
            </p>

            <br />

            <div style='margin-top: 10px'>
                <p style='text-align:center; color: #000 !important'>
                    Pesan ini adalah pesan otomatis dari Undangan Ulang Tahun Gladisa Almahyra Putri
                </p>
            </div>
        </div>
        <br />
    </div>
@endsection

@section('footer')
    <p style='text-align:center'>
        Salam Hangat, <br/> <b>Iban Syahdien Akbar & Keluarga</b>
    </p>
@endsection
