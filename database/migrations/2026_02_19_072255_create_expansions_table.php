<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expansions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('release_date')->nullable();
            $table->string('symbol_image_url')->nullable();
            $table->integer('total_cards')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expansions');
    }
};