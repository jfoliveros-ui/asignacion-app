@php
    $estado = match ($s->status ?? 'PENDIENTE') {
        'ACEPTADA' => 'Aceptada',
        'RECHAZADA' => 'Rechazada',
        'CANCELADA' => 'Cancelada',
        default => 'Pendiente',
    };
@endphp

@component('mail::message')
# Actualización de solicitud de salón 🔄

Tu solicitud fue actualizada correctamente.

Este es el estado más reciente del registro:

@component('mail::panel')
**Estado actual:** {{ $estado }}
**Salón:** {{ $s->salon?->name ?? 'N/A' }}
**Área:** {{ $s->area?->name ?? 'N/A' }}
@endcomponent

@component('mail::table')
| Dato | Información |
|:-----|:------------|
| **Solicitante** | {{ $s->nombre ?? 'N/A' }} |
| **Correo** | {{ $s->email ?? 'N/A' }} |
| **Fecha solicitada** | {{ $s->fecha ? \Carbon\Carbon::parse($s->fecha)->format('d/m/Y') : 'N/A' }} |
| **Horario** | {{ substr((string) $s->hora_inicio, 0, 5) }} - {{ substr((string) $s->hora_fin, 0, 5) }} |
@endcomponent

@component('mail::panel')
La solicitud presenta una actualización en su información o en su estado. Si requieres más detalles, por favor comunícate con el área administrativa correspondiente.
@endcomponent

Gracias,

**Territorial Huila Caquetá y Bajo Putumayo**
@endcomponent
