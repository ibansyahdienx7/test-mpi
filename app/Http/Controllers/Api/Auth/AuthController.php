<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SentsAdminMail;
use App\Mail\SentsMail;
use App\Models\Api\Cart;
use App\Models\Api\Invoices;
use App\Models\Api\PasswordResets;
use App\Models\Api\Store;
use App\Models\Api\Transaction;
use App\Models\Api\VaUser;
use App\Models\User;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File as FacadesFile;

class AuthController extends Controller
{
    use MyHelper;

    function list($id = NULL)
    {
        try {
            if ($id) {
                $user = User::where('id', $id)->first();
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
                        'code' => 404,
                        'status' => false,
                        'msg' => 'User not found',
                        'error' => 1
                    ], 404);
                }
            } else {
                $user = User::get();

                if (count($user) == 0) {
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'msg' => 'User is empty',
                        'error' => 1
                    ], 404);
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
                'pzn' => $pzn,
                'role' => $role,
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

    function update_pass()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
            'old_password' => 'required',
            'new_password' => 'required'
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
            $old_password = request()->old_password;
            $new_password = request()->new_password;
            $pzn = $this->encryptPin($new_password);

            $check = User::where('email', $email)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }

            if (!Hash::check($old_password, $check->password)) {
                return response()->json([
                    'code' => 406,
                    'status' => false,
                    'msg' => 'Oopss... Passwords don`t match',
                    'error' => 1
                ], 406);
            }

            $check->update([
                'password' => Hash::make($new_password),
                'pzn' => $pzn,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 202,
                'status' => true,
                'msg' => 'Password changed successfully',
                'data' => $check,
                'error' => 0
            ], 202);
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

    function forgot()
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

            $token = md5($this->unix_time());
            $checkPass = PasswordResets::where('email', $email)->first();
            if ($checkPass) {
                $checkPass->update([
                    'email' => $email,
                    'token' => $token,
                    'updated_at' => now()
                ]);
            } else {
                PasswordResets::create([
                    'email' => $email,
                    'token' => $token,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            if ($email) {
                $dataMail = [
                    'title_mail' => 'forgot',
                    'subject' => 'Forgot Password',
                    'title' => 'Forgot Password',
                    'deskripsi' => 'Hi ' . $check->name . ', it looks like someone wants to change your password, if this is true, please click the reset password button to change your password. And if not you please ignore this message.',
                    'name' => $check->name,
                    'emailto' => $email,
                    'email' => $email,
                    'token' => $token,
                    'role' => 'user'
                ];

                Mail::to($email)->send(new SentsMail($dataMail));
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Completed, check your email',
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

    function checkTokenResetPass()
    {
        $validator = Validator::make(request()->all(), [
            'token' => 'required',
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
            $token = request()->token;
            $checkPass = PasswordResets::where('token', $token)->first();
            if (empty($checkPass)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }

            return response()->json([
                'code' => 202,
                'status' => true,
                'msg' => 'Data found',
                'data' => $checkPass,
                'error' => 0
            ], 202);
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

    function resetPassAuth()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
            'new_password' => 'required'
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
            $new_password = request()->new_password;
            $pzn = $this->encryptPin($new_password);

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
                'password' => Hash::make($new_password),
                'pzn' => $pzn,
                'updated_at' => now()
            ]);

            if ($email) {
                $dataMail = [
                    'title_mail' => 'verify',
                    'subject' => 'Reset Password Has Been Changed',
                    'title' => 'Reset Password Has Been Changed',
                    'deskripsi' => 'Hi ' . $check->name . ', Password changed successfully',
                    'name' => $check->name,
                    'emailto' => $email,
                    'email' => $email,
                    'role' => 'user'
                ];

                Mail::to($email)->send(new SentsMail($dataMail));
            }

            return response()->json([
                'code' => 202,
                'status' => true,
                'msg' => 'Password changed successfully',
                'data' => $check,
                'error' => 0
            ], 202);
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

    function checkPass()
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

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Completed',
                'data' => [
                    'password' => $this->decryptPin($check->pzn),
                    'user' => $check
                ],
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

    function upload_photo()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
            'photo_profile' => 'required',
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
            $foto = request()->photo_profile;

            $check = User::where('email', $email)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }
            // master photo
            $master_photo = $this->uploadPhoto($foto, $check->email, 'user');
            if ($master_photo == false) {
                return response()->json([
                    'code' => 422,
                    'status' => false,
                    'msg' => 'format photo is invalid',
                    'error' => 1
                ], 422);
            }

            $check->update([
                'profile_photo_path' => $master_photo,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Upload Successfully',
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

            // DELETE TRANSACTION //
            $transaction = Transaction::where('user_id', $check->id)->get();
            if (count($transaction) > 0) {
                $transaction->delete();
            }

            // DELETE VA USER //
            $va_user = VaUser::where('user_id', $check->id)->get();
            if (count($va_user) > 0) {
                $va_user->delete();
            }

            // DELETE STORE //
            $store = Store::where('user_id', $check->id)->first();
            if ($store) {
                $store->delete();
            }

            // DELETE INVOICE //
            $inv = Invoices::where('user_id', $check->id)->get();
            if (count($inv) > 0) {
                $inv->delete();
            }

            // DELETE CART //
            $cart = Cart::where('user_id', $check->id)->get();
            if (count($cart) > 0) {
                $cart->delete();
            }

            $this->deletePhoto($check->profile_photo_path);
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

    function updateStatus()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
            'status' => 'required|integer'
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
            $status = request()->status;

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
                'status' => $status,
                'updated_at' => now()
            ]);

            if ($status == 10) {
                $subject = 'Your account has been reactivated by the system';
                $title = 'Your account has been reactivated by the system';
            } else {
                $subject = 'Your account has been deactivated by the system';
                $title = 'Your account has been deactivated by the system';
            }

            if ($email) {
                $dataMail = [
                    'title_mail' => 'verify',
                    'subject' => $subject,
                    'title' => $title,
                    'deskripsi' => 'Hi ' . $check->name . ', ' . $title,
                    'name' => $check->name,
                    'emailto' => $email,
                    'email' => $email,
                    'role' => 'user'
                ];

                Mail::to($email)->send(new SentsMail($dataMail));
            }

            return response()->json([
                'code' => 202,
                'status' => true,
                'msg' => 'Update Status Successfully',
                'data' => $check,
                'error' => 0
            ], 202);
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

    function updateRole()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
            'role' => 'required|string'
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
            $role = request()->role;

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
                'role' => $role,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 202,
                'status' => true,
                'msg' => 'Update Role Successfully',
                'data' => $check,
                'error' => 0
            ], 202);
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
