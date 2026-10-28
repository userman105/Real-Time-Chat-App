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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade'); // belongs to a chat
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // sender

            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('file_type', ['text', 'image', 'video'])->default('text');

            $table->boolean('is_read')->default(false); // quick read flag
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
