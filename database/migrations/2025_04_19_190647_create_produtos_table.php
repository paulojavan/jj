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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('produto');
            $table->string('marca');
            $table->string('genero');
            $table->string('grupo');
            $table->string('subgrupo');
            $table->string('codigo');
            $table->string('quantidade');
            $table->integer('num1');
            $table->integer('num2');
            $table->decimal('preco', 15, 2)->default(0.00);
            $table->string('foto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
