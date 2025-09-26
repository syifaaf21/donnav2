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
        Schema::create('part_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('part_number')->nullable()->unique();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('model_id')->constrained('models')->onDelete('cascade');
            $table->enum('process', ['injection', 'painting', 'assembling body', 'die casting', 'machining', 'assembling unit', 'electric']);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_numbers');
    }
};
