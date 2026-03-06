@component('mail::message')
# Actualización de solicitud de salón 🔄

Tu solicitud fue actualizada y quedó en estado: **{{ $s->status }}**

**Detalles:**
- **Solicitante:** {{ $s->nombre ?? 'N/A' }}
- **Correo:** {{ $s->email ?? 'N/A' }}
- **Área:** {{ $s->area?->name ?? 'N/A' }}
- **Salón:** {{ $s->salon?->name ?? 'N/A' }}
- **Fecha:** {{ optional($s->fecha)->format('Y-m-d') ?? 'N/A' }}
- **Hora inicio:** {{ substr((string) $s->hora_inicio, 0, 5) }}
- **Hora fin:** {{ substr((string) $s->hora_fin, 0, 5) }}

Gracias,
{{ config('app.name') }}
@endcomponent
