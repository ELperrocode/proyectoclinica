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
                fn(Cita $event) => EventData::make()
                    ->id($event->id)
                    ->title($event->paciente->nombre)
                    ->start($event->fecha . 'T' . $event->hora_inicio)
                    ->end($event->fecha . 'T' . $event->hora_fin)
                    ->backgroundColor($this->getEventColor($event->status))
            )
            ->toArray();
    }

    protected function getEventColor(string $status): string
    {
        return match ($status) {
            'pendiente' => 'blue',
            'confirmada' => 'green',
            'cancelada' => 'red',
            'completada' => 'gray',
            default => 'blue',
        };
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('paciente_id')
                ->label('Paciente')
                ->options(Paciente::all()->pluck('nombre', 'id'))
                ->required(),

            Forms\Components\Select::make('motivo_id')
                ->label('Motivo')
                ->options(Servicio::all()->pluck('nombre', 'id'))
                ->required()
                ->reactive()
                ->afterStateUpdated(function (callable $set, $state) {
                    $especialidadId = Servicio::find($state)?->especialidad_id;
                    $set('doctor_id', null);
                    $set('especialidad_id', $especialidadId);
                }),

            Forms\Components\Select::make('doctor_id')
                ->label('Doctor')
                ->relationship('doctor', 'nombre', function ($query, $get) {
                    $especialidadId = $get('especialidad_id');
                    return $query->where('especialidad_id', $especialidadId);
                })
                ->required(),

            Forms\Components\DatePicker::make('fecha')
                ->label('Fecha')
                ->required()
                ->minDate(now()->toDateString()),

            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\TimePicker::make('hora_inicio')
                        ->seconds(false)
                        ->required(),

                    Forms\Components\TimePicker::make('hora_fin')
                        ->seconds(false)
                        ->required(),
                ]),
        ];
    }

    protected function modalActions(): array
    {
        return [
            EditAction::make()
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

    protected function headerActions(): array
    {
        return [
            CreateAction::make()
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
