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
        Schema::table('pacientes', function (Blueprint $table) {
            $table->string('sexo')->after('apellido');
            $table->string('nacionalidad')->after('sexo');
            $table->string('cip')->unique()->after('nacionalidad');
            $table->string('grupo_sanguineo')->nullable()->after('email');
            $table->text('alergias')->nullable()->after('grupo_sanguineo');
            $table->text('condiciones_medicas')->nullable()->after('alergias');
            $table->text('medicamentos')->nullable()->after('condiciones_medicas');
            $table->string('nombre_aseguradora')->nullable()->after('medicamentos');
            $table->string('numero_poliza')->nullable()->after('nombre_aseguradora');
            $table->date('fecha_vencimiento_poliza')->nullable()->after('numero_poliza');
            $table->string('contacto_emergencia_nombre')->after('fecha_vencimiento_poliza');
            $table->string('contacto_emergencia_relacion')->after('contacto_emergencia_nombre');
            $table->string('contacto_emergencia_telefono')->after('contacto_emergencia_relacion');
            $table->string('ocupacion')->nullable()->after('contacto_emergencia_telefono');
            $table->string('estado_civil')->nullable()->after('ocupacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn([
                'sexo', 'nacionalidad', 'cip', 'grupo_sanguineo', 'alergias', 'condiciones_medicas',
                'medicamentos', 'historial_medico_familiar', 'nombre_aseguradora', 'numero_poliza',
                'fecha_vencimiento_poliza', 'contacto_emergencia_nombre', 'contacto_emergencia_relacion',
                'contacto_emergencia_telefono', 'ocupacion', 'estado_civil'
            ]);
        });
    }
};
