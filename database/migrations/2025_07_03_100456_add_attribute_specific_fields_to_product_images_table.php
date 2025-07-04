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
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('alt_text')->nullable()->after('image_url');
            $table->integer('sort_order')->default(0)->after('alt_text');
            // JSON field to store attribute combinations this image applies to
            // Example: [{"attribute_id": 1, "attribute_value_id": 2}, {"attribute_id": 3, "attribute_value_id": 5}]
            $table->json('attribute_combination')->nullable()->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['alt_text', 'sort_order', 'attribute_combination']);
        });
    }
};
