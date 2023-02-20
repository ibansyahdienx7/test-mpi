<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Product;
use App\Models\Api\Store;
use App\Models\Api\Wishlist;
use App\Models\User;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WishlistController extends Controller
{
    use MyHelper;

    function list($user_id)
    {

        try {
            $Wishlist = Wishlist::select(
                'wishlists.id as id_wishlist',
                'users.name',
                'products.name',
                'categories.name',
                'stores.name as name_store',
                'stores.photo as photo_store',
                'products.photo',
                'products.second_photo',
                'products.third_photo',
                'products.price',
                'products.discount',
                'size_products.size',
                'variant_products.variant',
                'products.real_price',
                'products.slug',
                'wishlists.created_at as created_at_wishlist',
                'wishlists.updated_at as updated_at_wishlist'
            )
                ->leftJoin('users', 'wishlists.user_id', '=', 'users.id')
                ->leftJoin('products', 'wishlists.id_product', '=', 'products.id')
                ->leftJoin('stores', 'products.id_store', '=', 'stores.id')
                ->leftJoin('size_products', 'products.id', '=', 'size_products.id_product')
                ->leftJoin('variant_products', 'products.id', '=', 'variant_products.id_product')
                ->leftJoin('categories', 'products.id_category', '=', 'categories.id')
                ->where('wishlists.user_id', $user_id)
                ->groupBy(
                    'wishlists.id',
                    'users.name',
                    'products.name',
                    'categories.name',
                    'stores.name',
                    'stores.photo',
                    'products.photo',
                    'products.second_photo',
                    'products.third_photo',
                    'products.price',
                    'products.discount',
                    'size_products.size',
                    'variant_products.variant',
                    'products.real_price',
                    'products.slug',
                    'wishlists.created_at',
                    'wishlists.updated_at'
                )
                ->get();

            if (count($Wishlist) == 0) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Wishlist not found',
                    'error' => 1
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Wishlist is already',
                'data' => $Wishlist,
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
            'id_product' => 'required|integer',
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
            $user_id = (int)request()->user_id;
            $id_product = (int)request()->id_product;

            $check = Wishlist::where('user_id', $user_id)->where('id_product', $id_product)->first();
            if ($check) {
                return response()->json([
                    'code' => 409,
                    'status' => false,
                    'msg' => 'Wishlist is available',
                    'data' => $check,
                    'error' => 1
                ], 409);
            }

            $user = User::where('id', $user_id)->first();
            if (empty($user)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User not found',
                    'error' => 1
                ], 404);
            }

            $product = Product::where('id', $id_product)->first();
            if (empty($product)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Product not found',
                    'error' => 1
                ], 404);
            }

            $store = Store::where('id', $product->id_store)->first();
            if (empty($store)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Store not found',
                    'error' => 1
                ], 404);
            }

            if ($user_id == $store->user_id) {
                return response()->json([
                    'code' => 417,
                    'status' => false,
                    'msg' => 'Sorry, you cannot wishlist with your own store',
                    'error' => 1
                ], 417);
            }

            $Wishlist = Wishlist::create([
                'user_id' => $user_id,
                'id_product' => $id_product,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'Wishlist created',
                'data' => [
                    'wishlist' => $Wishlist,
                    'user' => $user,
                    'product' => $product
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

            $check = Wishlist::where('id', $id)->first();
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
