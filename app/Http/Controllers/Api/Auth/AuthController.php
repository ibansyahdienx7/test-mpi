<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SentsAdminMail;
use App\Mail\SentsMail;
use App\Models\User;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

            if (preg_match("/^[a-zA-Z]*$/", $name)) {
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
                ], 419);
            }

            $insert = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'slug' => $slug,
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($email) {
                $dataMail = [
                    'title_mail' => 'regist',
                    'subject' => 'Hi, ' . $name . '. Verify Your Account Now, To Make Shopping',
                    'title' => 'Account Verification',
                    'deskripsi' => 'Hi ' . $name . ', Verify Your Account Now, To Make Shopping',
                    'name' => $name,
                    'emailto' => $email,
                    'email' => $email,
                    'role' => 'user'
                ];

                Mail::to($email)->send(new SentsMail($dataMail));
            }

            $dataMail = [
                'title_mail' => 'regist',
                'subject' => 'Pengguna Baru ' . $name,
                'title' => 'Pengguna Baru ' . $name,
                'deskripsi' => 'Halo ' . config('app.brand') . ', Ada Pengguna Baru Dari ' . $name . '.',
                'name' => $name,
                'emailto' => $email,
                'email' => $email,
                'role' => 'admin'
            ];

            Mail::to('ibansyahdienx7@gmail.com')->send(new SentsAdminMail($dataMail));

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'User created',
                'data' => $insert,
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

    function verify()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
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
            $check = User::where('email', $email)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }

            $check->update([
                'status' => 10,
                'email_verified_at' => now(),
                'updated_at' => now()
            ]);

            if ($email) {
                $dataMail = [
                    'title_mail' => 'verify',
                    'subject' => 'Hi, ' . $check->name . '. Verify Successfully',
                    'title' => 'Account Verification Successfully',
                    'deskripsi' => 'Hi ' . $check->name . ', Your account has been successfully verified, now you can shop safely',
                    'name' => $check->name,
                    'emailto' => $email,
                    'email' => $email,
                    'role' => 'user'
                ];

                Mail::to($email)->send(new SentsMail($dataMail));
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Verify has successfully',
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
}
