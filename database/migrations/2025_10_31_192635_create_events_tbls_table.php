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
        Schema::create('eventstbl', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->timestamp('startTime')->nullable();
            $table->timestamp('endTime')->nullable();
            $table->enum('status', ['BUSY', 'SWAPPABLE', 'SWAP_PENDING'])->default('BUSY');
            $table->foreignId('user_id')->constrained('authuserstbl')->onDelete('cascade');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventstbl');
    }
};
