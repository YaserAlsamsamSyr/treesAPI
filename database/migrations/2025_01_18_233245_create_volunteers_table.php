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
        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->string('mac');
            $table->mediumText('desc');
            $table->string('address');
            $table->string('phone');
            $table->string('isApproved')->default("pin");
            $table->string('rate')->default(0);
            $table->string('rejectDesc')->nullable();
            $table->string('adminApproved')->nullable();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteers');
    }
};
