<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->string('category')->nullable()->after('rarity');
            $table->string('illustrator')->nullable()->after('category');
            $table->text('description')->nullable()->after('illustrator');
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn(['category', 'illustrator', 'description']);
        });
    }
};