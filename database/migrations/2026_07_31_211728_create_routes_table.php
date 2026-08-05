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
        Schema::create('routes', function (Blueprint $table) {

            // Primary Key
            $table->uuid('id')->primary();

            // Relationships
            $table->foreignUuid('area_id')
                ->constrained('areas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('municipality_id')
                ->constrained('municipalities')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('depot_id')
                ->nullable()
                ->constrained('depots')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // Route Information
            $table->string('route_code', 20)->unique();
            $table->string('route_name');

            // Official Buscor Route
            $table->string('from_location');
            $table->string('to_location');

            // Operational Information
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('estimated_duration')->nullable()->comment('Minutes');

            // Route Status
            $table->boolean('active')->default(true);

            // Notes
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('route_code');
            $table->index('route_name');
            $table->index('from_location');
            $table->index('to_location');
            $table->index('area_id');
            $table->index('municipality_id');
            $table->index('depot_id');
            $table->index('active');

            $table->index([
                'from_location',
                'to_location'
            ]);

            $table->index([
                'area_id',
                'municipality_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};