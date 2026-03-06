<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use App\Mail\SolicitudSalonActualizadaMail;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EditSchedule extends EditRecord
{
    protected static string $resource = ScheduleResource::class;

    /**
     * Reglas:
     * - Panel admin: normal (valida choque horario como siempre).
     * - Panel asignacion:
     *   - Filtra area_id por usuario (fijo).
     *   - Solo permite cambiar status a CANCELADA.
     *   - Si el registro está ACEPTADA o RECHAZADA (o CANCELADA) no deja editar.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $panelId = Filament::getCurrentPanel()?->getId();
        $email   = Auth::user()?->email;

        $areaByEmail = [
            'academica@admin.com'      => 5,
            'direccion@admin.com'      => 3,
            'capacitaciones@admin.com' => 6,
        ];

        // ✅ Panel asignacion: reglas especiales
        if ($panelId === 'asignacion' && isset($areaByEmail[$email])) {

            // 1) Si ya está ACEPTADA/RECHAZADA/CANCELADA -> NO editar
            if (in_array($this->record->status, ['ACEPTADA', 'RECHAZADA', 'CANCELADA'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Esta solicitud no se puede editar porque ya está ACEPTADA/RECHAZADA/CANCELADA.',
                ]);
            }

            // 2) Solo permitir cambiar a CANCELADA
            if (($data['status'] ?? null) !== 'CANCELADA') {
                throw ValidationException::withMessages([
                    'status' => 'En el panel de asignaciones solo puedes cambiar el estado a CANCELADA.',
                ]);
            }

            // 3) Forzar area_id fijo (según usuario)
            $data['area_id'] = $areaByEmail[$email];

            // 4) Bloquear cambios a otros campos (aunque manipulen el request)
            //    Solo se guarda status= CANCELADA, lo demás queda igual.
            return [
                'salon_id'    => $this->record->salon_id,
                'area_id'     => $data['area_id'],
                'nombre'      => $this->record->nombre,
                'email'       => $this->record->email,
                'fecha'       => $this->record->fecha,
                'hora_inicio' => $this->record->hora_inicio,
                'hora_fin'    => $this->record->hora_fin,
                'observacion' => $this->record->observacion,
                'status'      => 'CANCELADA',
            ];
        }

        // ✅ Panel admin (o cualquier otro): mantener tu validación normal
        ScheduleResource::validarChoqueHorario($data, $this->record->id);

        return $data;
    }

    protected function afterSave(): void
    {
        // Notificar solo si cambió algo relevante
        $fieldsToNotify = ['status', 'fecha', 'hora_inicio', 'hora_fin'];

        $changed = collect($fieldsToNotify)->contains(fn ($f) => $this->record->wasChanged($f));

        if (! $changed) {
            return;
        }

        $schedule = $this->record->fresh()->load(['salon', 'area']);

        if (! empty($schedule->email)) {
            Mail::to($schedule->email)
                ->cc('jassonoliveros123@gmail.com') // cambia a laura.manrique@esap.edu.co si aplica
                ->send(new SolicitudSalonActualizadaMail($schedule));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            // Si quieres bloquear borrado en panel asignacion, dímelo y lo dejo condicionado.
            DeleteAction::make(),
        ];
    }
}
