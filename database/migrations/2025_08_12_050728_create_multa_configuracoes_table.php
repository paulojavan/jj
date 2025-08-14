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
        Schema::create('multa_configuracoes', function (Blueprint $table) {
            $table->id();
            $table->decimal('taxa_multa', 5, 2)->default(0.00)->comment('Taxa de multa em percentual');
            $table->decimal('taxa_juros', 5, 2)->default(0.00)->comment('Taxa de juros em percentual');
            $table->unsignedInteger('dias_cobranca')->default(30)->comment('Dias para iniciar cobrança');
            $table->unsignedInteger('dias_carencia')->default(0)->comment('Dias de carência antes da multa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multa_configuracoes');
    }
};
