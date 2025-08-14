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
        Schema::create('autorizados', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('idCliente');
            $table->foreign('idCliente')->references('id')->on('clientes');

            $table->string('nome');
            $table->string('rg');
            $table->string('cpf');
            $table->string('foto');
            $table->string('rg_frente');
            $table->string('rg_verso');
            $table->string('cpf_foto');
            $table->string('pasta');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autorizados');
    }
};
