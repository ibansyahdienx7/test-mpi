<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->integer('id_transaction');
            $table->string('name_product');
            $table->integer('amount_product');
            $table->integer('subtotal');
            $table->integer('discount');
            $table->integer('tax');
            $table->integer('tax_price');
            $table->integer('grandtotal');
            $table->integer('admin_fee');
            $table->string('payment_method');
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
