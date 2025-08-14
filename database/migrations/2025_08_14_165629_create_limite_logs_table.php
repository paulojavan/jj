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
        Schema::create('limite_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('usuario_id');
            $table->enum('acao', ['limite_alterado', 'status_alterado']);
            $table->string('valor_anterior')->nullable();
            $table->string('valor_novo')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['cliente_id', 'created_at']);
            $table->index(['usuario_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('limite_logs');
    }
};
