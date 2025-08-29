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
        Schema::create('despesas_agua_branca', function (Blueprint $table) {
            $table->id('id_despesas');
            $table->date('data');
            $table->string('tipo', 255);
            $table->string('empresa', 255);
            $table->string('numero', 255)->nullable();
            $table->decimal('valor', 11, 2);
            $table->string('status', 255)->nullable();
            $table->string('pagamento', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despesas_agua_branca');
    }
};
