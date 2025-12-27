<?php

namespace App\Providers\Filament;

use App\Filament\SuperAdmin\Pages\Auth\Login;
use App\Filament\SuperAdmin\Pages\Dashboard;
use App\Filament\SuperAdmin\Widgets\ExpiringSoonWidget;
use App\Filament\SuperAdmin\Widgets\PlanDistributionChart;
use App\Filament\SuperAdmin\Widgets\RecentActivityWidget;
use App\Filament\SuperAdmin\Widgets\RevenueChart;
use App\Filament\SuperAdmin\Widgets\StatsOverviewWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SuperAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('super-admin')
            ->path('super-admin')
            ->login(Login::class)
            ->passwordReset()
            ->colors([
                'primary' => Color::Indigo,
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->font('Cairo')
            ->brandName('TABsense Admin')
            ->brandLogo(asset('images/logo.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.ico'))
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('لوحة التحكم')
                    ->icon('heroicon-o-home')
                    ->collapsed(false),
                NavigationGroup::make()
                    ->label('إدارة العملاء')
                    ->icon('heroicon-o-users')
                    ->collapsed(false),
                NavigationGroup::make()
                    ->label('الاشتراكات')
                    ->icon('heroicon-o-credit-card')
                    ->collapsed(false),
                NavigationGroup::make()
                    ->label('الإعدادات')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(true),
            ])
            ->discoverResources(in: app_path('Filament/SuperAdmin/Resources'), for: 'App\\Filament\\SuperAdmin\\Resources')
            ->discoverPages(in: app_path('Filament/SuperAdmin/Pages'), for: 'App\\Filament\\SuperAdmin\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/SuperAdmin/Widgets'), for: 'App\\Filament\\SuperAdmin\\Widgets')
            ->widgets([
                StatsOverviewWidget::class,
                RevenueChart::class,
                PlanDistributionChart::class,
                ExpiringSoonWidget::class,
                RecentActivityWidget::class,
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
            ->authGuard('super_admin')
            ->authPasswordBroker('super_admins')
            ->loginRouteSlug('login')
            ->registrationRouteSlug('register')
            ->passwordResetRouteSlug('password-reset')
            ->passwordResetRequestRouteSlug('password-reset/request')
            ->emailVerificationRouteSlug('email-verification')
            ->profile(isSimple: false)
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarFullyCollapsibleOnDesktop()
            ->renderHook(
                'panels::body.end',
                fn () => view('filament.super-admin.footer')
            );
    }
}
