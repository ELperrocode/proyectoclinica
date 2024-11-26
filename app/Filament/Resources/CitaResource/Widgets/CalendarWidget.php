<?php

namespace App\Filament\Resources\CitaResource\Widgets;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Doctor;
use App\Models\Servicio;
use Filament\Forms;
use App\Filament\Resources\CitaResource;
use Filament\Forms\Components\TextInput;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Data\EventData;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Reactive;
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
                fn(Cita $event) => EventData::make()
                    ->id($event->id)
                    ->title($event->paciente->nombre . " " . $event->paciente->apellido . " - " . $event->motivo->nombre)
                    ->start($event->fecha . 'T' . $event->hora_inicio)
                    ->end($event->fecha . 'T' . $event->hora_fin)
                    ->backgroundColor($this->getEventColor($event->status))
            )
            ->toArray();
    }

    protected function getEventColor(string $status): string
    {
        return match ($status) {
            'pendiente' => 'warning',
            'confirmada' => 'primary',
            'cancelada' => 'red',
            'completada' => 'success',
            default => 'red',
        };
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('paciente_id')
                ->label('Paciente')
                ->options(Paciente::all()->mapWithKeys(function ($paciente) {
                    return [$paciente->id => $paciente->nombre . ' ' . $paciente->apellido . ' (CIP: ' . $paciente->cip . ')'];
                }))
                ->required()
                ->reactive()
                ->afterStateUpdated(function (callable $get, callable $set) {
                    $this->validateAvailability($get, $set);
                }),

            Forms\Components\Select::make('motivo_id')
                ->label('Motivo')
                ->options(Servicio::all()->pluck('nombre', 'id'))
                ->required()
                ->reactive()
                ->afterStateUpdated(function (callable $set, $state) {
                    $especialidadId = Servicio::find($state)?->especialidad_id;
                    $set('doctor_id', null);
                    $set('especialidad_id', $especialidadId);
                })
                ->afterStateUpdated(function (callable $get, callable $set) {
                    $this->validateAvailability($get, $set);
                }),

            Forms\Components\Select::make('doctor_id')
                ->label('Doctor')
                ->options(function (callable $get) {
                    $especialidadId = $get('especialidad_id');
                    return Doctor::where('especialidad_id', $especialidadId)->get()
                        ->mapWithKeys(function ($doctor) {
                            return [$doctor->id => $doctor->nombre . ' ' . $doctor->apellido . ' (CIP: ' . $doctor->cip . ')'];
                        });
                })
                ->required()
                ->reactive()
                ->afterStateUpdated(function (callable $get, callable $set) {
                    $this->validateAvailability($get, $set);
                }),

            Forms\Components\DatePicker::make('fecha')
                ->label('Fecha')
                ->required()
                ->minDate(now()->toDateString())
                ->reactive()
                ->afterStateUpdated(function (callable $get, callable $set) {
                    $this->validateAvailability($get, $set);
                }),

            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\TimePicker::make('hora_inicio')
                        ->seconds(false)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            $horaInicio = $get('hora_inicio');
                            $horaFin = $get('hora_fin');

                            if ($horaFin && $horaInicio > $horaFin) {
                                $set('hora_fin', null);
                                Notification::make()
                                    ->title('Error')
                                    ->danger()
                                    ->body('La hora de finalización no puede ser menor que la hora de inicio.')
                                    ->send();
                            }
                        }),

                    Forms\Components\TimePicker::make('hora_fin')
                        ->seconds(false)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            $horaInicio = $get('hora_inicio');
                            $horaFin = $get('hora_fin');

                            if ($horaInicio && $horaFin < $horaInicio) {
                                $set('hora_fin', null);
                                Notification::make()
                                    ->title('Error')
                                    ->danger()
                                    ->body('La hora de finalización no puede ser menor que la hora de inicio.')
                                    ->send();
                            }
                        }),
                ]),

            Forms\Components\Placeholder::make('error')
                ->label('Error')
                ->visible(fn(callable $get) => $get('error') !== null)
                ->content(fn(callable $get) => $get('error')),
            Forms\Components\Placeholder::make('log_message')
                ->label('Log Message')
                ->visible(fn(callable $get) => $get('log_message') !== null)
                ->content(fn(callable $get) => $get('log_message')),
        ];
    }

    protected function validateAvailability(callable $get, callable $set)
    {
        $doctorId = $get('doctor_id');
        $fecha = $get('fecha');
        $horaInicio = $get('hora_inicio');
        $horaFin = $get('hora_fin');
        error_log("Doctor ID: $doctorId, Fecha: $fecha, Hora Inicio: $horaInicio, Hora Fin: $horaFin");
        $set('log_message', "Doctor ID: $doctorId, Fecha: $fecha, Hora Inicio: $horaInicio, Hora Fin: $horaFin");

        if ($doctorId && $fecha && $horaInicio && $horaFin) {
            try {
                $sqlQuery = (new Cita())->validarDisponibilidad([
                    'doctor_id' => $doctorId,
                    'fecha' => $fecha,
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                ]);
                $set('error', null);
                $set('sql_query', $sqlQuery);
            } catch (\Exception $e) {
                $set('error', $e->getMessage());
                $set('sql_query', null);
            }
        }
    }


    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->using(function (array $data) {
                    // Validar disponibilidad usando el modelo
                    $cita = new Cita();

                    if ($cita->isValid($data)) {
                        // Crear la cita si no hay conflictos
                        Cita::create($data);

                        Notification::make()
                            ->title('Cita creada exitosamente')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Error al crear la cita')
                            ->danger()
                            ->body('No se puede crear la cita debido a conflictos.')
                            ->send();
                    }
                })
                ->mountUsing(function (Forms\ComponentContainer $form, array $arguments) {
                    $form->fill([
                        'paciente_id' => null,
                        'doctor_id' => null,
                        'motivo_id' => null,
                        'especialidad_id' => null,
                        'fecha' => $arguments['start'] ?? null,
                        'hora_inicio' => $arguments['start'] ? Carbon::parse($arguments['start'])->format('H:i') : null,
                        'hora_fin' => $arguments['end'] ? Carbon::parse($arguments['end'])->format('H:i') : null,
                    ]);
                }),
        ];
    }

    protected function modalActions(): array
    {
        return [
            EditAction::make()
                ->using(function (Cita $record, array $data) {
                    try {
                        // Validar disponibilidad usando el modelo
                        $record->validarDisponibilidad($data);

                        // Actualizar la cita si no hay conflictos
                        $record->update($data);

                        Notification::make()
                            ->title('Cita actualizada exitosamente')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al actualizar la cita')
                            ->danger()
                            ->body($e->getMessage())
                            ->send();
                    }
                })
                ->mountUsing(function (Cita $record, Forms\ComponentContainer $form, array $arguments) {
                    $especialidadId = Servicio::find($record->motivo_id)?->especialidad_id;
                    $form->fill([
                        'paciente_id' => $record->paciente_id,
                        'doctor_id' => $record->doctor_id,
                        'motivo_id' => $record->motivo_id,
                        'especialidad_id' => $especialidadId,
                        'fecha' => $arguments['event']['start'] ?? $record->fecha,
                        'hora_inicio' => $arguments['event']['start'] ?? $record->hora_inicio,
                        'hora_fin' => $arguments['event']['end'] ?? $record->hora_fin,
                    ]);
                }),
            DeleteAction::make(),
        ];
    }

    public function eventDidMount(): string
    {
        return <<<JS
        function({ event, timeText, isStart, isEnd, isMirror, isPast, isFuture, isToday, el, view }){
            el.setAttribute("x-tooltip", "tooltip");
            el.setAttribute("x-data", "{ tooltip: '"+event.title+"' }");
        }
    JS;
    }
}
