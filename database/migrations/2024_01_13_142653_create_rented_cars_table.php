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
        Schema::create('rented_cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('car_id')->constrained()->restrictOnDelete();
            $table->string('status');
            $table->date('pickup_date')->nullable();
            $table->date('return_date')->nullable();
            $table->decimal('amount', 8, 2)->nullable();
            $table->decimal('due_fee', 10, 2)->default(0.00);
            $table->string('payment_status')->default('unpaid');
            $table->integer('days')->nullable();
            $table->timestamps();
        });
  }
     // Add closing parenthesis here

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rented_cars');
    }
};
