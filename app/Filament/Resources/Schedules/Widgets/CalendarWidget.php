<?php

namespace App\Filament\Resources\Schedules\Widgets;

use App\Models\Parameter;
use App\Models\Schedule;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    protected static ?string $heading = 'Calendario de Reservas';

    public ?int $salonFiltro = null;

    public function getViewData(): array
    {
        return [];
    }

    /**
     * Config normal (SIN JS aquí)
     */
    public function config(): array
    {
        return [
            'firstDay' => 1,
            'locale' => 'es',
        ];
    }

    protected function headerActions(): array
    {
        return [
            Action::make('filtrarSalon')
                ->label(fn () => $this->salonFiltro
                            ? 'Salón: ' . Parameter::find($this->salonFiltro)?->name
                            : 'Filtrar salón'
                        )
                ->icon('heroicon-o-funnel')
                ->form([
                    Select::make('salon_id')
                        ->label('Salón')
                        ->options(fn () => Parameter::query()
                            ->where('type', Parameter::TYPE_SALON)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->salonFiltro = (int) $data['salon_id'];

                    // Browser event (DOM)
                    $this->dispatch('calendar-refetch-events');
                }),

            Action::make('quitarFiltro')
                ->label('Quitar filtro')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->visible(fn () => filled($this->salonFiltro))
                ->action(function (): void {
                    $this->salonFiltro = null;

                    $this->dispatch('calendar-refetch-events');
                }),
        ];
    }

    /**
     * ✅ Hook soportado por el plugin (Render Hooks): aquí sí va JS.
     * Usamos esto para guardar la instancia del calendario y forzar refetch al aplicar filtro.
     */
    public function eventDidMount(): string
{
    return <<<JS
        function(info) {
            const calendar = info.view.calendar;

            // Guardar instancia del calendario (una vez)
            if (!window.__esapRoomCalendar) {
                window.__esapRoomCalendar = calendar;
            }

            // Registrar listener una sola vez
            if (!window.__esapRoomCalendarListener) {
                window.__esapRoomCalendarListener = true;

                window.addEventListener('calendar-refetch-events', () => {
                    window.__esapRoomCalendar?.refetchEvents();
                });
            }

            // ✅ Badge de estado (month)
            const status = info.event.extendedProps?.status;
            if (!status) return;

            // Evitar duplicados si vuelve a montar
            if (info.el.querySelector('.esap-status-badge')) return;

            const config = {
                'ACEPTADA':  { text: 'ACEPTADA',  bg: '#22c55e', fg: '#ffffff' },
                'PENDIENTE': { text: 'PENDIENTE', bg: '#f59e0b', fg: '#111827' },
                'RECHAZADA': { text: 'RECHAZADA', bg: '#ef4444', fg: '#ffffff' },
                'CANCELADA': { text: 'CANCELADA', bg: '#6b7280', fg: '#ffffff' },
            };

            const c = config[status] ?? { text: status, bg: '#6b7280', fg: '#ffffff' };

            const badge = document.createElement('span');
            badge.className = 'esap-status-badge';
            badge.textContent = c.text;

            // Estilos badge
            badge.style.display = 'inline-flex';
            badge.style.alignItems = 'center';
            badge.style.padding = '1px 6px';
            badge.style.borderRadius = '999px';
            badge.style.fontSize = '10px';
            badge.style.fontWeight = '700';
            badge.style.marginRight = '6px';
            badge.style.background = c.bg;
            badge.style.color = c.fg;
            badge.style.lineHeight = '1.2';
            badge.style.verticalAlign = 'middle';

            // Insertar antes del título (month)
            const titleEl =
                info.el.querySelector('.fc-event-title') ||
                info.el.querySelector('.fc-event-title-container') ||
                info.el;

            titleEl.prepend(badge);

            // Tooltip con el estado
            info.el.setAttribute('title', 'Estado: ' + c.text);
        }
    JS;
}
    public function fetchEvents(array $fetchInfo): array
{
    $start = Carbon::parse($fetchInfo['start'])->toDateString();
    $end   = Carbon::parse($fetchInfo['end'])->toDateString();

    return Schedule::query()
        ->with(['salon:id,name,meta', 'area:id,name'])
        ->whereBetween('fecha', [$start, $end])
        ->whereIn('status', ['ACEPTADA', 'PENDIENTE'])
        ->when($this->salonFiltro, fn ($q) => $q->where('salon_id', $this->salonFiltro))
        ->get()
        ->map(function (Schedule $s) {
            $salon  = $s->salon?->name ?? 'Salón';
            $area   = $s->area?->name ?? 'Área';
            $inicio = substr((string) $s->hora_inicio, 0, 5);
            $fin    = substr((string) $s->hora_fin, 0, 5);

            $color = $this->salonColor($s->salon?->meta, (int) $s->salon_id);

            return [
                'id' => $s->id,
                'title' => "{$salon} - {$area} ({$inicio}-{$fin})", // ✅ title limpio
                'start' => $s->fecha->format('Y-m-d') . 'T' . $inicio,
                'end'   => $s->fecha->format('Y-m-d') . 'T' . $fin,

                // ✅ enviar status para el badge
                'extendedProps' => [
                    'status' => $s->status,
                ],

                'backgroundColor' => $color,
                'borderColor'     => $color,
            ];
        })
        ->toArray();
}

    private function salonColor(?string $meta, int $salonId): string
    {
        if (is_string($meta) && preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', trim($meta))) {
            return trim($meta);
        }

        $palette = ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#a855f7', '#06b6d4', '#f97316', '#14b8a6'];
        return $palette[$salonId % count($palette)];
    }
}
