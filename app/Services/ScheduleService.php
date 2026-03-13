<?php

namespace App\Services;

use App\Mail\SolicitudSalonActualizadaMail;
use App\Mail\SolicitudSalonMail;
use App\Models\Schedule;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ScheduleService
{
    public static function getAreaByEmailMap(): array
    {
        return [
            'academica@admin.com'      => 5,
            'direccion@admin.com'      => 3,
            'capacitaciones@admin.com' => 6,
        ];
    }

    public static function getCurrentPanelId(): ?string
    {
        return Filament::getCurrentPanel()?->getId();
    }

    public static function getCurrentUserEmail(): ?string
    {
        return Auth::user()?->email;
    }

    public static function isAsignacionPanel(): bool
    {
        return self::getCurrentPanelId() === 'asignacion';
    }

    public static function getFixedAreaIdForCurrentUser(): ?int
    {
        $email = self::getCurrentUserEmail();
        $map = self::getAreaByEmailMap();

        return isset($map[$email]) ? $map[$email] : null;
    }

    public static function applyFixedAreaIfNeeded(array $data): array
    {
        if (self::isAsignacionPanel()) {
            $fixedAreaId = self::getFixedAreaIdForCurrentUser();

            if (filled($fixedAreaId)) {
                $data['area_id'] = $fixedAreaId;
            }
        }

        return $data;
    }

    public static function validateRequiredFieldsForConflict(array $data): void
    {
        if (
            empty($data['salon_id']) ||
            empty($data['fecha']) ||
            empty($data['hora_inicio']) ||
            empty($data['hora_fin'])
        ) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'Faltan datos obligatorios para validar el cruce de horario.',
            ]);
        }
    }

    public static function shouldSkipConflictValidation(array $data): bool
    {
        return in_array($data['status'] ?? 'PENDIENTE', ['RECHAZADA', 'CANCELADA'], true);
    }

    public static function validarChoqueHorario(array $data, ?int $ignoreId = null): void
    {
        self::validateRequiredFieldsForConflict($data);

        if (self::shouldSkipConflictValidation($data)) {
            return;
        }

        $conflicto = Schedule::query()
            ->with('salon')
            ->where('salon_id', $data['salon_id'])
            ->whereDate('fecha', $data['fecha'])
            ->whereIn('status', ['PENDIENTE', 'ACEPTADA'])
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('hora_inicio', '<', $data['hora_fin'])
            ->where('hora_fin', '>', $data['hora_inicio'])
            ->orderBy('hora_inicio')
            ->first();

        if (! $conflicto) {
            return;
        }

        $salon  = $conflicto->salon?->name ?? 'salón seleccionado';
        $fecha  = Carbon::parse($conflicto->fecha)->format('d/m/Y');
        $inicio = substr((string) $conflicto->hora_inicio, 0, 5);
        $fin    = substr((string) $conflicto->hora_fin, 0, 5);

        $mensaje = "El salón {$salon} ya tiene una reserva activa el {$fecha} desde {$inicio} hasta {$fin}.";

        Notification::make()
            ->title('Salón ocupado')
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('danger')
            ->body($mensaje)
            ->danger()
            ->persistent()
            ->actions([
                Action::make('Aceptar')
                    ->button()
                    ->color('danger')
                    ->close(),
            ])
            ->send();

        throw ValidationException::withMessages([
            'hora_inicio' => $mensaje,
        ]);
    }

    public static function sanitizeEditData(array $data): array
    {
        unset($data['fechas']);

        return $data;
    }

    public static function validateAsignacionEditOrFail(Schedule $record, array $data): array
    {
        $fixedAreaId = self::getFixedAreaIdForCurrentUser();

        if (! self::isAsignacionPanel() || blank($fixedAreaId)) {
            return $data;
        }

        if (in_array($record->status, ['ACEPTADA', 'RECHAZADA', 'CANCELADA'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Esta solicitud no se puede editar porque ya está ACEPTADA, RECHAZADA o CANCELADA.',
            ]);
        }

        if (($data['status'] ?? null) !== 'CANCELADA') {
            throw ValidationException::withMessages([
                'status' => 'En el panel de asignaciones solo puedes cambiar el estado a CANCELADA.',
            ]);
        }

        return [
            'salon_id'    => $record->salon_id,
            'area_id'     => $fixedAreaId,
            'nombre'      => $record->nombre,
            'email'       => $record->email,
            'fecha'       => $record->fecha,
            'hora_inicio' => $record->hora_inicio,
            'hora_fin'    => $record->hora_fin,
            'observacion' => $record->observacion,
            'status'      => 'CANCELADA',
        ];
    }

    public static function shouldValidateConflictOnEdit(Schedule $record, array $data): bool
    {
        $fechaActual = $record->fecha
            ? Carbon::parse($record->fecha)->format('Y-m-d')
            : null;

        $horaInicioActual = substr((string) $record->hora_inicio, 0, 5);
        $horaFinActual    = substr((string) $record->hora_fin, 0, 5);

        return
            ($data['salon_id'] ?? null) != $record->salon_id ||
            ($data['fecha'] ?? null) != $fechaActual ||
            ($data['hora_inicio'] ?? null) != $horaInicioActual ||
            ($data['hora_fin'] ?? null) != $horaFinActual ||
            (($data['status'] ?? $record->status) !== $record->status);
    }

    public static function normalizeDates(array $fechas): array
    {
        return collect($fechas)
            ->filter(fn ($fecha) => filled($fecha))
            ->map(function ($fecha) {
                try {
                    return Carbon::parse($fecha)->format('Y-m-d');
                } catch (\Throwable $e) {
                    return null;
                }
            })
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public static function createMultipleSchedules(array $data): array
    {
        $data = self::applyFixedAreaIfNeeded($data);

        $fechas = self::normalizeDates($data['fechas'] ?? []);
        unset($data['fechas']);

        if (empty($fechas)) {
            throw ValidationException::withMessages([
                'fechas' => 'Debes seleccionar al menos una fecha válida.',
            ]);
        }

        $created = [];

        DB::transaction(function () use ($data, $fechas, &$created) {
            foreach ($fechas as $fecha) {
                $row = $data;
                $row['fecha'] = $fecha;

                self::validarChoqueHorario($row);

                $created[] = Schedule::create($row);
            }
        });

        return $created;
    }

    public static function formatDatesForEmail(iterable $schedules): array
    {
        return collect($schedules)
            ->pluck('fecha')
            ->filter()
            ->map(fn ($fecha) => Carbon::parse($fecha)->format('d/m/Y'))
            ->values()
            ->all();
    }

    public static function getSalonNotificationEmail(?Schedule $schedule): ?string
    {
        if (! $schedule) {
            return null;
        }

        $email = $schedule->salon?->notification_email;

        return filled($email) ? $email : null;
    }

    public static function sendCreationEmail(iterable $schedules): void
    {
        $collection = collect($schedules);

        if ($collection->isEmpty()) {
            return;
        }

        $first = Schedule::query()
            ->with(['salon', 'area'])
            ->find($collection->first()->id);

        if (! $first || empty($first->email)) {
            return;
        }

        $first->fechas_correo = self::formatDatesForEmail($collection);

        $cc = self::getSalonNotificationEmail($first);

        $mail = Mail::to($first->email);

        if (filled($cc)) {
            $mail->cc($cc);
        }

        $mail->send(new SolicitudSalonMail($first));
    }

    public static function sendUpdateEmail(Schedule $schedule): void
    {
        $schedule = $schedule->fresh()->load(['salon', 'area']);

        if (! $schedule || empty($schedule->email)) {
            return;
        }

        Mail::to($schedule->email)
            ->send(new SolicitudSalonActualizadaMail($schedule));
    }

    public static function shouldNotifyAfterEdit(Schedule $schedule, array $fieldsToNotify = ['status', 'fecha', 'hora_inicio', 'hora_fin']): bool
    {
        return collect($fieldsToNotify)->contains(
            fn ($field) => $schedule->wasChanged($field)
        );
    }

    public static function getAreaFilterForCurrentUser(): ?int
    {
        if (! self::isAsignacionPanel()) {
            return null;
        }

        return self::getFixedAreaIdForCurrentUser();
    }
}