<?php

namespace App\Filament\Resources\Schedules\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('salon_id')
                    ->numeric(),
                TextEntry::make('area_id')
                    ->numeric(),
                TextEntry::make('nombre'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('fecha')
                    ->date(),
                TextEntry::make('hora_inicio')
                    ->time(),
                TextEntry::make('hora_fin')
                    ->time(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('observacion'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
