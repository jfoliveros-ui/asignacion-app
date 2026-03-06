<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACIÓN: parameters
 * Catálogo para SALON, AREA, etc.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parameters', function (Blueprint $table) {
            $table->increments('id')->comment('Identificador de la tabla');

            // Ej: SALON, AREA, ESTADO_SOLICITUD, etc.
            $table->string('type', 50)->comment('Tipo de parámetro (grupo)');

            // Ej: "Salón 101", "Académica"
            $table->string('name', 190)->comment('Nombre/valor visible del parámetro');

            // Ej: capacidad=40, sede=Altico, piso=2, video=si, etc.
            $table->string('meta', 255)->nullable()->comment('Valor adicional (metadatos)');

            $table->timestamps();

            $table->index('type', 'idx_parameters_type');
            $table->index(['type', 'name'], 'idx_parameters_type_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parameters');
    }
};
