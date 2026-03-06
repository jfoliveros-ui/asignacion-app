@component('mail::message')
# Solicitud de salón registrada ✅

Se ha registrado una solicitud de salón con la siguiente información:

- **Solicitante:** {{ $s->nombre ?? 'N/A' }}
- **Correo:** {{ $s->email ?? 'N/A' }}
- **Área:** {{ $s->area?->name ?? 'N/A' }}
- **Salón:** {{ $s->salon?->name ?? 'N/A' }}
- **Fecha:** {{ optional($s->fecha)->format('Y-m-d') ?? 'N/A' }}
- **Hora inicio:** {{ substr((string) $s->hora_inicio, 0, 5) }}
- **Hora fin:** {{ substr((string) $s->hora_fin, 0, 5) }}
- **Estado:** {{ $s->status ?? 'PENDIENTE' }}

Gracias,
{{ config('app.name') }}
@endcomponent
