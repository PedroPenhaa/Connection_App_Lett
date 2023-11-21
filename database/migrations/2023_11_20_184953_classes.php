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
        Schema::create('classes', function (Blueprint $table) {
            $table->id(); // Cria o campo 'id' como chave primária autoincremental
            $table->string('name'); // Cria o campo 'name' como uma string

            $table->unsignedBigInteger('family_id');
            $table->foreign('family_id')->references('id')->on('families');
            // Adicione outros campos conforme necessário

            $table->timestamps(); // Adiciona automaticamente os campos 'created_at' e 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};