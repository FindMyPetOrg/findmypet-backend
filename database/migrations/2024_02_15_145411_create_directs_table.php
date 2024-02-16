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
        Schema::create('directs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id');
            $table->foreignId('receiver_id');
            $table->string('text');
            $table->boolean('seen')->default(false);
            $table->timestamps();

            $table->foreign('sender_id')->on('users')->references('id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('receiver_id')->on('users')->references('id')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directs');
    }
};
