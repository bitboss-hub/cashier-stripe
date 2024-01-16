<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $prefix = config('cashier.database.table_prefix');
        Schema::create("{$prefix}stripe_data", function (Blueprint $table) {
            $table->id();
            $table->string('stripeable_type');
            $table->unsignedBigInteger('stripeable_id');
            $table->string('stripe_id')->nullable();
            $table->timestamps();
        });
    }
};
