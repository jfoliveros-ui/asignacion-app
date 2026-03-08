@php
    $estado = match ($s->status ?? 'PENDIENTE') {
        'ACEPTADA' => 'Aceptada',
        'RECHAZADA' => 'Rechazada',
        'CANCELADA' => 'Cancelada',
        default => 'Pendiente',
    };
@endphp

@component('mail::message')
# Solicitud de salón registrada ✅

Hemos registrado correctamente tu solicitud de salón.

A continuación encontrarás el resumen de la información enviada:

@component('mail::panel')
**Salón:** {{ $s->salon?->name ?? 'N/A' }}
**Área:** {{ $s->area?->name ?? 'N/A' }}
**Estado actual:** {{ $estado }}
@endcomponent

@component('mail::table')
| Dato | Información |
|:-----|:------------|
| **Solicitante** | {{ $s->nombre ?? 'N/A' }} |
| **Correo** | {{ $s->email ?? 'N/A' }} |
| **Horario** | {{ substr((string) $s->hora_inicio, 0, 5) }} - {{ substr((string) $s->hora_fin, 0, 5) }} |
@endcomponent

@if(!empty($s->fechas_correo) && is_array($s->fechas_correo))
## Fechas solicitadas

@component('mail::table')
|| Fecha |
|:-:|:------|
@foreach($s->fechas_correo as $index => $fecha)
| {{ $index + 1 }} | {{ $fecha }} |
@endforeach
@endcomponent
@else
@component('mail::table')
| Dato | Información |
|:-----|:------------|
| **Fecha solicitada** | {{ $s->fecha ? \Carbon\Carbon::parse($s->fecha)->format('d/m/Y') : 'N/A' }} |
@endcomponent
@endif

@component('mail::panel')
Si necesitas modificar o cancelar esta solicitud, por favor comunícate con el área administrativa correspondiente.
@endcomponent

Gracias,

**Territorial Huila Caquetá y Bajo Putumayo**
@endcomponent
