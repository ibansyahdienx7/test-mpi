<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SentsAdminMail;
use App\Mail\SentsMail;
use App\Models\Api\Cart;
use App\Models\Api\Invoices;
use App\Models\Api\PaymentMethod;
use App\Models\Api\Product;
use App\Models\Api\Store;
use App\Models\Api\Transaction;
use App\Models\Api\TransactionDetail;
use App\Models\Api\VaUser;
use App\Models\User;
use App\Traits\MyHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    use MyHelper;

    function store()
    {
        $validator = Validator::make(request()->all(), [
            'user_id' => 'required|integer',
            'id_product' => 'required|integer',
            'id_payment' => 'required|integer',
            'subtotal' => 'required|integer',
            'id_cart' => 'required|integer'
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
            $id_payment = request()->id_payment;
            $subtotal = request()->subtotal;
            $id_cart = request()->id_cart;
            $admin_fee = 500;
            $tax = 11;
            $tax_price = ($subtotal * 11) / 100;

            $cek_user = User::where('id', $user_id)->where('status', 10)->first();
            if (empty($cek_user)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User not found',
                    'error' => 1
                ], 404);
            }

            $cek_cart = Cart::where('id', $id_cart)->where('user_id', $cek_user->id)->where('id_product', $id_product)->first();
            if (empty($cek_cart)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Cart not found',
                    'error' => 1
                ], 404);
            }

            $cek_product = Product::where('id', $id_product)->where('status', 10)->first();
            if (empty($cek_product)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Product not found',
                    'error' => 1
                ], 404);
            }

            $cek_store = Store::where('id', $cek_product->id_store)->where('status', 10)->first();
            if (empty($cek_store)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Store not found',
                    'error' => 1
                ], 404);
            }

            $cek_user_store = User::where('id', $cek_store->user_id)->where('status', 10)->first();
            if (empty($cek_user_store)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User Store not found',
                    'error' => 1
                ], 404);
            }

            $cek_payment = PaymentMethod::where('id', $id_payment)->where('status', 10)->first();
            if (empty($cek_payment)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Payment Method not found',
                    'error' => 1
                ], 404);
            }

            $order_id = 'OLI-' . $this->randomNumber();
            $now = now();
            $year = date('Y');
            $month = date('m');
            $day = date('d');

            $grand_total = $subtotal + $admin_fee + $tax_price;

            $alamat = "{$this->paymentMethod('charge')}";
            $client = new Client([
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic'
                ]
            ]);

            if ($cek_payment->payment_type == 'bank_transfer') {
                $request = $client->request('POST', $alamat, [
                    'auth' => [$this->paymentMethod('server_key_dev'),  $this->paymentMethod('server_key_dev')],
                    'json' => [
                        'payment_type' => $cek_payment->payment_type,
                        'transaction_details' => [
                            'order_id' => $order_id,
                            'gross_amount' => $grand_total
                        ],
                        'bank_transfer' => [
                            "bank" => $cek_payment->slug
                        ]
                    ]
                ]);

                $response = $request->getBody()->getContents();
            } else if ($cek_payment->payment_type == 'echannel') {
                $request = $client->request('POST', $alamat, [
                    'auth' => [$this->paymentMethod('server_key_dev'),  $this->paymentMethod('server_key_dev')],
                    'json' => [
                        'payment_type' => $cek_payment->payment_type,
                        'transaction_details' => [
                            'order_id' => $order_id,
                            'gross_amount' => $grand_total
                        ],
                        'echannel' => [
                            "bill_info1" => "Payment: " . $cek_product->name,
                            "bill_info2" => "Online purchase"
                        ]
                    ]
                ]);

                $response = $request->getBody()->getContents();
            } else if ($cek_payment->payment_type == 'permata') {
                $request = $client->request('POST', $alamat, [
                    'auth' => [$this->paymentMethod('server_key_dev'),  $this->paymentMethod('server_key_dev')],
                    'json' => [
                        'payment_type' => $cek_payment->payment_type,
                        'transaction_details' => [
                            'order_id' => $order_id,
                            'gross_amount' => $grand_total
                        ],
                    ]
                ]);

                $response = $request->getBody()->getContents();
            } else if ($cek_payment->payment_type == 'gopay') {
                $request = $client->request('POST', $alamat, [
                    'auth' => [$this->paymentMethod('server_key_dev'),  $this->paymentMethod('server_key_dev')],
                    'json' => [
                        'payment_type' => $cek_payment->payment_type,
                        'transaction_details' => [
                            'order_id' => $order_id,
                            'gross_amount' => $grand_total
                        ],
                    ]
                ]);

                $response = $request->getBody()->getContents();
            }

            $response = json_decode($response);

            if ($response) {
                if ($response->status_code == 201) {

                    if ($cek_user->email) {
                        $dataMail = [
                            'title_mail' => 'transaction',
                            'subject' => 'Hi, ' . $cek_user->name . '. Thank you for subscribing to our newsletter',
                            'title' => 'Hi, ' . $cek_user->name . '. Thank you for subscribing to our newsletter',
                            'deskripsi' => 'Hi ' . $cek_user->name . ', Thank you for subscribing to our newsletter, we will keep you updated.',
                            'transaction_id' => $order_id,
                            'name_product' => $cek_product->name,
                            'price' => $this->mataUang() . ' ' . number_format($cek_product->real_price, 0, '.', '.'),
                            'subtotal' => $this->mataUang() . ' ' . number_format($subtotal, 0, '.', '.'),
                            'tax' => number_format($tax, 0, '.', '.'),
                            'tax_price' => $this->mataUang() . ' ' . number_format($tax_price, 0, '.', '.'),
                            'admin_fee' => $this->mataUang() . ' ' . number_format($admin_fee, 0, '.', '.'),
                            'grandtotal' => $this->mataUang() . ' ' . number_format($grand_total, 0, '.', '.'),
                            'emailto' => $cek_user->email,
                            'name' => $cek_user->name,
                            'time_ago' => date_format(date_create(now()), 'F, d Y H:i A'),
                            'role' => 'user'
                        ];

                        Mail::to($cek_user->email)->send(new SentsMail($dataMail));
                    }

                    $dataMail = [
                        'title_mail' => 'transaction',
                        'subject' => 'Hi, ' . $cek_user_store->name . '. ' . $cek_user->name . ' telah melakukan pembelian produk ' . $cek_product->name,
                        'title' => 'Hi, ' . $cek_user_store->name . '. ' . $cek_user->name . ' telah melakukan pembelian produk ' . $cek_product->name,
                        'deskripsi' => 'Hi, ' . $cek_user_store->name . '. ' . $cek_user->name . ' telah melakukan pembelian produk ' . $cek_product->name . ' dengan harga ' . $this->mataUang() . ' ' . number_format($grand_total, 0, '.', '.'),
                        'transaction_id' => $order_id,
                        'name_product' => $cek_product->name,
                        'price' => $this->mataUang() . ' ' . number_format($cek_product->real_price, 0, '.', '.'),
                        'subtotal' => $this->mataUang() . ' ' . number_format($subtotal, 0, '.', '.'),
                        'tax' => number_format($tax, 0, '.', '.'),
                        'tax_price' => $this->mataUang() . ' ' . number_format($tax_price, 0, '.', '.'),
                        'admin_fee' => $this->mataUang() . ' ' . number_format($admin_fee, 0, '.', '.'),
                        'grandtotal' => $this->mataUang() . ' ' . number_format($grand_total, 0, '.', '.'),
                        'emailto' => $cek_user->email,
                        'name' => $cek_user->name,
                        'time_ago' => date_format(date_create(now()), 'F, d Y H:i A'),
                        'role' => 'admin'
                    ];

                    Mail::to($cek_user_store->email)->send(new SentsAdminMail($dataMail));

                    $invoice = 'INV-' . Str::random(6);
                    $insert = Transaction::create([
                        'user_id' => $cek_user->id,
                        'id_product' => $cek_product->id,
                        'id_payment' => $cek_payment->id,
                        'year' => $year,
                        'month' => $month,
                        'day' => $day,
                        'status' => $response->transaction_status,
                        'status_transaction' => $response->transaction_status,
                        'expired_due' => $response->expiry_time,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $insert_detail = TransactionDetail::create([
                        'id_transaction' => $insert->id,
                        'name_product' => $cek_product->name,
                        'amount_product' => $cek_product->real_price,
                        'subtotal' => $subtotal,
                        'discount' => $cek_product->discount,
                        'tax' => $tax,
                        'tax_price' => $tax_price,
                        'grandtotal' => $grand_total,
                        'admin_fee' => $admin_fee,
                        'payment_method' => $cek_payment->name,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $insert_invoice = Invoices::create([
                        'no_inv' => $invoice,
                        'user_id' => $cek_user->id,
                        'id_transaction' => $insert->id,
                        'id_product' => $cek_product->id,
                        'status' => $response->transaction_status,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    if ($cek_payment->payment_type == 'bank_transfer') {
                        foreach ($response->va_numbers as $va) {
                            $insert_va = VaUser::create([
                                'user_id' => $cek_user->id,
                                'va' => $va->va_number,
                                'bank' => $va->bank,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }

                    $cek_product->update([
                        'stocks' => $cek_product->stocks - $cek_cart->total_product,
                        'updated_at' => now()
                    ]);

                    $cek_cart->delete();

                    return response()->json([
                        'code' => 201,
                        'status' => true,
                        'msg' => 'Transaction Created',
                        'data' => [
                            'gateway' => $response,
                            'transaction' => $insert,
                            'detail' => $insert_detail,
                            'invoice' => $insert_invoice,
                            'product' => $cek_product,
                            'user' => $cek_user,
                        ],
                        'error' => 1,
                    ], 200);
                } else {
                    return response()->json([
                        'code' => 417,
                        'status' => false,
                        'msg' => 'something was wrong',
                        'error' => 1,
                    ], 417);
                }
            } else {
                return response()->json([
                    'code' => 417,
                    'status' => false,
                    'msg' => 'something was wrong',
                    'error' => 1,
                ], 417);
            }
        } catch (RequestException $exception) {
            dd($exception);
            $response = $exception->getResponse();
            $rcode = $response->getStatusCode();
            $rbody = $response->getBody();
            $reason = $response->getReasonPhrase();

            return response()->json([
                'code' => $rcode,
                'status' => false,
                'msg' => $rbody,
                'error' => 1,
                'error_detail' => [
                    'code' => $rcode,
                    'other' => $reason,
                    'headers' => $response->getHeaders(),
                ]
            ], $rcode);
        }
    }
}
