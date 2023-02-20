<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Store;
use App\Models\Api\StoreDetail;
use App\Models\User;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    use MyHelper;

    function list($id = NULL)
    {
        try {
            if ($id) {
                $Store = Store::where('id', $id)->first();
                if ($Store->status == 0) {
                    return response()->json([
                        'code' => 409,
                        'status' => false,
                        'msg' => 'Store ' . $Store->name . ' is not active',
                        'data' => $Store,
                        'error' => 1
                    ], 409);
                }

                if (empty($Store)) {
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'msg' => 'Store not found',
                        'error' => 1
                    ], 404);
                }
            } else {
                $Store = Store::where('status', 10)->get();

                if (count($Store) == 0) {
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'msg' => 'Store is empty',
                        'error' => 1
                    ], 404);
                }
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Store is already',
                'data' => $Store,
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
            'user_id' => 'required|integer',
            'name' => 'required|string',
            'photo' => 'required',
            'subject' => 'required',
            'description' => 'required'
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
            $user_id = request()->user_id;
            $name = Str::upper(request()->name);
            $photo = request()->photo;
            $slug = Str::replace(" ", "_", Str::lower(request()->name));
            $subject_store = request()->subject;
            $description_store = request()->description;

            $check_user = User::where('id', $user_id)->first();
            if (empty($check_user)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... User not found',
                    'error' => 1
                ], 404);
            }

            $check = Store::where('slug', $slug)->where('user_id', $user_id)->first();
            if ($check) {
                return response()->json([
                    'code' => 409,
                    'status' => false,
                    'msg' => 'Oopss... Data is available',
                    'data' => $check,
                    'error' => 1
                ], 409);
            }

            $master_photo = $this->uploadPhoto($photo, null, 'store');
            if ($master_photo == false) {
                return response()->json([
                    'code' => 422,
                    'status' => false,
                    'msg' => 'format photo is invalid',
                    'error' => 1
                ], 422);
            }

            $insert = Store::create([
                'user_id' => $user_id,
                'name' => $name,
                'photo' => $master_photo,
                'slug' => $slug,
                'status' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $detail = StoreDetail::create([
                'id_store' => $insert->id,
                'subject_store' => $subject_store,
                'description_store' => $description_store,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'Store created',
                'data' => [
                    'store' => $insert,
                    'detail' => $detail,
                    'user' => $check_user
                ],
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
            $subject = request()->subject;
            $description = request()->description;

            $check = Store::where('id', $id)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }

            $check_detail = StoreDetail::where('id_store', $check->id)->first();
            if (empty($check_detail)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Store Detail not found',
                    'error' => 1
                ], 404);
            }

            if ($subject) {
                $subject_store = $subject;
            } else {
                $subject_store = $check_detail->subject_store;
            }

            if ($description) {
                $description_store = $description;
            } else {
                $description_store = $check_detail->description_store;
            }

            $check_user = User::where('id', $check->user_id)->first();
            if (empty($check_user)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... User not found',
                    'error' => 1
                ], 404);
            }

            $check->update([
                'name' => $name,
                'slug' => $slug,
                'updated_at' => now()
            ]);

            $check_detail->update([
                'subject_store' => $subject_store,
                'description_store' => $description_store,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Store updated',
                'data' => [
                    'store' => $check,
                    'detail' => $check_detail,
                    'user' => $check_user
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

            $check = Store::where('id', $id)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }

            $master_photo = $this->uploadPhoto($photo, $check->id, 'store');
            if ($master_photo == false) {
                return response()->json([
                    'code' => 422,
                    'status' => false,
                    'msg' => 'format photo is invalid',
                    'error' => 1
                ], 422);
            }

            $check->update([
                'icon' => $master_photo,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Store updated',
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

            $check = Store::where('id', $id)->first();
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
                'msg' => 'Store updated',
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

            $check = Store::where('id', $id)->first();
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
