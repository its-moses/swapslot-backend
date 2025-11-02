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
        Schema::create('swaprequeststbl', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_user_id');
            $table->unsignedBigInteger('receiver_user_id');

            // Event slot relations
            $table->unsignedBigInteger('mySlotId');
            $table->unsignedBigInteger('theirSlotId');


            // Add foreign key constraints explicitly
            $table->foreign('requester_user_id')->references('id')->on('authuserstbl')->onDelete('cascade');
            $table->foreign('receiver_user_id')->references('id')->on('authuserstbl')->onDelete('cascade');
            $table->foreign('mySlotId')->references('id')->on('eventstbl')->onDelete('cascade');
            $table->foreign('theirSlotId')->references('id')->on('eventstbl')->onDelete('cascade');

            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED'])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swaprequeststbl');
    }
};
