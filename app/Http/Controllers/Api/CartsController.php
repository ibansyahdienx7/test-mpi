<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Cart;
use App\Models\Api\Product;
use App\Models\Api\Store;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CartsController extends Controller
{
    use MyHelper;

    function list($user_id)
    {

        try {
            $Cart = Cart::select(
                'carts.id as id_cart',
                'users.name',
                'products.id as id_product',
                'products.name',
                'categories.name',
                'carts.amount_product',
                'carts.total_product',
                'carts.size as cart_size_product',
                'carts.variant as cart_variant_product',
                'stores.name as name_store',
                'stores.photo as photo_store',
                'products.photo',
                'products.second_photo',
                'products.third_photo',
                'products.price',
                'products.stocks as stocks_product',
                'products.discount',
                'size_products.size',
                'variant_products.variant',
                'products.real_price',
                'products.slug',
                'carts.created_at as created_at_cart',
                'carts.updated_at as updated_at_cart'
            )
                ->leftJoin('users', 'carts.user_id', '=', 'users.id')
                ->leftJoin('products', 'carts.id_product', '=', 'products.id')
                ->leftJoin('stores', 'products.id_store', '=', 'stores.id')
                ->leftJoin('size_products', 'products.id', '=', 'size_products.id_product')
                ->leftJoin('variant_products', 'products.id', '=', 'variant_products.id_product')
                ->leftJoin('categories', 'products.id_category', '=', 'categories.id')
                ->where('carts.user_id', $user_id)
                ->groupBy(
                    'carts.id',
                    'users.name',
                    'products.id',
                    'products.name',
                    'categories.name',
                    'carts.amount_product',
                    'carts.total_product',
                    'carts.size',
                    'carts.variant',
                    'stores.name',
                    'stores.photo',
                    'products.photo',
                    'products.second_photo',
                    'products.third_photo',
                    'products.price',
                    'products.discount',
                    'products.stocks',
                    'size_products.size',
                    'variant_products.variant',
                    'products.real_price',
                    'products.slug',
                    'carts.created_at',
                    'carts.updated_at'
                )
                ->get();

            if (count($Cart) == 0) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Cart not found',
                    'error' => 1
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Cart is already',
                'data' => $Cart,
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

            $check = Cart::where('user_id', $user_id)->where('id_product', $id_product)->first();
            if ($check) {
                return response()->json([
                    'code' => 409,
                    'status' => false,
                    'msg' => 'Cart is available',
                    'data' => $check,
                    'error' => 1
                ], 409);
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
                    'msg' => 'You cannot add a cart to your own shop',
                    'error' => 1
                ], 417);
            }

            $cart = Cart::create([
                'user_id' => $user_id,
                'id_product' => $id_product,
                'amount_product' => $product->real_price * 1,
                'total_product' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'Cart created',
                'data' => $cart,
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

    function minusCart()
    {
        $validator = Validator::make(request()->all(), [
            'id' => 'required|integer',
            'value' => 'required|integer'
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
            $value = (int)request()->value;

            $check = Cart::where('id', $id)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Cart not found',
                    'error' => 1
                ], 404);
            }

            $product = Product::where('id', $check->id_product)->first();
            if (empty($product)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Product not found',
                    'error' => 1
                ], 404);
            }

            if ($product->stocks < 1) {
                return response()->json([
                    'code' => 417,
                    'status' => false,
                    'msg' => 'Stocks Empty',
                    'error' => 1
                ], 417);
            }

            if ($check->total_product < 1) {
                $check->delete();
            }

            $check->update([
                'amount_product' => $check->amount_product - $product->real_price,
                'total_product' => $check->total_product - $value,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Cart updated',
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

    function plusCart()
    {
        $validator = Validator::make(request()->all(), [
            'id' => 'required|integer',
            'value' => 'required|integer'
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
            $value = (int)request()->value;

            $check = Cart::where('id', $id)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Cart not found',
                    'error' => 1
                ], 404);
            }

            $product = Product::where('id', $check->id_product)->first();
            if (empty($product)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Product not found',
                    'error' => 1
                ], 404);
            }

            if ($product->stocks < 1) {
                return response()->json([
                    'code' => 417,
                    'status' => false,
                    'msg' => 'Stocks Empty',
                    'error' => 1
                ], 417);
            }

            $check->update([
                'amount_product' => $product->real_price + $check->amount_product * $value,
                'total_product' => $check->total_product + $value,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Cart updated',
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

    function update()
    {
        $validator = Validator::make(request()->all(), [
            'id' => 'required|integer',
            'size' => 'required',
            'variant' => 'required'
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
            $size = request()->size;
            $variant = request()->variant;

            $check = Cart::where('id', $id)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Cart not found',
                    'error' => 1
                ], 404);
            }

            $product = Product::where('id', $check->id_product)->first();
            if (empty($product)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Product not found',
                    'error' => 1
                ], 404);
            }

            if ($product->stocks < 1) {
                return response()->json([
                    'code' => 417,
                    'status' => false,
                    'msg' => 'Stocks Empty',
                    'error' => 1
                ], 417);
            }

            if ($size !== NULL) {
                if (!preg_match("/[A-Z]/", $size)) {
                    return response()->json([
                        'code' => 400,
                        'status' => false,
                        'msg' => 'Example request size must be: L / M / XL / XXL / etc',
                        'error' => 1
                    ], 400);
                }
            }

            if ($variant !== NULL) {
                if (!preg_match("/[A-Z]/", $variant)) {
                    return response()->json([
                        'code' => 400,
                        'status' => false,
                        'msg' => 'Example request variant must be: RED / GREEN / etc',
                        'error' => 1
                    ], 400);
                }
            }

            $check->update([
                'size' => $size,
                'variant' => $variant,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Cart updated',
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

            $check = Cart::where('id', $id)->first();
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
