<?php

namespace App\Filament\Resources\Schedules\Schemas;

use App\Models\Parameter;
use App\Models\Schedule;
use App\Services\ScheduleService;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        $isAsignacionPanel = ScheduleService::isAsignacionPanel();
        $fixedAreaId = ScheduleService::getFixedAreaIdForCurrentUser();

        $lockedStatuses = ['ACEPTADA', 'RECHAZADA', 'CANCELADA'];

        return $schema->components([
            Select::make('salon_id')
                ->label('Salón')
                ->options(fn () => Parameter::query()
                    ->where('type', Parameter::TYPE_SALON)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray())
                ->searchable()
                ->preload()
                ->required()
                ->disabled(fn (?Schedule $record) => $isAsignacionPanel && filled($record)),

            Select::make('area_id')
                ->label('Área')
                ->options(fn () => Parameter::query()
                    ->where('type', Parameter::TYPE_AREA)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray())
                ->required()
                ->default(fn () => $fixedAreaId)
                ->disabled(fn () => $isAsignacionPanel && filled($fixedAreaId))
                ->dehydrated(true),

            TextInput::make('nombre')
                ->label('Nombre solicitante')
                ->required()
                ->maxLength(255)
                ->disabled(fn (?Schedule $record) => $isAsignacionPanel && filled($record)),

            TextInput::make('email')
                ->label('Correo institucional')
                ->email()
                ->required()
                ->maxLength(255)
                ->disabled(fn (?Schedule $record) => $isAsignacionPanel && filled($record)),

            Flatpickr::make('fechas')
                ->label('Fechas de solicitud')
                ->multiplePicker()
                ->format('Y-m-d')
                ->displayFormat('d/m/Y')
                ->required(fn (?Schedule $record) => blank($record))
                ->visible(fn (?Schedule $record) => blank($record))
                ->dehydrated(fn (?Schedule $record) => blank($record))
                ->disabled(fn (?Schedule $record) => $isAsignacionPanel && filled($record))
                ->minDate(now()->format('Y-m-d'))
                ->placeholder('Seleccione una o varias fechas'),

            DatePicker::make('fecha')
                ->label('Fecha de solicitud')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->required(fn (?Schedule $record) => filled($record))
                ->visible(fn (?Schedule $record) => filled($record))
                ->dehydrated(fn (?Schedule $record) => filled($record))
                ->disabled(fn (?Schedule $record) => $isAsignacionPanel && filled($record))
                ->minDate(now()->toDateString()),

            Select::make('hora_inicio')
                ->label('Hora de inicio solicitud')
                ->required()
                ->searchable()
                ->preload()
                ->options(function () {
                    $stepMinutes = 30;
                    $options = [];

                    for ($t = 0; $t <= (23 * 60 + 30); $t += $stepMinutes) {
                        $hh = str_pad((string) intdiv($t, 60), 2, '0', STR_PAD_LEFT);
                        $mm = str_pad((string) ($t % 60), 2, '0', STR_PAD_LEFT);
                        $time = "{$hh}:{$mm}";
                        $options[$time] = $time;
                    }

                    return $options;
                })
                ->formatStateUsing(fn ($state) => $state ? substr((string) $state, 0, 5) : null)
                ->live()
                ->afterStateUpdated(fn ($set) => $set('hora_fin', null))
                ->disabled(fn (?Schedule $record) => $isAsignacionPanel && filled($record)),

            Select::make('hora_fin')
                ->label('Hora final solicitud')
                ->required()
                ->searchable()
                ->preload()
                ->disabled(fn ($get, ?Schedule $record) => blank($get('hora_inicio')) || ($isAsignacionPanel && filled($record)))
                ->options(function ($get) {
                    $inicio = $get('hora_inicio');

                    if (blank($inicio)) {
                        return [];
                    }

                    $stepMinutes = 30;
                    [$h, $m] = array_map('intval', explode(':', substr((string) $inicio, 0, 5)));
                    $minStart = ($h * 60) + $m;

                    $options = [];
                    for ($t = $minStart + $stepMinutes; $t <= (23 * 60 + 59); $t += $stepMinutes) {
                        $hh = str_pad((string) intdiv($t, 60), 2, '0', STR_PAD_LEFT);
                        $mm = str_pad((string) ($t % 60), 2, '0', STR_PAD_LEFT);
                        $time = "{$hh}:{$mm}";
                        $options[$time] = $time;
                    }

                    return $options;
                })
                ->formatStateUsing(fn ($state) => $state ? substr((string) $state, 0, 5) : null)
                ->rule(fn ($get) => function ($attribute, $value, $fail) use ($get) {
                    $inicio = $get('hora_inicio');

                    if (blank($inicio) || blank($value)) {
                        return;
                    }

                    if ($value <= $inicio) {
                        $fail('La hora final debe ser mayor que la hora inicial.');
                    }
                }),

            TextInput::make('observacion')
                ->label('Motivo solicitud')
                ->required()
                ->maxLength(500)
                ->disabled(fn (?Schedule $record) => $isAsignacionPanel && filled($record)),

            Select::make('status')
                ->label('Estado solicitud')
                ->options([
                    'PENDIENTE' => 'Pendiente',
                    'ACEPTADA'  => 'Aceptada',
                    'RECHAZADA' => 'Rechazada',
                    'CANCELADA' => 'Cancelada',
                ])
                ->default('PENDIENTE')
                ->required()
                ->hidden(fn (?Schedule $record) => blank($record))
                ->disableOptionWhen(function (string $value, ?Schedule $record) use ($isAsignacionPanel) {
                    if ($isAsignacionPanel && filled($record)) {
                        return $value !== 'CANCELADA';
                    }

                    return false;
                })
                ->disabled(function (?Schedule $record) use ($isAsignacionPanel, $lockedStatuses) {
                    return $isAsignacionPanel
                        && filled($record)
                        && in_array($record->status, $lockedStatuses, true);
                }),
        ]);
    }
}
