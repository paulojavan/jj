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
        Schema::create('vendas_tavares', function (Blueprint $table) {
            $table->id('id_vendas');
            $table->unsignedBigInteger('id_vendedor');
            $table->unsignedBigInteger('id_vendedor_atendente')->nullable();
            $table->unsignedBigInteger('id_produto');
            $table->date('data_venda');
            $table->time('hora')->nullable();
            $table->date('data_estorno')->nullable();
            $table->decimal('valor_dinheiro', 15, 2)->nullable();
            $table->decimal('valor_pix', 15, 2)->default(0);
            $table->decimal('valor_cartao', 15, 2)->nullable();
            $table->decimal('valor_crediario', 15, 2)->nullable();
            $table->decimal('preco', 15, 2);
            $table->decimal('preco_venda', 15, 2);
            $table->boolean('desconto')->default(false);
            $table->boolean('alerta')->default(false);
            $table->boolean('baixa_fiscal')->default(false);
            $table->integer('numeracao')->nullable();
            $table->date('pedido_devolucao')->nullable();
            $table->string('reposicao', 10)->nullable();
            $table->string('bd', 5);
            $table->string('ticket', 60)->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('id_produto');
            $table->index('id_vendedor');
            $table->index('id_vendedor_atendente');
            
            // Chaves estrangeiras
            $table->foreign('id_produto')->references('id')->on('produtos');
            $table->foreign('id_vendedor')->references('id')->on('users');
            $table->foreign('id_vendedor_atendente')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendas_tavares');
    }
};
