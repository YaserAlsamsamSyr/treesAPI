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
        Schema::create('planstores', function (Blueprint $table) {
            $table->id();
            $table->string('mac');
            $table->string('address');
            $table->string('phone');
            $table->string('ownerName');
            $table->mediumText('desc');
            $table->string('openTime');
            $table->string('closeTime');
            $table->string('isApproved')->default("pin");
            $table->string('rejectDesc')->nullable();
            $table->string('adminApproved')->nullable();
            $table->integer('rate')->default(0);
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planstores');
    }
};
