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
        Schema::create('fares', function (Blueprint $table) {
    $table->uuid('id')->primary();

    $table->foreignUuid('route_id')
          ->nullable()
          ->constrained()
          ->nullOnDelete();

    $table->string('from');
    $table->string('to');

    $table->decimal('one_way_fare', 8, 2);

    $table->decimal('fare_5_day', 8, 2);
    $table->decimal('fare_6_day', 8, 2);
    $table->decimal('fare_7_day', 8, 2);
    $table->decimal('fare_22_day', 8, 2);
    $table->decimal('fare_26_day', 8, 2);

    $table->boolean('active')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fares');
    }
};
