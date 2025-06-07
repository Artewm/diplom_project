<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // владелец трека
            $table->string('title');       // Название трека
            $table->string('artist');      // Исполнитель
            $table->string('genre');       // Жанр
            $table->string('duration');    // Длительность
            $table->string('file_path');   // Путь к аудиофайлу
            $table->string('cover_path')->nullable(); // Путь к обложке (опционально)
            $table->timestamps();          // created_at и updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
