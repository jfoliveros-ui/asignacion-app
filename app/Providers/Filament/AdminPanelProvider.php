<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use App\Filament\Pages\Auth\Login;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(Login::class)
            ->colors([
                'primary' => '#1B262C',
            ])
            ->font('https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap')
            ->collapsibleNavigationGroups(false)
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])

            ->authMiddleware([
                Authenticate::class,
            ])

            ->plugins([
            FilamentFullCalendarPlugin::make()
                ->config([
                    'locale' => 'es',
                    'displayEventEnd' => true, // ✅ muestra fin en vista month/dayGrid
                    'eventTimeFormat' => [
                        'hour' => '2-digit',
                        'minute' => '2-digit',
                        'hour12' => false,
                    ],
                ]),
            ])
            ->renderHook('panels::body.start', fn() => '
                <style>
                /* Ocultar hora automática del evento */
                .filament-fullcalendar .fc-event-time {
                    display: none !important;
                }
                .filament-fullcalendar .fc-daygrid-event {
                    white-space: normal !important;
                }
                .fi-logo {
                        height: 3.5rem !important;
                    }
                    .fi-sidebar{
                        background-color: #1B262C !important; /*Cambio de color de la barra lateral */
                    }
                    .fi-sidebar-item-label{
                        color: white !important; /*Cambio de color de texto */
                    }
                    .fi-sidebar-group-label{
                        color: white !important; /*Cambio de color de texto de los grupos */
                    }
                    .fi-sidebar-item-icon{
                        color: white !important; /*Cambio de color de iconos */
                    }
                    .fi-sidebar-item :hover .fi-sidebar-item-label{
                        color: black !important; /*Cambio de color de texto al pasar el mouse */
                    }
                    .fi-sidebar-item :hover .fi-sidebar-item-icon{
                        color: black !important; /*Cambio de color de texto al pasar el mouse */
                    }
                    .fc-h-event .fc-event-main {
                        white-space: normal !important; /*salto de linea en el calendario */
                    }
                        .filament-fullcalendar {
                        --fc-small-font-size : 0.75em !important; /*salto de linea en el calendario */
                    }
                    .fi-layout {
                        background-color: #BBE1FA;
                    }
                    a.fi-breadcrumbs-item-label:nth-child(1){
                        color: #1B262C !important;
                    }
                    a.fi-breadcrumbs-item-label:nth-child(3){
                        color: #1B262C !important;
                    }
                    svg.fi-breadcrumbs-item-separator:nth-child(1) {
                        fill-color: black !important;
                    }
                    .fi-header-heading {
                        font-size: 1.2rem;
                        }
                    .fi-sidebar-item.fi-active .fi-sidebar-item-label {
                        color: black !important; /*Cambio de color de texto al hacer click */
                    }
                        .fi-sidebar-item.fi-active .fi-sidebar-item-icon {
                        color: black !important; /*Cambio de color de iconos al hacer click */
                    }
                </style>');
    }
}
