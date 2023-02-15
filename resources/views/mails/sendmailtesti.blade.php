@extends('mails.layout_testi')

@section('content_email')
    <div style="margin-top: 30px !important;margin-bottom: 20px">

        <div
            style="margin-top: 20px; padding: 20px 20px; border-bottom: 2px solid #fff;background: #F2F2F2; color: #5B5B5B">
            <h4 style="text-align:center; font-size: 18px; color: #000">
                {{ $data['title'] }}
            </h4>
            <p style="text-align:left; color: #000; font-size:16px">
                <strong>{{ $data['deskripsi'] }}</strong>
            </p>
            <hr />
            <p style="color: #000 !important; text-align:left; font-size:16px">
                Berikut rincian ulasan:
            </p>
            <p style="color: #000 !important; text-align:left; font-size:16px">
                <ul>
                    <li>
                        Nama : {{ $data['name'] }}
                    </li>
                    <li>
                        Company : {{ $data['company'] }}
                    </li>
                    <li>
                        Rating : ⭐ {{ $data['rating'] }} Star
                    </li>
                    <li>
                        Review : {{ $data['review'] }}
                    </li>
                </ul>

                <center>
                    <a href="{{ url('') }}" class="btn-custom" target="_blank">
                        ⭐ {{ $data['rating'] }} Star
                    </a>
                </center>
            </p>

            <br />

            <div style='margin-top: 10px'>
                <p style='text-align:center; color: #000 !important'>
                    This message is an automated message from Iban Syahdien Akbar
                </p>
            </div>
        </div>
        <br />
    </div>
@endsection

@section('footer')
    <p style='text-align:center'>
        Best Regards, <br/> <b>Iban Syahdien Akbar</b>
    </p>
@endsection
