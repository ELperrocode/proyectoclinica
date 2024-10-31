<!DOCTYPE html>
<html>
<head>
    <title>Receta</title>
</head>
<body>
    <h1>Receta</h1>
    <p>Paciente: {{ $paciente->nombre }} {{ $paciente->apellido }}</p>
    <p>Doctor: {{ $doctor->nombre }} {{ $doctor->apellido }}</p>
    <p>Fecha: {{ $fecha }}</p>
    <h2>Recetas</h2>
    <ul>
        @foreach($receta as $r)
            <li>{{ $r->descripcion }}</li>
        @endforeach
    </ul>
</body>
</html>
