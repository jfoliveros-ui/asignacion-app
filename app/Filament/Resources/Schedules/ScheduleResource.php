<?php

namespace App\Filament\Resources\Schedules;

use App\Filament\Resources\Schedules\Pages\CreateSchedule;
use App\Filament\Resources\Schedules\Pages\EditSchedule;
use App\Filament\Resources\Schedules\Pages\ListSchedules;
use App\Filament\Resources\Schedules\Pages\ViewSchedule;
use App\Filament\Resources\Schedules\Schemas\ScheduleForm;
use App\Filament\Resources\Schedules\Schemas\ScheduleInfolist;
use App\Filament\Resources\Schedules\Tables\SchedulesTable;
use App\Models\Schedule;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;
use App\Filament\Resources\Schedules\Widgets\CalendarWidget;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static ?string $recordTitleAttribute = 'name';

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
    // ✅ Si la solicitud que se está guardando NO es "activa", no valida choques
    if (in_array($data['status'] ?? 'PENDIENTE', ['RECHAZADA', 'CANCELADA'], true)) {
        return;
    }

    $conflicto = Schedule::query()
        ->where('salon_id', $data['salon_id'])
        ->whereDate('fecha', $data['fecha'])
        ->whereIn('status', ['PENDIENTE', 'ACEPTADA']) // ✅ solo estos bloquean
        ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
        // ✅ Cruce de horas
        ->where('hora_inicio', '<', $data['hora_fin'])
        ->where('hora_fin', '>', $data['hora_inicio'])
        ->orderBy('hora_inicio')
        ->first();

    if (! $conflicto) {
        return;
    }

    $inicio = substr((string) $conflicto->hora_inicio, 0, 5);
    $fin    = substr((string) $conflicto->hora_fin, 0, 5);

    Notification::make()
        ->title('🚫 Salón ocupado')
        ->icon('heroicon-o-exclamation-triangle')
        ->iconColor('danger')
        ->body("El salón 🏫 {$conflicto->salon->name} ya está reservado desde {$inicio} hasta {$fin}.")
        ->danger()
        ->persistent()
        ->actions([
            Action::make('Aceptar')->button()->color('danger')->close(),
        ])
        ->send();

    throw ValidationException::withMessages([
        'hora_inicio' => "La sala ya está ocupada desde {$inicio} hasta {$fin}.",
    ]);
}

    public static function getWidgets(): array
    {
        return [
            CalendarWidget::class,
        ];
    }

    public static function getLabel(): ?string
    {
        return 'Agendamientos'; //Traducir titulo
    }
    public static function getPages(): array
    {
        return [
            'index' => ListSchedules::route('/'),
            'create' => CreateSchedule::route('/create'),
            'view' => ViewSchedule::route('/{record}'),
            'edit' => EditSchedule::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    $panelId = Filament::getCurrentPanel()?->getId();
    // use Auth facade to satisfy static analysis
    $email   = Auth::user()?->email;

    // ✅ Mapa de correos a área
    $areaByEmail = [
        'academica@admin.com'      => 5,
        'direccion@admin.com'      => 3,
        'capacitaciones@admin.com' => 6,
    ];

    // Solo aplica en el panel asignacion
    if ($panelId === 'asignacion' && isset($areaByEmail[$email])) {
        $query->where('area_id', $areaByEmail[$email]);
    }

    return $query;
}

}
