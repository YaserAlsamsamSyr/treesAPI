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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('img');
            $table->foreignId('article_id')->nullable()->references('id')->on('articles')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('advertisement_id')->nullable()->references('id')->on('advertisements')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('admin_id')->nullable()->references('id')->on('admins')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('event_id')->nullable()->references('id')->on('events')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('work_id')->nullable()->references('id')->on('works')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('planstore_id')->nullable()->references('id')->on('planstores')->onDelete('cascade')->onUpdate('cascade');         
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
