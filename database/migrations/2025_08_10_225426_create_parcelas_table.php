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
        Schema::create('parcelas', function (Blueprint $table) {
            $table->id('id_parcelas');
            $table->string('ticket', 60);
            $table->integer('id_cliente');
            $table->integer('id_autorizado')->nullable();
            $table->string('numero', 11);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->time('hora')->nullable();
            $table->double('valor_parcela', 15, 2);
            $table->double('valor_pago', 15, 2)->nullable();
            $table->double('dinheiro', 15, 2)->nullable();
            $table->double('pix', 15, 2)->nullable();
            $table->double('cartao', 15, 2)->nullable();
            $table->string('metodo', 11)->nullable();
            $table->integer('id_vendedor')->nullable();
            $table->string('status', 30);
            $table->string('bd', 100);
            $table->string('ticket_pagamento', 60)->nullable();
            $table->string('lembrete', 20)->nullable();
            $table->string('primeira', 10)->nullable();
            $table->string('segunda', 10)->nullable();
            $table->string('terceira', 10)->nullable();
            $table->string('quarta', 10)->nullable();
            $table->string('quinta', 10)->nullable();
            $table->string('sexta', 10)->nullable();
            $table->string('setima', 10)->nullable();
            $table->string('oitava', 10)->nullable();
            $table->string('nona', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcelas');
    }
};