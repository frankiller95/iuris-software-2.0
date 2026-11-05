<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Áreas (Civil, Pensiones, Laboral, Familia, Penal) y tipos de proceso. Ver docs/TIPOS_PROCESOS.md */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas_proceso', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('tipos_proceso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_proceso_id')->constrained('areas_proceso')->restrictOnDelete();
            $table->string('nombre', 150);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['area_proceso_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_proceso');
        Schema::dropIfExists('areas_proceso');
    }
};
