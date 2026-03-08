<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use App\Services\ScheduleService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSchedule extends EditRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = ScheduleService::sanitizeEditData($data);

        $asignacionData = ScheduleService::validateAsignacionEditOrFail($this->record, $data);

        if ($asignacionData !== $data) {
            return $asignacionData;
        }

        if (ScheduleService::shouldValidateConflictOnEdit($this->record, $data)) {
            ScheduleService::validarChoqueHorario($data, $this->record->id);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if (! ScheduleService::shouldNotifyAfterEdit($this->record)) {
            return;
        }

        ScheduleService::sendUpdateEmail($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
