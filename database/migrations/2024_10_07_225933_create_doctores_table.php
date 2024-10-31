<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctoresTable extends Migration
{
    public function up()
    {
        Schema::create('doctores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            $table->string('telefono');
            $table->string('email')->unique();
            $table->string('cip')->unique();
            $table->string('numero_junta_tecnica')->unique();
            $table->string('direccion');
            $table->string('sexo');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctores');
    }
}
