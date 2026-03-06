<?php

namespace App\Filament\Resources\Parameters;

use App\Filament\Resources\Parameters\Pages\CreateParameter;
use App\Filament\Resources\Parameters\Pages\EditParameter;
use App\Filament\Resources\Parameters\Pages\ListParameters;
use App\Filament\Resources\Parameters\Pages\ViewParameter;
use App\Filament\Resources\Parameters\Schemas\ParameterForm;
use App\Filament\Resources\Parameters\Schemas\ParameterInfolist;
use App\Filament\Resources\Parameters\Tables\ParametersTable;
use App\Models\Parameter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ParameterResource extends Resource
{
    protected static ?string $model = Parameter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog8Tooth;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ParameterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ParameterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParametersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getLabel(): ?string
    {
        return 'Parametros'; //Traducir titulo
    }
    public static function getPages(): array
    {
        return [
            'index' => ListParameters::route('/'),
            'create' => CreateParameter::route('/create'),
            'view' => ViewParameter::route('/{record}'),
            'edit' => EditParameter::route('/{record}/edit'),
        ];
    }
}
