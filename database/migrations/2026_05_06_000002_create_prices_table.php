<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('server')->default('all')->index();
            $table->unsignedBigInteger('price_1')->nullable();   // prix unitaire
            $table->unsignedBigInteger('price_10')->nullable();  // lot de 10
            $table->unsignedBigInteger('price_100')->nullable(); // lot de 100
            $table->unsignedInteger('volume')->nullable();       // volume échangé
            $table->timestamp('recorded_at')->index();
            $table->timestamps();

            $table->index(['item_id', 'server', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
