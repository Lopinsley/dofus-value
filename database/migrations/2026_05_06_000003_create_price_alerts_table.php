<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('server')->default('all');
            $table->enum('type', ['below', 'above'])->default('below');
            $table->unsignedBigInteger('target_price');
            $table->string('notify_via')->default('email'); // email, discord
            $table->string('notify_target')->nullable(); // email ou discord webhook
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();

            $table->index(['item_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_alerts');
    }
};
