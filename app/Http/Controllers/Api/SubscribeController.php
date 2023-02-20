<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SentsAdminMail;
use App\Mail\SentsMail;
use App\Models\Api\Subscribe;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;

class SubscribeController extends Controller
{
    use MyHelper;

    function list()
    {

        try {
            $Subscribe = Subscribe::get();

            if (count($Subscribe) == 0) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Subscribe not found',
                    'error' => 1
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Subscribe is already',
                'data' => $Subscribe,
                'error' => 0
            ], 200);
        } catch (HttpException $exception) {
            return response()->json([
                'code' => $exception->getstatusCode(),
                'status' => false,
                'msg' => $exception->getMessage(),
                'error' => 1,
                'error_detail' => [
                    'code' => $exception->getStatusCode(),
                    'headers' => $exception->getHeaders(),
                    'line' => $exception->getLine(),
                ]
            ], $exception->getstatusCode());
        }
    }

    function store()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
            'ip' => 'required',
        ]);

        if ($validator->fails()) {
            $json = [
                'retcode' => 422,
                'status' => false,
                'error' => 1,
                'error_detail' => $validator->errors()
            ];
            return response()->json($json, 422);
        }

        try {
            $email = Str::lower(request()->email);
            $ip = request()->ip;

            $check = Subscribe::where('email', $email)->first();
            if ($check) {
                return response()->json([
                    'code' => 409,
                    'status' => false,
                    'msg' => 'Subscribe is available',
                    'data' => $check,
                    'error' => 1
                ], 409);
            }

            $Subscribe = Subscribe::create([
                'email' => $email,
                'ip' => $ip,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($email) {
                $dataMail = [
                    'title_mail' => 'subscribe',
                    'subject' => 'Hi, ' . $email . '. Thank you for subscribing to our newsletter',
                    'title' => 'Hi, ' . $email . '. Thank you for subscribing to our newsletter',
                    'deskripsi' => 'Hi ' . $email . ', Thank you for subscribing to our newsletter, we will keep you updated.',
                    'ip' => $ip,
                    'emailto' => $email,
                    'email' => $email,
                    'time_ago' => date_format(date_create(now()), 'F, d Y H:i A'),
                    'role' => 'user'
                ];

                Mail::to($email)->send(new SentsMail($dataMail));
            }

            $dataMail = [
                'title_mail' => 'subscribe',
                'subject' => 'Hi, ' . config('app.email_supports') . '. ' . $email . ' telah berlangganan buletin',
                'title' => 'Hi, ' . config('app.email_supports') . '. ' . $email . ' telah berlangganan buletin',
                'deskripsi' => 'Hi, ' . config('app.email_supports') . '. ' . $email . ' telah berlangganan buletin',
                'ip' => $ip,
                'emailto' => $email,
                'email' => $email,
                'time_ago' => date_format(date_create(now()), 'F, d Y H:i A'),
                'role' => 'admin'
            ];

            Mail::to(config('app.email_supports'))->send(new SentsAdminMail($dataMail));

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'Subscribe created',
                'data' => $Subscribe,
                'error' => 0
            ], 201);
        } catch (HttpException $exception) {
            return response()->json([
                'code' => $exception->getstatusCode(),
                'status' => false,
                'msg' => $exception->getMessage(),
                'error' => 1,
                'error_detail' => [
                    'code' => $exception->getStatusCode(),
                    'headers' => $exception->getHeaders(),
                    'line' => $exception->getLine(),
                ]
            ], $exception->getstatusCode());
        }
    }

    function delete()
    {
        $validator = Validator::make(request()->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $json = [
                'retcode' => 422,
                'status' => false,
                'error' => 1,
                'error_detail' => $validator->errors()
            ];
            return response()->json($json, 422);
        }

        try {
            $id = (int)request()->id;

            $check = Subscribe::where('id', $id)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }

            $check->delete();

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Delete Success',
                'error' => 0
            ], 200);
        } catch (HttpException $exception) {
            return response()->json([
                'code' => $exception->getstatusCode(),
                'status' => false,
                'msg' => $exception->getMessage(),
                'error' => 1,
                'error_detail' => [
                    'code' => $exception->getStatusCode(),
                    'headers' => $exception->getHeaders(),
                    'line' => $exception->getLine(),
                ]
            ], $exception->getstatusCode());
        }
    }
}
