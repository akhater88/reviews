<?php

namespace App\Filament\SuperAdmin\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SystemSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'إعدادات النظام';
    protected static ?string $title = 'إعدادات النظام';
    protected static ?string $navigationGroup = 'الإعدادات';
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.super-admin.pages.system-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = [
            'trial_days' => config('subscription.trial_days', 7),
            'grace_period_days' => config('subscription.grace_period_days', 3),
            'default_currency' => config('subscription.default_currency', 'SAR'),
            'payment_gateway' => config('subscription.payment_gateway', 'manual'),
            'tax_rate' => config('subscription.invoice.tax_rate', 15),
            'maintenance_mode' => app()->isDownForMaintenance(),
        ];

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إعدادات الاشتراكات')
                    ->description('إعدادات الفترات التجريبية والسماح')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('trial_days')
                                    ->label('أيام الفترة التجريبية')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(90)
                                    ->suffix('يوم')
                                    ->helperText('الفترة التجريبية للعملاء الجدد'),

                                TextInput::make('grace_period_days')
                                    ->label('أيام فترة السماح')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(30)
                                    ->suffix('يوم')
                                    ->helperText('الفترة بعد انتهاء الاشتراك'),

                                TextInput::make('tax_rate')
                                    ->label('نسبة الضريبة')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->helperText('ضريبة القيمة المضافة'),
                            ]),
                    ]),

                Section::make('إعدادات الدفع')
                    ->description('إعدادات العملات وبوابات الدفع')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('default_currency')
                                    ->label('العملة الافتراضية')
                                    ->options([
                                        'SAR' => 'ريال سعودي (SAR)',
                                        'USD' => 'دولار أمريكي (USD)',
                                    ])
                                    ->native(false),

                                Select::make('payment_gateway')
                                    ->label('بوابة الدفع')
                                    ->options([
                                        'manual' => 'دفع يدوي',
                                        'stripe' => 'Stripe',
                                        'moyasar' => 'Moyasar',
                                    ])
                                    ->native(false)
                                    ->helperText('يتم تكوينها من ملف .env'),
                            ]),
                    ]),

                Section::make('إعدادات النظام')
                    ->description('إعدادات عامة للمنصة')
                    ->schema([
                        Toggle::make('maintenance_mode')
                            ->label('وضع الصيانة')
                            ->helperText('تفعيل وضع الصيانة سيمنع العملاء من الوصول للمنصة')
                            ->onColor('danger')
                            ->offColor('success'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Handle maintenance mode
        if ($data['maintenance_mode'] && !app()->isDownForMaintenance()) {
            Artisan::call('down', ['--secret' => 'super-admin-bypass']);
        } elseif (!$data['maintenance_mode'] && app()->isDownForMaintenance()) {
            Artisan::call('up');
        }

        // Clear config cache
        Cache::forget('subscription.settings');

        Notification::make()
            ->title('تم حفظ الإعدادات')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ الإعدادات')
                ->submit('save'),
        ];
    }
}
