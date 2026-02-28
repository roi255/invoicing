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
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use App\Filament\Widgets\InvoiceStatsWidget;
use App\Filament\Widgets\RecentInvoicesWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->sidebarWidth('13rem')
            ->maxContentWidth(Width::SevenExtraLarge)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                InvoiceStatsWidget::class,
                RecentInvoicesWidget::class,
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
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => '<style>
                    /* ── Sidebar ────────────────────────────────── */
                    .fi-sidebar-nav {
                        padding: 12px 14px !important;
                        gap: 8px !important;
                    }
                    .fi-sidebar-nav-groups {
                        gap: 8px !important;
                    }
                    .fi-sidebar-group {
                        gap: 2px !important;
                    }
                    .fi-sidebar-group-items {
                        gap: 2px !important;
                    }
                    .fi-sidebar-group-btn {
                        padding: 4px 8px !important;
                    }
                    .fi-sidebar-group-label {
                        font-size: 0.68rem !important;
                        letter-spacing: 0.07em !important;
                        text-transform: uppercase !important;
                    }
                    .fi-sidebar-item-btn {
                        padding: 5px 8px !important;
                        gap: 8px !important;
                    }
                    .fi-sidebar-item-label {
                        font-size: 0.8125rem !important;
                    }
                    .fi-sidebar-item-icon {
                        width: 1.1rem !important;
                        height: 1.1rem !important;
                    }
                    /* ── Main content area ──────────────────────── */
                    .fi-main {
                        padding-left: 12px !important;
                        padding-right: 12px !important;
                    }
                    /* ── Page header ────────────────────────────── */
                    .fi-page-header-main-ctn {
                        padding-top: 14px !important;
                        padding-bottom: 14px !important;
                        gap: 10px !important;
                    }
                    .fi-header {
                        gap: 8px !important;
                    }
                    .fi-header-heading {
                        font-size: 1.2rem !important;
                        line-height: 1.6rem !important;
                    }
                    /* ── Page content ───────────────────────────── */
                    .fi-page-content {
                        row-gap: 10px !important;
                    }
                </style>',
            )
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
