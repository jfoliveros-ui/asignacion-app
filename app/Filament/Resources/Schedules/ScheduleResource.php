<?php

namespace App\Filament\Resources\Schedules;

use App\Filament\Resources\Schedules\Pages\CreateSchedule;
use App\Filament\Resources\Schedules\Pages\EditSchedule;
use App\Filament\Resources\Schedules\Pages\ListSchedules;
use App\Filament\Resources\Schedules\Pages\ViewSchedule;
use App\Filament\Resources\Schedules\Schemas\ScheduleForm;
use App\Filament\Resources\Schedules\Schemas\ScheduleInfolist;
use App\Filament\Resources\Schedules\Tables\SchedulesTable;
use App\Filament\Resources\Schedules\Widgets\CalendarWidget;
use App\Models\Schedule;
use App\Services\ScheduleService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;
protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return ScheduleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ScheduleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SchedulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function validarChoqueHorario(array $data, ?int $ignoreId = null): void
    {
        ScheduleService::validarChoqueHorario($data, $ignoreId);
    }

    public static function getWidgets(): array
    {
        return [
            CalendarWidget::class,
        ];
    }

    public static function getLabel(): ?string
    {
        return 'Agendamientos';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Agendamientos';
    }

    public static function getNavigationLabel(): string
    {
        return 'Agendamientos';
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSchedules::route('/'),
            'create' => CreateSchedule::route('/create'),
            'view'   => ViewSchedule::route('/{record}'),
            'edit'   => EditSchedule::route('/{record}/edit'),
        ];
    }
public static function getNavigationBadge(): ?string
{
    return static::getModel()::count();
}
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $areaId = ScheduleService::getAreaFilterForCurrentUser();

        if (filled($areaId)) {
            $query->where('area_id', $areaId);
        }

        return $query;
    }
}
