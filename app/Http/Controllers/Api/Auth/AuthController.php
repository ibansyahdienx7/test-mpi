<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use MyHelper;

    function list()
    {
        $validator = Validator::make(request()->all(), [
            'id' => 'integer',
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
            if (request()->id) {
                $user = User::where('id', request()->id)->first();
                if ($user->status == 0) {
                    return response()->json([
                        'code' => 406,
                        'status' => false,
                        'msg' => 'User ' . $user->name . ' is not active',
                        'data' => $user,
                        'error' => 1
                    ], 406);
                }

                if (empty($user)) {
                    return response()->json([
                        'code' => 204,
                        'status' => false,
                        'msg' => 'User not found',
                        'error' => 1
                    ], 204);
                }
            } else {
                $user = User::get();

                if (count($user) == 0) {
                    return response()->json([
                        'code' => 204,
                        'status' => false,
                        'msg' => 'User is empty',
                        'error' => 1
                    ], 204);
                }
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'User is already',
                'data' => $user,
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

    function register()
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
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
            $email = Str::lower(request()->email);
            $password = request()->password;
            $pzn = $this->encryptPin($password);
            $slug = Str::replace(" ", "_", Str::lower(request()->name));
            $role = 'user';

            if (!preg_match("/^[a-zA-Z0-9]*$/", $password)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Password must contain capital letters, numbers, characters',
                    'error' => 1
                ], 400);
            }

            if (!preg_match("/^[a-zA-Z]*$/", $name)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only letters are allowed',
                    'error' => 1
                ], 400);
            }


            $check = User::where('email', $email)->orWhere('slug', $slug)->first();
            if ($check) {
                return response()->json([
                    'code' => 419,
                    'status' => false,
                    'msg' => 'Oopss... Data is available',
                    'data' => $check,
                    'error' => 1
                ], 400);
            }
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
