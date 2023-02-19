<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Category;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use MyHelper;

    function list($id = NULL)
    {

        try {
            if ($id) {
                $Category = Category::where('id', $id)->first();
                if ($Category->status == 0) {
                    return response()->json([
                        'code' => 406,
                        'status' => false,
                        'msg' => 'Category ' . $Category->name . ' is not active',
                        'data' => $Category,
                        'error' => 1
                    ], 406);
                }

                if (empty($Category)) {
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'msg' => 'Category not found',
                        'error' => 1
                    ], 404);
                }
            } else {
                $Category = Category::where('status', 10)->get();

                if (count($Category) == 0) {
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'msg' => 'Category is empty',
                        'error' => 1
                    ], 404);
                }
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Category is already',
                'data' => $Category,
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
            'icon' => 'required',
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
            $icon = request()->icon;
            $slug = Str::replace(" ", "_", Str::lower(request()->name));

            $master_photo = $this->uploadPhoto($icon, null, 'category');
            if ($master_photo == false) {
                return response()->json([
                    'code' => 422,
                    'status' => false,
                    'msg' => 'format photo is invalid',
                    'error' => 1
                ], 422);
            }

            $check = Category::where('slug', $slug)->first();
            if ($check) {
                return response()->json([
                    'code' => 419,
                    'status' => false,
                    'msg' => 'Oopss... Data is available',
                    'data' => $check,
                    'error' => 1
                ], 419);
            }

            $insert = Category::create([
                'name' => $name,
                'icon' => $master_photo,
                'slug' => $slug,
                'status' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'Category created',
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


            $check = Category::where('id', $id)->first();
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
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Category updated',
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
            'icon' => 'required',
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
            $icon = request()->icon;

            $check = Category::where('id', $id)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }

            $master_photo = $this->uploadPhoto($icon, $check->id, 'category');
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
                'msg' => 'Category updated',
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

            $check = Category::where('id', $id)->first();
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
                'msg' => 'Category updated',
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

            $check = Category::where('id', $id)->first();
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