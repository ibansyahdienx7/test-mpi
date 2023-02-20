<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SentsMail;
use App\Models\Api\Product;
use App\Models\Api\ProductDetail;
use App\Models\Api\Review;
use App\Models\Api\SizeProduct;
use App\Models\Api\Store;
use App\Models\Api\Subscribe;
use App\Models\Api\VariantProduct;
use App\Models\User;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use MyHelper;

    function list($id_user = NULL)
    {
        try {
            if ($id_user) {
                $Product = Product::select(
                    'stores.user_id as id_user',
                    'stores.id as id_store',
                    'stores.photo as photo_store',
                    'stores.slug as slug_store',
                    'products.id as id_product',
                    'categories.name',
                    'products.name',
                    'products.id_category as id_category',
                    'products.stocks',
                    'products.photo',
                    'products.second_photo',
                    'products.third_photo',
                    'products.price',
                    'products.discount',
                    'products.size as status_size',
                    'size_products.size',
                    'products.variant as status_variant',
                    'variant_products.variant',
                    'products.real_price',
                    'products.slug',
                    'products.status',
                    'products.created_at as product_created_at',
                    'products.updated_at as product_updated_at',
                    'reviews.rate as rate_product',
                )
                    ->leftJoin('size_products', 'products.id', '=', 'size_products.id_product')
                    ->leftJoin('stores', 'products.id_store', '=', 'stores.id')
                    ->leftJoin('variant_products', 'products.id', '=', 'variant_products.id_product')
                    ->leftJoin('categories', 'products.id_category', '=', 'categories.id')
                    ->leftJoin('reviews', 'products.id', '=', 'reviews.id_product')
                    ->whereNotIn('stores.user_id', [$id_user])
                    ->where('products.stocks', '>', 0)
                    ->where('products.status', 10)
                    ->groupBy(
                        'stores.user_id',
                        'stores.id',
                        'stores.photo',
                        'stores.slug',
                        'products.id',
                        'categories.name',
                        'products.name',
                        'products.id_category',
                        'products.stocks',
                        'products.photo',
                        'products.second_photo',
                        'products.third_photo',
                        'products.price',
                        'products.discount',
                        'products.size',
                        'size_products.size',
                        'products.variant',
                        'variant_products.variant',
                        'products.real_price',
                        'products.slug',
                        'products.status',
                        'products.created_at',
                        'products.updated_at',
                        'reviews.rate',
                    )
                    ->get();

                if (count($Product) == 0) {
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'msg' => 'Product not found',
                        'error' => 1
                    ], 404);
                }
            } else {
                $Product = Product::select(
                    'stores.user_id as id_user',
                    'stores.id as id_store',
                    'stores.photo as photo_store',
                    'stores.slug as slug_store',
                    'products.id as id_product',
                    'categories.name',
                    'products.name',
                    'products.id_category as id_category',
                    'products.stocks',
                    'products.photo',
                    'products.second_photo',
                    'products.third_photo',
                    'products.price',
                    'products.discount',
                    'products.size as status_size',
                    'size_products.size',
                    'products.variant as status_variant',
                    'variant_products.variant',
                    'products.real_price',
                    'products.slug',
                    'products.status',
                    'products.created_at as product_created_at',
                    'products.updated_at as product_updated_at',
                    'reviews.rate as rate_product',
                )
                    ->leftJoin('size_products', 'products.id', '=', 'size_products.id_product')
                    ->leftJoin('stores', 'products.id_store', '=', 'stores.id')
                    ->leftJoin('variant_products', 'products.id', '=', 'variant_products.id_product')
                    ->leftJoin('categories', 'products.id_category', '=', 'categories.id')
                    ->leftJoin('reviews', 'products.id', '=', 'reviews.id_product')
                    ->where('products.status', 10)
                    ->where('products.stocks', '>', 0)
                    ->groupBy(
                        'stores.user_id',
                        'stores.id',
                        'stores.photo',
                        'stores.slug',
                        'products.id',
                        'categories.name',
                        'products.name',
                        'products.id_category',
                        'products.stocks',
                        'products.photo',
                        'products.second_photo',
                        'products.third_photo',
                        'products.price',
                        'products.discount',
                        'products.size',
                        'size_products.size',
                        'products.variant',
                        'variant_products.variant',
                        'products.real_price',
                        'products.slug',
                        'products.status',
                        'products.created_at',
                        'products.updated_at',
                        'reviews.rate',
                    )->get();

                if (count($Product) == 0) {
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'msg' => 'Product is empty',
                        'error' => 1
                    ], 404);
                }
            }

            $x = 0;
            foreach ($Product as $p) {
                $review = Review::where('id_product', $p->id_product)->first();
                if ($review) {
                    if (count($review) > 5) {
                        $total_rate = $review->rate / 5;
                    } else {
                        $total_rate = $review->rate;
                    }

                    $total_rate_store = $total_rate;
                } else {
                    $total_rate_store = 0;
                }

                $Product[$x]->total_rate_store = $total_rate_store;
                $Product[$x]->rate_product = $review ? $review->rate : 0;
                $x++;
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Product is already',
                'data' => $Product,
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

    function listBySlug($slug)
    {
        try {
            $Product = Product::select(
                'stores.user_id as id_user',
                'stores.id as id_store',
                'stores.photo as photo_store',
                'stores.slug as slug_store',
                'products.id as id_product',
                'categories.name',
                'products.name',
                'products.id_category as id_category',
                'products.stocks',
                'products.photo',
                'products.second_photo',
                'products.third_photo',
                'products.price',
                'products.discount',
                'products.slug as slug_product',
                'products.size as status_size',
                'size_products.size',
                'products.variant as status_variant',
                'variant_products.variant',
                'products.real_price',
                'products.slug',
                'products.status',
                'products.created_at as product_created_at',
                'products.updated_at as product_updated_at',
                'reviews.rate as rate_product',
            )
                ->leftJoin('size_products', 'products.id', '=', 'size_products.id_product')
                ->leftJoin('stores', 'products.id_store', '=', 'stores.id')
                ->leftJoin('variant_products', 'products.id', '=', 'variant_products.id_product')
                ->leftJoin('categories', 'products.id_category', '=', 'categories.id')
                ->leftJoin('reviews', 'products.id', '=', 'reviews.id_product')
                ->where('products.slug', $slug)
                ->where('products.stocks', '>', 0)
                ->where('products.status', 10)
                ->groupBy(
                    'stores.user_id',
                    'stores.id',
                    'stores.photo',
                    'stores.slug',
                    'products.id',
                    'categories.name',
                    'products.name',
                    'products.id_category',
                    'products.stocks',
                    'products.photo',
                    'products.second_photo',
                    'products.third_photo',
                    'products.price',
                    'products.discount',
                    'products.slug',
                    'products.size',
                    'size_products.size',
                    'products.variant',
                    'variant_products.variant',
                    'products.real_price',
                    'products.slug',
                    'products.status',
                    'products.created_at',
                    'products.updated_at',
                    'reviews.rate',
                )
                ->first();
            if ($Product->status == 0) {
                return response()->json([
                    'code' => 417,
                    'status' => false,
                    'msg' => 'Product ' . $Product->name . ' is not active',
                    'data' => $Product,
                    'error' => 1
                ], 417);
            }

            if (empty($Product)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Product not found',
                    'error' => 1
                ], 404);
            }

            $review_store = Review::where('id_product', $Product->id_product)->first();
            if ($review_store) {
                $review_stores = Review::where('id_product', $Product->id_product)->get();
                if (count($review_stores) > 5) {
                    $total_rate = $review_store->rate / 5;
                } else {
                    $total_rate = $review_store->rate;
                }

                $total_rate_store = $total_rate;
            } else {
                $total_rate_store = 0;
            }

            $Product->total_rate_store = $total_rate_store;
            $Product->rate_product = $review_store ? $review_store->rate : 0;

            $review = Review::where('id_product', $Product->id_product)->get();

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Product is already',
                'data' => [
                    'product' => $Product,
                    'review' => count($review) == 0 ? NULL : $review,
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

    function listByToko($slug)
    {
        try {
            $Product = Product::select(
                'stores.user_id as id_user',
                'stores.id as id_store',
                'stores.photo as photo_store',
                'stores.slug as slug_store',
                'products.id as id_product',
                'categories.name',
                'products.name',
                'products.id_category as id_category',
                'products.stocks',
                'products.photo',
                'products.second_photo',
                'products.third_photo',
                'products.price',
                'products.discount',
                'products.size as status_size',
                'size_products.size',
                'products.variant as status_variant',
                'variant_products.variant',
                'products.real_price',
                'products.slug',
                'products.status',
                'products.created_at as product_created_at',
                'products.updated_at as product_updated_at',
                'reviews.rate as rate_product',
            )
                ->leftJoin('size_products', 'products.id', '=', 'size_products.id_product')
                ->leftJoin('stores', 'products.id_store', '=', 'stores.id')
                ->leftJoin('variant_products', 'products.id', '=', 'variant_products.id_product')
                ->leftJoin('categories', 'products.id_category', '=', 'categories.id')
                ->leftJoin('reviews', 'products.id', '=', 'reviews.id_product')
                ->where('stores.slug', $slug)
                ->where('products.stocks', '>', 0)
                ->where('products.status', 10)
                ->groupBy(
                    'stores.user_id',
                    'stores.id',
                    'stores.photo',
                    'stores.slug',
                    'products.id',
                    'categories.name',
                    'products.name',
                    'products.id_category',
                    'products.stocks',
                    'products.photo',
                    'products.second_photo',
                    'products.third_photo',
                    'products.price',
                    'products.discount',
                    'products.size',
                    'size_products.size',
                    'products.variant',
                    'variant_products.variant',
                    'products.real_price',
                    'products.slug',
                    'products.status',
                    'products.created_at',
                    'products.updated_at',
                    'reviews.rate',
                )
                ->get();

            if (count($Product) == 0) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Product is empty',
                    'error' => 1
                ], 404);
            }

            $x = 0;
            foreach ($Product as $p) {
                $review = Review::where('id_product', $p->id_product)->first();
                if ($review) {
                    if (count($review) > 5) {
                        $total_rate = $review->rate / 5;
                    } else {
                        $total_rate = $review->rate;
                    }

                    $total_rate_store = $total_rate;
                } else {
                    $total_rate_store = 0;
                }

                $Product[$x]->total_rate_store = $total_rate_store;
                $Product[$x]->rate_product = $review ? $review->rate : 0;
                $x++;
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Product is already',
                'data' => $Product,
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
            'id_store' => 'required|integer',
            'id_category' => 'required|integer',
            'name' => 'required|string',
            'stocks' => 'required|integer',
            'photo' => 'required',
            'price' => 'required|integer',
            'discount' => 'required|integer',
            'tag' => 'required',
            'note' => 'required',
            'description' => 'required',
            'share' => 'required|string'
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
            $id_store = (int)request()->id_store;
            $id_category = (int)request()->id_category;
            $stocks = (int)request()->stocks;
            $name = Str::upper(request()->name);
            $photo = request()->photo;
            $second_photo = request()->second_photo;
            $third_photo = request()->third_photo;
            $price = (int)request()->price;
            $discount = (int)request()->discount;
            $tag = request()->tag;
            $note = request()->note;
            $description = request()->description;
            $slug = Str::replace(" ", "_", Str::lower(request()->name));
            $size = request()->size;
            $variant = request()->variant;
            $broadcast = request()->share;

            if ($size !== NULL) {
                if (!preg_match("/[A-Z](?=,)/", $size)) {
                    return response()->json([
                        'code' => 400,
                        'status' => false,
                        'msg' => 'Example request size must be: L, M, etc. (there must be a comma in each word)',
                        'error' => 1
                    ], 400);
                }
            }

            if ($variant !== NULL) {
                if (!preg_match("/[A-Z](?=,)/", $variant)) {
                    return response()->json([
                        'code' => 400,
                        'status' => false,
                        'msg' => 'Example request variant must be: RED, GREEN, etc. (must have a comma and capital letter in each word)',
                        'error' => 1
                    ], 400);
                }
            }

            if (!preg_match("/[a-z](?=,)/", $tag)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Example request tag must be: #shirt, #pants, etc (there must be a fence in each word)',
                    'error' => 1
                ], 400);
            }

            if (!preg_match("/(?!true)/", $broadcast) || !preg_match("/(?!false)/", $broadcast)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Request broadcast true/false',
                    'error' => 1
                ], 400);
            }

            $check_store = Store::where('id', $id_store)->first();
            if (empty($check_store)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Store not found',
                    'error' => 1
                ], 404);
            }

            $check_user = User::where('id', $check_store->user_id)->first();
            if (empty($check_user)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... User not found',
                    'error' => 1
                ], 404);
            }

            $check = Product::where('slug', $slug)->first();
            if ($check) {
                return response()->json([
                    'code' => 409,
                    'status' => false,
                    'msg' => 'Oopss... Data is available',
                    'data' => $check,
                    'error' => 1
                ], 409);
            }

            if ($discount == 0 || $discount == NULL) {
                $cekHarga = $price;
                $harga_real = $price;
                $diskon = 0;
            } else {
                $cekHarga = ($discount / 100) * $price;
                $harga_real = $discount - $cekHarga;
                $diskon = $discount;
            }

            $master_photo = $this->uploadPhoto($photo, null, 'product');
            if ($master_photo == false) {
                return response()->json([
                    'code' => 422,
                    'status' => false,
                    'msg' => 'format master photo is invalid',
                    'error' => 1
                ], 422);
            }

            if ($second_photo !== NULL) {
                $seconds_photo = $this->uploadPhoto($second_photo, null, 'product');
                if ($seconds_photo == false) {
                    return response()->json([
                        'code' => 422,
                        'status' => false,
                        'msg' => 'format second photo is invalid',
                        'error' => 1
                    ], 422);
                }
            } else {
                $seconds_photo = NULL;
            }

            if ($third_photo !== NULL) {
                $thirds_photo = $this->uploadPhoto($third_photo, null, 'product');
                if ($thirds_photo == false) {
                    return response()->json([
                        'code' => 422,
                        'status' => false,
                        'msg' => 'format third photo is invalid',
                        'error' => 1
                    ], 422);
                }
            } else {
                $thirds_photo = NULL;
            }

            $insert = Product::create([
                'id_store' => $id_store,
                'id_category' => $id_category,
                'name' => $name,
                'stocks' => $stocks,
                'photo' => $master_photo,
                'second_photo' => $second_photo ? $seconds_photo : NULL,
                'third_photo' => $third_photo ? $thirds_photo : NULL,
                'price' => $price,
                'discount' => $diskon,
                'real_price' => $harga_real,
                'size' => $size ? 1 : 0,
                'variant' => $variant ? 1 : 0,
                'slug' => $slug,
                'status' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $detail = ProductDetail::create([
                'id_product' => $insert->id,
                'tag' => $tag,
                'note' => $note,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($size) {
                $insert_size = SizeProduct::create([
                    'id_product' => $insert->id,
                    'size' => $size,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $insert_size = NULL;
            }

            if ($variant) {
                $insert_variant = VariantProduct::create([
                    'id_product' => $insert->id,
                    'variant' => $variant,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $insert_variant = NULL;
            }

            if ($broadcast == true) {
                $cek_subscribe = Subscribe::get();
                if (count($cek_subscribe) > 0) {
                    foreach ($cek_subscribe as $s) {
                        $dataMail = [
                            'title_mail' => 'product',
                            'subject' => 'New Product! ' . $name . ' ' . $this->mataUang() . ' ' . number_format($harga_real, 0, '.', '.') . ' | ' . $check_store->name,
                            'title' => 'New Product! ' . $name . ' ' . $this->mataUang() . ' ' . number_format($harga_real, 0, '.', '.') . ' | ' . $check_store->name,
                            'deskripsi' => 'Hi ' . $s->email . ', There is a new product from ' . $check_store->name . ' - ' . $name . ' only ' . $this->mataUang() . ' ' . number_format($harga_real, 0, '.', '.'),
                            'name' => $name,
                            'price' => $this->mataUang() . ' ' . number_format($harga_real, 0, '.', '.'),
                            'discount' => $discount . '%',
                            'photo' => $master_photo,
                            'stocks' => number_format($stocks, 0, '.', '.'),
                            'link' => config('app.url_olshop') . '/product/detail/' . $slug,
                            'emailto' => $s->email,
                            'email' => $s->email,
                            'role' => 'user'
                        ];

                        Mail::to($s->email)->send(new SentsMail($dataMail));
                    }
                }
            }

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'Product created',
                'data' => [
                    'Product' => $insert,
                    'Detail' => $detail,
                    'Size' => $insert_size,
                    'Variant' => $insert_variant,
                    'store' => $check_store,
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
            'id_category' => 'required|integer',
            'name' => 'required|string',
            'stocks' => 'required|integer',
            'price' => 'required|integer',
            'discount' => 'required|integer',
            'tag' => 'required',
            'note' => 'required',
            'description' => 'required',
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
            $id_category = (int)request()->id_category;
            $stocks = (int)request()->stocks;
            $name = Str::upper(request()->name);
            $price = (int)request()->price;
            $discount = (int)request()->discount;
            $tag = request()->tag;
            $note = request()->note;
            $description = request()->description;
            $slug = Str::replace(" ", "_", Str::lower(request()->name));
            $size = request()->size;
            $variant = request()->variant;

            if ($size !== NULL) {
                if (!preg_match("/[A-Z](?=,)/", $size)) {
                    return response()->json([
                        'code' => 400,
                        'status' => false,
                        'msg' => 'Example request size must be: L, M, etc. (there must be a comma in each word)',
                        'error' => 1
                    ], 400);
                }
            }

            if ($variant !== NULL) {
                if (!preg_match("/[A-Z](?=,)/", $variant)) {
                    return response()->json([
                        'code' => 400,
                        'status' => false,
                        'msg' => 'Example request variant must be: RED, GREEN, etc. (must have a comma and capital letter in each word)',
                        'error' => 1
                    ], 400);
                }
            }

            if (!preg_match("/[a-z](?=,)/", $tag)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Example request tag must be: #shirt, #pants, etc (there must be a fence in each word)',
                    'error' => 1
                ], 400);
            }

            $check = Product::where('id', $id)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }

            $product_detail = ProductDetail::where('id_product', $check->id)->first();
            if (empty($product_detail)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Product Detail not found',
                    'error' => 1
                ], 404);
            }

            $size_prd = SizeProduct::where('id_product', $check->id)->first();
            if (empty($size_prd)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Size Product not found',
                    'error' => 1
                ], 404);
            }

            $variant_prd = VariantProduct::where('id_product', $check->id)->first();
            if (empty($variant_prd)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Variant Product not found',
                    'error' => 1
                ], 404);
            }

            $check_store = Store::where('id', $check->id_store)->first();
            if (empty($check_store)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Store not found',
                    'error' => 1
                ], 404);
            }


            $check_user = User::where('id', $check_store->user_id)->first();
            if (empty($check_user)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... User not found',
                    'error' => 1
                ], 404);
            }


            if ($discount == 0 || $discount == NULL) {
                $cekHarga = $price;
                $harga_real = $price;
                $diskon = 0;
            } else {
                $cekHarga = ($discount / 100) * $price;
                $harga_real = $discount - $cekHarga;
                $diskon = $discount;
            }

            $check->update([
                'id_category' => $id_category,
                'name' => $name,
                'stocks' => $stocks,
                'price' => $price,
                'discount' => $diskon,
                'real_price' => $harga_real,
                'size' => $size ? 1 : 0,
                'variant' => $variant ? 1 : 0,
                'slug' => $slug,
                'status' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $product_detail->update([
                'id_product' => $check->id,
                'tag' => $tag,
                'note' => $note,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($size) {
                $size_prd->update([
                    'id_product' => $check->id,
                    'size' => $size,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $update_size = $size_prd;
            } else {
                $update_size = NULL;
            }

            if ($variant) {
                $variant_prd->update([
                    'id_product' => $check->id,
                    'variant' => $variant,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $update_variant = $variant_prd;
            } else {
                $update_variant = NULL;
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Product updated',
                'data' => [
                    'Product' => $check,
                    'Detail' => $product_detail,
                    'Size' => $update_size,
                    'Variant' => $update_variant,
                    'store' => $check_store,
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
            $second_photo = request()->second_photo;
            $third_photo = request()->third_photo;

            $check = Product::where('id', $id)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }

            if ($photo) {
                $master_photo = $this->uploadPhoto($photo, $check->id, 'product', 'master_photo');
                if ($master_photo == false) {
                    return response()->json([
                        'code' => 422,
                        'status' => false,
                        'msg' => 'format photo is invalid',
                        'error' => 1
                    ], 422);
                }
            } else {
                $master_photo = $check->photo;
            }

            if ($second_photo) {
                $seconds_photo = $this->uploadPhoto($second_photo, $check->id, 'product', 'second_photo');
                if ($seconds_photo == false) {
                    return response()->json([
                        'code' => 422,
                        'status' => false,
                        'msg' => 'format photo is invalid',
                        'error' => 1
                    ], 422);
                }
            } else {
                $seconds_photo = $check->second_photo;
            }

            if ($third_photo) {
                $thirds_photo = $this->uploadPhoto($third_photo, $check->id, 'product', 'third_photo');
                if ($thirds_photo == false) {
                    return response()->json([
                        'code' => 422,
                        'status' => false,
                        'msg' => 'format photo is invalid',
                        'error' => 1
                    ], 422);
                }
            } else {
                $thirds_photo = $check->third_photo;
            }

            $check->update([
                'photo' => $master_photo,
                'second_photo' => $seconds_photo,
                'third_photo' => $thirds_photo,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Product updated',
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
            $id = (int)request()->id;
            $status = (int)request()->status;

            $check = Product::where('id', $id)->first();
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
                'msg' => 'Product updated',
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
            $id = (int)request()->id;

            $check = Product::where('id', $id)->first();
            if (empty($check)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Oopss... Data not found',
                    'error' => 1
                ], 404);
            }

            $this->deletePhoto($check->photo);
            if ($check->second_photo) {
                $this->deletePhoto($check->second_photo);
            }

            if ($check->third_photo) {
                $this->deletePhoto($check->third_photo);
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
