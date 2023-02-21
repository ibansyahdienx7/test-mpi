<?php

namespace App\Http\Controllers\Api\Callback;

use App\Http\Controllers\Controller;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    use MyHelper;

    function notif()
    {
        request()->headers->set('Accept', 'application/json');

        $payload = request()->all();
        Log::debug('debug', ['payload_callback' => $payload]);
    }
}
