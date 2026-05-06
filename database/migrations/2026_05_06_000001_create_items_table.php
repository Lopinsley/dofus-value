<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dofus_id')->unique()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category');
            $table->unsignedTinyInteger('level')->default(1);
            $table->string('image_url')->nullable();
            $table->json('recipe')->nullable(); // ingredients de craft
            $table->json('effects')->nullable(); // stats de l'item
            $table->timestamps();

            $table->index(['category', 'level']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
