<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACIÓN: schedules
 * Reservas de salones con fecha + hora inicio/fin
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->increments('id')->comment('Identificador de la tabla');

            // Referencian a parameters.id (type = SALON / AREA)
            $table->unsignedInteger('salon_id')->comment('Salón reservado (FK parameters)');
            $table->unsignedInteger('area_id')->comment('Área solicitante (FK parameters)');

            $table->string('nombre', 190)->comment('Nombre del solicitante');
            $table->string('email', 190)->comment('Correo del solicitante');

            $table->date('fecha')->comment('Fecha de la reserva');
            $table->time('hora_inicio')->comment('Hora de inicio de la reserva');
            $table->time('hora_fin')->comment('Hora de fin de la reserva');

            $table->enum('status', ['PENDIENTE', 'ACEPTADA', 'RECHAZADA', 'CANCELADA'])
                ->default('PENDIENTE')
                ->comment('Estado de la solicitud');

            $table->string('observacion', 500)->comment('Motivo/uso del salón');

            $table->timestamps();

            // Índices para consultas por disponibilidad y reportes
            $table->index(['salon_id', 'fecha'], 'idx_schedules_salon_fecha');
            $table->index(['fecha', 'hora_inicio'], 'idx_schedules_fecha_hora_inicio');
            $table->index(['status'], 'idx_schedules_status');

            // FK (misma tabla parameters)
            $table->foreign('salon_id', 'fk_schedules_salon')
                ->references('id')->on('parameters')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('area_id', 'fk_schedules_area')
                ->references('id')->on('parameters')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Primero borrar FKs (por nombre)
            $table->dropForeign('fk_schedules_salon');
            $table->dropForeign('fk_schedules_area');
        });

        Schema::dropIfExists('schedules');
    }
};
