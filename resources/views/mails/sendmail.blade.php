@extends('mails.layout_product')

@section('content_email')
<div style="margin-top: 30px !important;margin-bottom: 20px">

    <div style="margin-top: 20px; padding: 20px 20px; border-bottom: 2px solid #fff;background: #F2F2F2; color: #5B5B5B">
        <h4 style="text-align:center; font-size: 18px; color: #000">
            {{ $data['title'] }}
        </h4>
        <p style="text-align:left; color: #000; font-size:16px">
            {{ $data['deskripsi'] }}
        </p>
        <hr />
        <p style="color: #000 !important; text-align:left; font-size:16px">
            Here are the product details:
        </p>
        <p style="color: #000 !important; text-align:left; font-size:16px">
            <ul>
                <li>
                    Name : {{ $data['name'] }}
                </li>
                <li>
                    Price : {{ $data['price'] }}
                </li>
                <li>
                    Discount : {{ $data['discount'] }}
                </li>
                <li>
                    Stocks : {{ $data['stocks'] }}
                </li>
            </ul>

            <center>
                <a href="{{ $data['link'] }}" class="btn-custom" target="_blank">
                    View
                </a>
            </center>
        </p>

        <br />

        <div style='margin-top: 10px'>
            <p style='text-align:center; color: #000 !important'>
                This message is an automated message from {{ config('app.brand') }}
            </p>
        </div>
    </div>
    <br />
</div>
@endsection

@section('footer')
<p style='text-align:center'>
    Best Regards, <br /> <b>{{ config('app.brand') }}</b>
</p>
@endsection
