<?php
namespace App\Filament\Resources\CitaResource\Pages;

use App\Filament\Resources\CitaResource;
use Filament\Resources\Pages\CreateRecord;
use App\Mail\AppointmentCreated;
use Illuminate\Support\Facades\Mail;
use App\Models\Cita;
use Filament\Notifications\Notification;
class CreateCita extends CreateRecord
{
    protected static string $resource = CitaResource::class;

    protected function handleRecordCreation(array $data): Cita
    {
        $appointment = parent::handleRecordCreation($data);

        // Enviar notificación de éxito
        Notification::make()
            ->title('Cita Creada')
            ->body('La cita ha sido creada exitosamente para ' . $appointment->paciente->nombre . ' ' . $appointment->paciente->apellido)
            ->success()
            ->send();

        // Enviar correo de confirmación
        try {
            Mail::to($appointment->paciente->email)->send(new AppointmentCreated($appointment));
            Notification::make()
                ->title('Correo Enviado')
                ->body('El correo de confirmación ha sido enviado a ' . $appointment->paciente->email)
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al Enviar Correo')
                ->body('Hubo un error al enviar el correo de confirmación: ' . $e->getMessage())
                ->danger()
                ->send();
        }

        return $appointment;
    }
}
