<?php

namespace App\Filament\Resources\Schedules\Schemas;

use App\Models\Parameter;
use App\Models\Schedule;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        // Panel / usuario actual
        $panelId = Filament::getCurrentPanel()?->getId();
        $email   = Auth::user()?->email;

        $isAsignacionPanel = $panelId === 'asignacion';

        // Mapa de correos a area_id (panel asignacion)
        $areaByEmail = [
            'academica@admin.com'      => 5,
            'direccion@admin.com'      => 3,
            'capacitaciones@admin.com' => 6,
        ];

        // Área fija si el usuario está en el mapa (solo asignacion)
        $fixedAreaId = ($isAsignacionPanel && isset($areaByEmail[$email]))
            ? $areaByEmail[$email]
            : null;

        // Estados bloqueados para edición en panel asignacion
        $lockedStatuses = ['ACEPTADA', 'RECHAZADA', 'CANCELADA'];

        return $schema->components([
            Select::make('salon_id')
                ->label('Salón')
                ->options(fn () => Parameter::query()
                    ->where('type', Parameter::TYPE_SALON)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray()
                )
                ->searchable()
                ->required()
                // En panel asignacion: no permitir editar campos distintos a status (solo en EDIT)
                ->disabled(fn (?Schedule $record) => $isAsignacionPanel && filled($record)),

            Select::make('area_id')
                ->label('Área')
                ->options(fn () => Parameter::query()
                        ->where('type', Parameter::TYPE_AREA)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                ->required()
                ->default(fn () => $fixedAreaId)
                // ✅ bloqueado también en CREATE (área fija por usuario)
                ->disabled(fn () => $isAsignacionPanel && filled($fixedAreaId))
                ->dehydrated(true),

            TextInput::make('nombre')
                ->label('Nombre solicitante')
                ->required()
                ->disabled(fn (?Schedule $record) => $isAsignacionPanel && filled($record)),

            TextInput::make('email')
                ->label('Correo Institucional')
                ->email()
                ->required()
                ->disabled(fn (?Schedule $record) => $isAsignacionPanel && filled($record)),

            DatePicker::make('fecha')
                ->label('Fecha de solicitud')
                ->required()
                ->disabled(fn (?Schedule $record) => $isAsignacionPanel && filled($record)),

            Select::make('hora_inicio')
                ->label('Hora de Inicio Solicitud')
                ->required()
                ->searchable()
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
                ->label('Hora de Final Solicitud')
                ->required()
                ->searchable()
                // ✅ SOLO bloquear en EDIT del panel asignacion (en CREATE debe permitir)
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
                ->label('Motivo Solicitud')
                ->required()
                ->disabled(fn (?Schedule $record) => $isAsignacionPanel && filled($record)),

            Select::make('status')
                ->label('Estado Solicitud')
                ->options([
                    'PENDIENTE' => 'Pendiente',
                    'ACEPTADA'  => 'Aceptada',
                    'RECHAZADA' => 'Rechazada',
                    'CANCELADA' => 'Cancelada',
                ])
                ->default('PENDIENTE')
                ->required()
                ->hidden(fn (?Schedule $record) => blank($record)) // ✅ ocultar en CREATE
                ->disableOptionWhen(function (string $value, ?Schedule $record) use ($isAsignacionPanel) {
                    if ($isAsignacionPanel && filled($record)) {
                        return $value !== 'CANCELADA';
                    }
                    return false;
                })
                ->disabled(function (?Schedule $record) use ($isAsignacionPanel, $lockedStatuses) {
                    return $isAsignacionPanel && filled($record) && in_array($record->status, $lockedStatuses, true);
                }),
        ]);
    }
}
