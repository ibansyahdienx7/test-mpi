<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SentsAdminMail;
use App\Mail\SentsMail;
use App\Models\Api\Product;
use App\Models\Api\Review;
use App\Models\Api\Store;
use App\Models\User;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    use MyHelper;

    function list()
    {

        try {
            $Review = Review::select(
                'reviews.id as id_review',
                'reviews.id_product as id_product_review',
                'reviews.name as name_review',
                'reviews.rate as rate_review',
                'reviews.created_at as created_at',
                'reviews.updated_at as updated_at',
                'products.id as id_product',
                'products.name as name_product',
                'products.stocks as stocks_product',
                'products.photo as photo_product',
                'products.price as price_product',
                'products.discount as discount_product',
                'products.real_price as real_price_products',
                'products.slug as slug_products',
            )
                ->leftJoin('products', 'reviews.id_product', '=', 'products.id')
                ->groupBy(
                    'reviews.id',
                    'reviews.id_product',
                    'reviews.name',
                    'reviews.rate',
                    'reviews.created_at',
                    'reviews.updated_at',
                    'products.id',
                    'products.name',
                    'products.stocks',
                    'products.photo',
                    'products.price',
                    'products.discount',
                    'products.real_price',
                    'products.slug',
                )
                ->get();

            if (count($Review) == 0) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Review not found',
                    'error' => 1
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Review is already',
                'data' => $Review,
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
            'id_product' => 'required|integer',
            'user_id' => 'required|integer',
            'rate' => 'required|integer',
            'review' => 'required'
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
            $id_product = request()->id_product;
            $rate = request()->rate;
            $review = request()->review;

            if ($rate > 5) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Rate should be 1 - 5',
                    'error' => 1
                ], 400);
            }

            $cek_user = User::where('id', $user_id)->first();
            if (empty($cek_user)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User not found',
                    'error' => 1
                ], 404);
            }

            $cek_product = Product::where('id', $id_product)->first();
            if (empty($cek_product)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Product not found',
                    'error' => 1
                ], 404);
            }

            $cek_store = Store::where('id', $cek_product->id_store)->first();
            if (empty($cek_store)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Store not found',
                    'error' => 1
                ], 404);
            }

            $cek_user_store = User::where('id', $cek_store->user_id)->first();
            if (empty($cek_user_store)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User Store not found',
                    'error' => 1
                ], 404);
            }

            $check = Review::where('name', $cek_user->name)->where('id_product', $id_product)->first();
            if ($check) {
                return response()->json([
                    'code' => 409,
                    'status' => false,
                    'msg' => 'Review is available',
                    'data' => $check,
                    'error' => 1
                ], 409);
            }

            $Review = Review::create([
                'id_product' => $id_product,
                'name' => $cek_user->name,
                'rate' => $rate,
                'review' => $review,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($cek_user->email) {
                $dataMail = [
                    'title_mail' => 'review',
                    'subject' => 'Hi, ' . $cek_user->name . '. Thank you for review product ' . $cek_product->name,
                    'title' => 'Hi, ' . $cek_user->name . '. Thank you for review product ' . $cek_product->name,
                    'deskripsi' => 'Hi ' . $cek_user->name . ', Thank you for review product ' . $cek_product->name,
                    'rate' => $rate,
                    'review' => $review,
                    'emailto' => $cek_user->email,
                    'email' => $cek_user->email,
                    'time_ago' => date_format(date_create(now()), 'F, d Y H:i A'),
                    'role' => 'user'
                ];

                Mail::to($cek_user->email)->send(new SentsMail($dataMail));
            }

            $dataMail = [
                'title_mail' => 'review',
                'subject' => 'Hi, ' . $cek_user_store->name . '. ' . $cek_user->name . ' telah memberikan ulasan pada product ' . $cek_product->name . ' dengan rating ⭐ ' . $rate,
                'title' => 'Hi, ' . $cek_user_store->name . '. ' . $cek_user->name . ' telah memberikan ulasan pada product ' . $cek_product->name . ' dengan rating ⭐ ' . $rate,
                'deskripsi' => 'Hi, ' . $cek_user_store->name . '. ' . $cek_user->name . ' telah memberikan ulasan pada product ' . $cek_product->name . ' dengan rating ⭐ ' . $rate,
                'rate' => $rate,
                'review' => $review,
                'emailto' => $cek_user->email,
                'email' => $cek_user->email,
                'time_ago' => date_format(date_create(now()), 'F, d Y H:i A'),
                'role' => 'admin'
            ];

            Mail::to($cek_user_store->email)->send(new SentsAdminMail($dataMail));

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'Review created',
                'data' => $Review,
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

            $check = Review::where('id', $id)->first();
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
