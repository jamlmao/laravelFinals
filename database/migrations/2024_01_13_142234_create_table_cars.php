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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->text('desc');
            $table->string('name');
            $table->string('brand');
            $table->decimal('price', 8, 2);
            $table->decimal('pickup_counter', 8, 2) ->default(0) -> nullable();
            $table->string('image')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
