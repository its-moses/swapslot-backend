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
        Schema::create('SwapRequestsTbl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_user_id')->constrained('AuthUsersTbl')->onDelete('cascade'); // who initiated swap
            $table->foreignId('receiver_user_id')->constrained('AuthUsersTbl')->onDelete('cascade'); // whose slot is being requested

            $table->foreignId('mySlotId')->constrained('EventsTbl')->onDelete('cascade'); // requester’s slot
            $table->foreignId('theirSlotId')->constrained('EventsTbl')->onDelete('cascade'); // receiver’s slot

            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED'])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('SwapRequestsTbl');
    }
};
