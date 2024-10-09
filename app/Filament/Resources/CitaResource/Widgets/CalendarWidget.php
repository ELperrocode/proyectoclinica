<?php

namespace App\Filament\Resources\CitaResource\Widgets;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Doctor;
use App\Models\Servicio;
use Filament\Forms;
use App\Filament\Resources\CitaResource;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Data\EventData;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Actions\EditAction;
use Saade\FilamentFullCalendar\Actions\DeleteAction;

class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Cita::class;

    public function fetchEvents(array $fetchInfo): array
    {
        return Cita::query()
            ->where('fecha', '>=', $fetchInfo['start'])
            ->where('fecha', '<=', $fetchInfo['end'])
            ->get()
            ->map(
                fn (Cita $event) => EventData::make()
                    ->id($event->id)
                    ->title($event->paciente->nombre) 
                    ->start($event->fecha . 'T' . $event->hora_inicio)
                    ->end($event->fecha . 'T' . $event->hora_fin)
                    ->url(
                        url: CitaResource::getUrl(name: 'edit', parameters: ['record' => $event]),
                        shouldOpenUrlInNewTab: false
                    )
            )
            ->toArray();
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('paciente_id')
                ->options(Paciente::all()->pluck('nombre', 'id'))
                ->required(),

            Forms\Components\Select::make('doctor_id')
                ->options(Doctor::all()->pluck('nombre', 'id'))
                ->required(),

            Forms\Components\Select::make('motivo_id')
                ->options(Servicio::all()->pluck('nombre', 'id'))
                ->required(),

            Forms\Components\DatePicker::make('fecha')
                ->required()
                ->minDate(now()->toDateString()),

            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\TimePicker::make('hora_inicio')
                        ->seconds(false)
                        ->required()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $doctorId = $get('doctor_id');
                            $fecha = $get('fecha');
                            $horaFin = $get('hora_fin');

                            if ($doctorId && $fecha && $horaFin) {
                                $exists = Cita::where('doctor_id', $doctorId)
                                    ->where('fecha', $fecha)
                                    ->where(function ($query) use ($state, $horaFin) {
                                        $query->whereBetween('hora_inicio', [$state, $horaFin])
                                              ->orWhereBetween('hora_fin', [$state, $horaFin]);
                                    })
                                    ->exists();

                                if ($exists) {
                                    $set('hora_inicio', null);
                                    Notification::make()
                                        ->title('Conflicto de Horario')
                                        ->body('Ya existe una cita con el mismo doctor en el mismo horario.')
                                        ->danger()
                                        ->send();
                                }
                            }
                        }),

                    Forms\Components\TimePicker::make('hora_fin')
                        ->seconds(false)
                        ->required()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $doctorId = $get('doctor_id');
                            $fecha = $get('fecha');
                            $horaInicio = $get('hora_inicio');

                            if ($doctorId && $fecha && $horaInicio) {
                                $exists = Cita::where('doctor_id', $doctorId)
                                    ->where('fecha', $fecha)
                                    ->where(function ($query) use ($horaInicio, $state) {
                                        $query->whereBetween('hora_inicio', [$horaInicio, $state])
                                              ->orWhereBetween('hora_fin', [$horaInicio, $state]);
                                    })
                                    ->exists();

                                if ($exists) {
                                    $set('hora_fin', null);
                                    Notification::make()
                                        ->title('Conflicto de Horario')
                                        ->body('Ya existe una cita con el mismo doctor en el mismo horario.')
                                        ->danger()
                                        ->send();
                                }
                            }
                        }),
                ]),
        ];
    }

    protected function modalActions(): array
    {
        return [
            EditAction::make()
                ->mountUsing(function (Cita $record, Forms\ComponentContainer $form, array $arguments) {
                    $form->fill([
                        'paciente_id' => $record->paciente_id,
                        'doctor_id' => $record->doctor_id,
                        'motivo_id' => $record->motivo_id,
                        'fecha' => $arguments['event']['start'] ?? $record->fecha,
                        'hora_inicio' => $arguments['event']['start'] ? Carbon::parse($arguments['event']['start'])->format('H:i') : $record->hora_inicio,
                        'hora_fin' => $arguments['event']['end'] ? Carbon::parse($arguments['event']['end'])->format('H:i') : $record->hora_fin,
                    ]);
                }),
            DeleteAction::make(),
        ];
    }
    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->mountUsing(function (Forms\ComponentContainer $form, array $arguments) {
                    $form->fill([
                        'paciente_id' => null,
                        'doctor_id' => null,
                        'motivo_id' => null,
                        'fecha' => $arguments['start'] ?? null,
                        'hora_inicio' => $arguments['start'] ? Carbon::parse($arguments['start'])->format('H:i') : null,
                        'hora_fin' => $arguments['end'] ? Carbon::parse($arguments['end'])->format('H:i') : null,
                    ]);
                }),
        ];
    }
}