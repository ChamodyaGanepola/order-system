<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->timestamp('pending_at')->nullable();
        $table->timestamp('shipping_at')->nullable();
        $table->timestamp('completed_at')->nullable();
        $table->timestamp('rejected_at')->nullable();
        $table->timestamp('out_of_stock_at')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
