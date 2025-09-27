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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('external_product_id')->unique();
            $table->string('product_variant_name')->nullable();
            $table->string('product_variant_id')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_tracked')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
