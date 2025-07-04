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
        Schema::table('attributes', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->text('description')->nullable()->after('slug');
            $table->enum('type', ['text', 'number', 'color', 'size', 'dropdown'])->default('dropdown')->after('description');
            $table->boolean('is_required')->default(false)->after('type');
            $table->integer('sort_order')->default(0)->after('is_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn(['slug', 'description', 'type', 'is_required', 'sort_order']);
        });
    }
};
