<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use App\Services\ScheduleService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSchedule extends CreateRecord
{
    protected static string $resource = ScheduleResource::class;

    protected array $createdSchedules = [];

    protected function handleRecordCreation(array $data): Model
    {
        $this->createdSchedules = ScheduleService::createMultipleSchedules($data);

        return $this->createdSchedules[0];
    }

    protected function afterCreate(): void
    {
        ScheduleService::sendCreationEmail($this->createdSchedules);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return count($this->createdSchedules) > 1
            ? 'Las solicitudes fueron creadas correctamente.'
            : 'La solicitud fue creada correctamente.';
    }
}
