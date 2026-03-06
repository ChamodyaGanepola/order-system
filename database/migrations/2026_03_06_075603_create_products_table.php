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
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('product_code');
        $table->string('other')->nullable();  // variant
        $table->string('name');
        $table->decimal('price', 10, 2);
        $table->integer('stock');
        $table->timestamps();

        $table->unique(['product_code', 'other'], 'unique_product_variant');
    });
}

public function down(): void
{
    Schema::dropIfExists('products');
}
  
};
