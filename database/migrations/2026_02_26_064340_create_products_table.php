<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code');       // main product code
            $table->string('other')->nullable();  // variant (chain, bracelet, etc.)
            $table->string('name');               // product name
            $table->decimal('price', 10, 2);     // price
            $table->integer('stock');             // stock
            $table->timestamps();

            // make combination of product_code + variant unique
            $table->unique(['product_code', 'other'], 'unique_product_variant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
