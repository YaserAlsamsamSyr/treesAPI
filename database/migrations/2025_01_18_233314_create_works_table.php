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
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mac')->default("no mac");
            $table->string('address');
            $table->mediumText('desc');
            $table->string('status')->default("wait");
            $table->string('isDone')->nullable();
            $table->foreignId('volunteer_id')->nullable()->references('id')->on('volunteers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};
