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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('apelido')->nullable();
            $table->string('rg');
            $table->string('cpf');
            $table->string('mae')->nullable();
            $table->string('pai')->nullable();
            $table->date('nascimento')->nullable();
            $table->string('telefone');
            $table->string('nome_referencia');
            $table->string('numero_referencia');
            $table->string('parentesco_referencia');
            $table->string('referencia_comercial1');
            $table->string('telefone_referencia_comercial1');
            $table->string('referencia_comercial2');
            $table->string('telefone_referencia_comercial2');
            $table->string('referencia_comercial3');
            $table->string('telefone_referencia_comercial3');
            $table->string('foto');
            $table->string('rg_frente');
            $table->string('rg_verso');
            $table->string('cpf_foto');
            $table->string('rua');
            $table->integer('numero');
            $table->string('bairro');
            $table->string('referencia');
            $table->string('cidade');
            $table->decimal('limite', 15, 2)->default(0.00);
            $table->string('renda');
            $table->enum('status', ['ativo', 'inativo'])->default('inativo');
            $table->date('atualizacao')->nullable();
            $table->string('token');
            $table->string('obs')->nullable();
            $table->date('ociosidade')->nullable();
            $table->string('pasta')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
