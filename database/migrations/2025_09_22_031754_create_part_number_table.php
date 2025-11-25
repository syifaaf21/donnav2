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
        Schema::create('tm_part_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('part_number')->nullable()->unique();
            $table->foreignId('product_id')->constrained('tm_products')->onDelete('cascade');
            $table->foreignId('model_id')->constrained('tm_models')->onDelete('cascade');
            // $table->enum('process', ['injection', 'painting', 'assembling body', 'die casting', 'machining', 'assembling unit', 'mounting', 'assembling electric', 'inspection']);
            $table->enum('plant', ['ALL', 'Body', 'Unit', 'Electric']);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tm_part_numbers');
    }
};
