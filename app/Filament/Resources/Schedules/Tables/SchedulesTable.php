<?php

namespace App\Filament\Resources\Schedules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('salon.name')
                    ->label('Salón')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('area.name')
                    ->label('Área')
                    ->sortable()
                    ->badge()
                    ->searchable(),
                TextColumn::make('nombre')
                    ->label('Nombre Solicitante')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo Institucional')
                    ->searchable(),
                TextColumn::make('fecha')
                    ->label('Fecha Solicitud')
                    ->date()
                    ->sortable(),
                TextColumn::make('hora_inicio')
                    ->time()
                    ->sortable(),
                TextColumn::make('hora_fin')
                    ->time()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'PENDIENTE' => 'Pendiente',
                        'ACEPTADA' => 'Aceptada',
                        'RECHAZADA' => 'Rechazada',
                        'CANCELADA' => 'Cancelada',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'PENDIENTE' => 'warning',
                        'ACEPTADA' => 'success',
                        'RECHAZADA' => 'danger',
                        'CANCELADA' => 'gray',
                        default => 'secondary',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'PENDIENTE' => 'heroicon-o-clock',
                        'ACEPTADA' => 'heroicon-o-check-circle',
                        'RECHAZADA' => 'heroicon-o-x-circle',
                        'CANCELADA' => 'heroicon-o-minus-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->iconPosition('before')
                    ->tooltip(fn (string $state): string => match ($state) {
                        'PENDIENTE' => 'Solicitud en revisión o esperando confirmación.',
                        'ACEPTADA' => 'Solicitud aprobada: el salón queda reservado.',
                        'RECHAZADA' => 'Solicitud no aprobada: no reserva el salón.',
                        'CANCELADA' => 'Solicitud cancelada: libera el salón.',
                        default => 'Estado no definido.',
                    }),
                TextColumn::make('observacion')
                    ->label('Observación')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
