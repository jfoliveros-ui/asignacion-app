<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use Filament\Resources\Pages\CreateRecord;
use App\Mail\SolicitudSalonMail;
use Illuminate\Support\Facades\Mail;
use Filament\Facades\Filament;

class CreateSchedule extends CreateRecord
{
    protected static string $resource = ScheduleResource::class;
    protected function afterCreate(): void
{
    $schedule = $this->record->load(['salon', 'area']); // ✅ sin 'user'

    $to = $schedule->email; // ✅ viene del formulario

    if (! empty($to)) {
        Mail::to($to)
            ->cc('jassonoliveros123@gmail.com') // CC a la coordinadora
            ->send(new SolicitudSalonMail($schedule));
    }
}
    protected function mutateFormDataBeforeCreate(array $data): array
{
    $panelId = Filament::getCurrentPanel()?->getId();
    $email   = \Illuminate\Support\Facades\Auth::user()?->email;

    $areaByEmail = [
        'academica@admin.com'      => 5,
        'direccion@admin.com'      => 3,
        'capacitaciones@admin.com' => 6,
    ];

    if ($panelId === 'asignacion' && isset($areaByEmail[$email])) {
        $data['area_id'] = $areaByEmail[$email];
    }
    ScheduleResource::validarChoqueHorario($data);
    return $data;
}


}
