<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->string('name');
            $table->string('photo');
            $table->string('slug');
            $table->string('payment_type');
            $table->string('status');
            $table->nullableTimestamps();
        });

        DB::table('payment_methods')->insert([
            [
                'code' => '84849',
                'name' => 'GO PAY',
                'photo' => url("") . '/assets/upload/payment_method/OFK5Y2-1676855538.png',
                'slug' => 'go_pay',
                'payment_type' => 'gopay',
                'status' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => '73455',
                'name' => 'BANK BNI',
                'photo' => url("") . '/assets/upload/payment_method/EqVvVa-1676855622.png',
                'slug' => 'bni',
                'payment_type' => 'bank_transfer',
                'status' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => '63286',
                'name' => 'BANK PERMATA',
                'photo' => url("") . '/assets/upload/payment_method/8pb9gg-1676855673.png',
                'slug' => 'permata',
                'payment_type' => 'permata',
                'status' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => '3551',
                'name' => 'BANK MANDIRI',
                'photo' => url("") . '/assets/upload/payment_method/At7cIc-1676855862.png',
                'slug' => 'mandiri',
                'payment_type' => 'echannel',
                'status' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => '34256',
                'name' => 'BANK BRI',
                'photo' => url("") . '/assets/upload/payment_method/UExMBx-1676893200.png',
                'slug' => 'bri',
                'payment_type' => 'bank_transfer',
                'status' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
