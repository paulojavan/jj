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
        Schema::table('tickets', function (Blueprint $table) {
            // Índice para buscar tickets por cliente
            $table->index('id_cliente', 'idx_tickets_id_cliente');
            
            // Índice composto para buscar tickets por cliente ordenados por data
            $table->index(['id_cliente', 'data'], 'idx_tickets_cliente_data');
        });

        Schema::table('parcelas', function (Blueprint $table) {
            // Índice para buscar parcelas por ticket
            $table->index('ticket', 'idx_parcelas_ticket');
            
            // Índice para buscar parcelas por cliente
            $table->index('id_cliente', 'idx_parcelas_id_cliente');
            
            // Índice composto para cálculos de status (ticket + data_vencimento)
            $table->index(['ticket', 'data_vencimento'], 'idx_parcelas_ticket_vencimento');
            
            // Índice para buscar parcelas por status
            $table->index('status', 'idx_parcelas_status');
            
            // Índice composto para análise de pagamentos (cliente + data_pagamento + status)
            $table->index(['id_cliente', 'data_pagamento', 'status'], 'idx_parcelas_cliente_pagamento_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_id_cliente');
            $table->dropIndex('idx_tickets_cliente_data');
        });

        Schema::table('parcelas', function (Blueprint $table) {
            $table->dropIndex('idx_parcelas_ticket');
            $table->dropIndex('idx_parcelas_id_cliente');
            $table->dropIndex('idx_parcelas_ticket_vencimento');
            $table->dropIndex('idx_parcelas_status');
            $table->dropIndex('idx_parcelas_cliente_pagamento_status');
        });
    }
};
