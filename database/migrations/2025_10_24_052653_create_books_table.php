<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->unsignedBigInteger('author_id')->index();
            $table->unsignedBigInteger('category_id')->index();
            $table->unsignedBigInteger('store_id')->nullable()->index();
            $table->string('publisher')->nullable()->index();
            $table->year('published_year')->nullable()->index();
            $table->string('isbn')->index();
            $table->integer('stock')->default(0)->index();
            $table->timestamps();

            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
