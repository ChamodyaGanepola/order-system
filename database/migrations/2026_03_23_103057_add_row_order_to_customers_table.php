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
    Schema::table('customers', function (Blueprint $table) {
        $table->integer('row_order')->nullable()->after('id'); // store Excel row number
        $table->integer('import_batch')->nullable()->after('row_order'); // optional for multiple Excels
    });
}

public function down()
{
    Schema::table('customers', function (Blueprint $table) {
        $table->dropColumn(['row_order', 'import_batch']);
    });
}
};
