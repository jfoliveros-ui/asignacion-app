<?php

namespace App\Filament\Resources\Parameters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ParameterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('type')
                    ->label('Tipo')
                    ->required(),
                TextInput::make('name')
                ->label('Valor')
                    ->required(),
                TextInput::make('notification_email')
                ->label('Correo notificación salón')
                    ->email()
                    ->maxLength(255)
                    ->placeholder('correo@esap.edu.co'),
                TextInput::make('meta')
                ->label('Valor Adicional'),
                        ]);
    }
}
