<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\PaymentMethod;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;

class PaymentMethodController extends Controller
{
    use MyHelper;

    function list($id = NULL)
    {

        try {
            if ($id) {
                $PaymentMethod = PaymentMethod::where('id', $id)->first();
                if ($PaymentMethod->status == 0) {
                    return response()->json([
                        'code' => 409,
                        'status' => false,
                        'msg' => 'Payment Method ' . $PaymentMethod->name . ' is not active',
                        'data' => $PaymentMethod,
                        'error' => 1
                    ], 409);
                }

                if (empty($PaymentMethod)) {
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'msg' => 'Payment Method not found',
                        'error' => 1
                    ], 404);
                }
            } else {
                $PaymentMethod = PaymentMethod::where('status', 10)->get();

                if (count($PaymentMethod) == 0) {
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'msg' => 'Payment Method is empty',
                        'error' => 1
                    ], 404);
                }
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Payment Method is already',
                'data' => $PaymentMethod,
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
            'name' => 'required|string',
            'photo' => 'required',
            'payment_type' => 'required'
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
            $name = Str::upper(request()->name);
            $photo = request()->photo;
            $payment_type = request()->payment_type;
            $payment_type = Str::replace(" ", "_", Str::lower($payment_type));
            $slug = Str::replace(" ", "_", Str::lower(request()->name));

            $master_photo = $this->uploadPhoto($photo, null, 'payment_method');
            if ($master_photo == false) {
                return response()->json([
                    'code' => 422,
                    'status' => false,
                    'msg' => 'format photo is invalid',
                    'error' => 1
                ], 422);
            }

            $check = PaymentMethod::where('slug', $slug)->first();
            if ($check) {
                return response()->json([
                    'code' => 409,
                    'status' => false,
                    'msg' => 'Oopss... Data is available',
                    'data' => $check,
                    'error' => 1
                ], 409);
            }

            $insert = PaymentMethod::create([
                'code' => $this->randomNumber(),
                'name' => $name,
                'photo' => $master_photo,
                'slug' => $slug,
                'payment_type' => $payment_type,
                'status' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'Payment Method created',
                'data' => $insert,
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

    function update()
    {
        $validator = Validator::make(request()->all(), [
            'id' => 'required|integer',
            'name' => 'required|string',
            'payment_type' => 'required',
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
            $id = request()->id;
            $name = Str::upper(request()->name);
            $slug = Str::replace(" ", "_", Str::lower(request()->name));
            $payment_type = request()->payment_type;
            $payment_type = Str::replace(" ", "_", Str::lower($payment_type));


            $check = PaymentMethod::where('id', $id)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }

            $check->update([
                'name' => $name,
                'slug' => $slug,
                'payment_type' => $payment_type,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Payment Method updated',
                'data' => $check,
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

    function updatePhoto()
    {
        $validator = Validator::make(request()->all(), [
            'id' => 'required|integer',
            'photo' => 'required',
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
            $id = request()->id;
            $photo = request()->photo;

            $check = PaymentMethod::where('id', $id)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }

            $master_photo = $this->uploadPhoto($photo, $check->id, 'payment_method');
            if ($master_photo == false) {
                return response()->json([
                    'code' => 422,
                    'status' => false,
                    'msg' => 'format photo is invalid',
                    'error' => 1
                ], 422);
            }

            $check->update([
                'photo' => $master_photo,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Payment Method updated',
                'data' => $check,
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

    function updateStatus()
    {
        $validator = Validator::make(request()->all(), [
            'id' => 'required|integer',
            'status' => 'required|integer',
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
            $id = request()->id;
            $status = request()->status;

            $check = PaymentMethod::where('id', $id)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }

            $check->update([
                'status' => $status,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'PaymentMethod updated',
                'data' => $check,
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
            $id = request()->id;

            $check = PaymentMethod::where('id', $id)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }

            $this->deletePhoto($check->icon);
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
