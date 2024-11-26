<!DOCTYPE html>
<html>
<head>
    <title>Cita Creada</title>
</head>
<body>
    <h1>Cita Creada</h1>
    <p>Estimado/a {{ $appointment->paciente->nombre }} {{ $appointment->paciente->apellido }},</p>
    <p>Su cita ha sido creada exitosamente.</p>
    <p>Detalles de la cita:</p>
    <ul>
        <li>Fecha: {{ $appointment->fecha }}</li>
        <li>Hora de Inicio: {{ $appointment->hora_inicio }}</li>
        <li>Hora de Fin: {{ $appointment->hora_fin }}</li>
        <li>Doctor: {{ $appointment->doctor->nombre }} {{ $appointment->doctor->apellido }}</li>
        <li>Motivo: {{ $appointment->motivo->nombre }}</li>
    </ul>
    <p>Gracias por confiar en nosotros.</p>
</body>
</html>
