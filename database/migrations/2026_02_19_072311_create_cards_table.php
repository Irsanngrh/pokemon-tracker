<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expansion_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('card_number');
            $table->string('rarity')->nullable();
            $table->string('image_url')->nullable();
            $table->string('category')->nullable();
            $table->string('illustrator')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};