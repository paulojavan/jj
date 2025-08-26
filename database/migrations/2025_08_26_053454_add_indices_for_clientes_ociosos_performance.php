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
        Schema::table('clientes', function (Blueprint $table) {
            // Índice para o campo ociosidade para otimizar consultas de clientes ociosos
            $table->index('ociosidade', 'idx_clientes_ociosidade');
            
            // Índice para o campo status para otimizar filtros de status
            $table->index('status', 'idx_clientes_status');
            
            // Índice composto para ociosidade e status (consulta mais comum)
            $table->index(['ociosidade', 'status'], 'idx_clientes_ociosidade_status');
        });

        Schema::table('tickets', function (Blueprint $table) {
            // Índice para o campo spc para otimizar consultas de negativação
            $table->index('spc', 'idx_tickets_spc');
            
            // Índice composto para id_cliente e spc (consulta mais comum)
            $table->index(['id_cliente', 'spc'], 'idx_tickets_cliente_spc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('idx_clientes_ociosidade');
            $table->dropIndex('idx_clientes_status');
            $table->dropIndex('idx_clientes_ociosidade_status');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_spc');
            $table->dropIndex('idx_tickets_cliente_spc');
        });
    }
};
