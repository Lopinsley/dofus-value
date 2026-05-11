<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('server')->default('draconiros');
            $table->unsignedBigInteger('threshold_price');
            $table->enum('direction', ['below', 'above'])->default('below');
            $table->string('email')->nullable();
            $table->boolean('triggered')->default(false);
            $table->timestamp('triggered_at')->nullable();
            $table->timestamps();

            $table->index(['item_id', 'server', 'triggered']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_alerts');
    }
};
