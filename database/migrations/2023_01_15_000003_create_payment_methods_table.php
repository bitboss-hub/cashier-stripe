<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $prefix = config('cashier.database.table_prefix');
        Schema::create("{$prefix}payment_methods", function (Blueprint $table) {
            $table->id();
            $table->string('stripeable_type');
            $table->unsignedBigInteger('stripeable_id');
            $table->string('stripe_id')->nullable();
            $table->boolean('default')->default(false)->nullable();
            $table->string('gateway')->default('stripe')->nullable();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }
};
