<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $prefix = config('cashier.database.table_prefix');
        Schema::create("{$prefix}invoice_data", function (Blueprint $table) {
            $table->id();
            $table->string('invoiceable_type');
            $table->unsignedBigInteger('invoiceable_id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('vat')->nullable();
            $table->string('fiscal_code')->nullable();
            $table->timestamps();
        });
    }
};
