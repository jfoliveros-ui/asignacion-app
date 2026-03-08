<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de salón registrada</title>
</head>
<body style="margin:0; padding:0; background:#f3f6fb; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
@php
    $estadoRaw = $s->status ?? 'PENDIENTE';

    $estado = match ($estadoRaw) {
        'ACEPTADA' => 'Aceptada',
        'RECHAZADA' => 'Rechazada',
        'CANCELADA' => 'Cancelada',
        default => 'Pendiente',
    };

    $estadoColor = match ($estadoRaw) {
        'ACEPTADA' => '#0f9d58',
        'RECHAZADA' => '#d93025',
        'CANCELADA' => '#b45309',
        default => '#3366CC',
    };

    $estadoBg = match ($estadoRaw) {
        'ACEPTADA' => '#e9f7ef',
        'RECHAZADA' => '#fdecec',
        'CANCELADA' => '#fff4e5',
        default => '#eef4ff',
    };
@endphp

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f6fb; padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="720" cellspacing="0" cellpadding="0" style="max-width:720px; width:100%; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 8px 24px rgba(20,74,138,0.10);">

                <tr>
                    <td bgcolor="#144A8A" style="background-color:#144A8A; padding:32px 40px; color:#ffffff;">
                        <div style="font-size:13px; letter-spacing:1px; text-transform:uppercase; margin-bottom:8px; color:#dbeafe;">
                            Escuela Superior de Administración Pública
                        </div>
                        <div style="font-size:30px; font-weight:bold; line-height:1.2; margin-bottom:10px; color:#ffffff;">
                            Solicitud de salón registrada
                        </div>
                        <div style="font-size:15px; line-height:1.7; color:#eff6ff;">
                            Su solicitud fue registrada correctamente en el sistema de agendamiento institucional.
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px 40px 8px 40px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="background:#f8fbff; border:1px solid #d7e5fb; border-radius:14px; padding:18px 20px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td style="font-size:15px; color:#144A8A; font-weight:bold;">
                                                Resumen de la solicitud
                                            </td>
                                            <td align="right">
                                                <span style="display:inline-block; background:{{ $estadoBg }}; color:{{ $estadoColor }}; border:1px solid {{ $estadoColor }}33; padding:6px 12px; border-radius:999px; font-size:12px; font-weight:bold;">
                                                    {{ $estado }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>

                                    <div style="font-size:15px; line-height:1.9; color:#1f2937; margin-top:12px;">
                                        <strong>Salón:</strong> {{ $s->salon?->name ?? 'N/A' }}<br>
                                        <strong>Área:</strong> {{ $s->area?->name ?? 'N/A' }}<br>
                                        <strong>Horario:</strong> {{ substr((string) $s->hora_inicio, 0, 5) }} - {{ substr((string) $s->hora_fin, 0, 5) }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 40px 8px 40px;">
                        <div style="font-size:19px; font-weight:bold; color:#144A8A; margin-bottom:14px;">
                            Información del solicitante
                        </div>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; border:1px solid #dbe4f0; border-radius:14px; overflow:hidden;">
                            <tr>
                                <td style="width:210px; background:#f8fbff; padding:14px 16px; border-bottom:1px solid #dbe4f0; font-weight:bold; color:#144A8A;">Solicitante</td>
                                <td style="padding:14px 16px; border-bottom:1px solid #dbe4f0;">{{ $s->nombre ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td style="background:#f8fbff; padding:14px 16px; font-weight:bold; color:#144A8A;">Correo institucional</td>
                                <td style="padding:14px 16px;">{{ $s->email ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 40px 8px 40px;">
                        <div style="font-size:19px; font-weight:bold; color:#144A8A; margin-bottom:14px;">
                            Fechas solicitadas
                        </div>

                        @if(!empty($s->fechas_correo) && is_array($s->fechas_correo))
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; border:1px solid #dbe4f0; border-radius:14px; overflow:hidden;">
                                <tr style="background:#f8fbff;">
                                    <td style="padding:12px 16px; border-bottom:1px solid #dbe4f0; font-weight:bold; width:70px; color:#144A8A;">#</td>
                                    <td style="padding:12px 16px; border-bottom:1px solid #dbe4f0; font-weight:bold; color:#144A8A;">Fecha</td>
                                </tr>
                                @foreach($s->fechas_correo as $index => $fecha)
                                    <tr>
                                        <td style="padding:12px 16px; border-bottom:1px solid #dbe4f0;">{{ $index + 1 }}</td>
                                        <td style="padding:12px 16px; border-bottom:1px solid #dbe4f0;">{{ $fecha }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        @else
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; border:1px solid #dbe4f0; border-radius:14px; overflow:hidden;">
                                <tr>
                                    <td style="width:210px; background:#f8fbff; padding:14px 16px; font-weight:bold; color:#144A8A;">Fecha solicitada</td>
                                    <td style="padding:14px 16px;">{{ $s->fecha ? \Carbon\Carbon::parse($s->fecha)->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                            </table>
                        @endif
                    </td>
                </tr>

                <tr>
                    <td style="padding:18px 40px 22px 40px;">
                        <div style="background:#eef4ff; border-left:5px solid #3366CC; border-radius:10px; padding:16px 18px; color:#344054; font-size:14px; line-height:1.7;">
                            Si necesita modificar o cancelar esta solicitud, por favor comuníquese con el área administrativa correspondiente.
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 38px 28px 38px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-top:3px solid #3366CC; background:#f8fbff; border-radius:0 0 14px 14px;">
                            <tr>
                                <td style="padding:22px 18px; width:170px; vertical-align:top;">
                                    <img src="{{ $message->embed(public_path('img/firma.png')) }}" alt="Firma ESAP" style="display:block; width:100%; max-width:640px; border:0; outline:none; text-decoration:none;">
                                </td>
                                <td style="padding:22px 0; width:2px;">
                                    <div style="width:2px; height:140px; background:#999999;"></div>
                                </td>
                                <td style="padding:22px 18px; vertical-align:top;">
                                    <div style="font-size:13px; color:#666666; margin-bottom:4px;">Sistema</div>
                                    <div style="font-size:15px; font-weight:bold; color:#000000; margin-bottom:10px;">Agendamiento</div>

                                    <div style="font-size:14px; color:#222222; line-height:1.8;">
                                        Grupo de Administración Pública Territorial OTIC<br>
                                        <a href="mailto:director.huila@esap.edu.co" style="color:#144A8A; text-decoration:none;">director.huila@esap.edu.co</a><br>
                                        Carrera 10 # 06-27<br>
                                        <a href="https://www.esap.edu.co" style="color:#144A8A; text-decoration:none;">www.esap.edu.co</a>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
