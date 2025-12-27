<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Enums\SubscriptionStatus;
use App\Filament\SuperAdmin\Resources\TenantResource\Pages;
use App\Filament\SuperAdmin\Resources\TenantResource\RelationManagers;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'العملاء';
    protected static ?string $modelLabel = 'عميل';
    protected static ?string $pluralModelLabel = 'العملاء';
    protected static ?string $navigationGroup = 'إدارة العملاء';
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tenant')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('معلومات العميل')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('اسم الشركة/المطعم')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('مطعم الشرق')
                                                    ->columnSpan(1),

                                                Forms\Components\TextInput::make('email')
                                                    ->label('البريد الإلكتروني')
                                                    ->email()
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(255)
                                                    ->placeholder('info@restaurant.com')
                                                    ->columnSpan(1),

                                                Forms\Components\TextInput::make('phone')
                                                    ->label('رقم الهاتف')
                                                    ->tel()
                                                    ->maxLength(20)
                                                    ->placeholder('+966 5X XXX XXXX')
                                                    ->columnSpan(1),

                                                Forms\Components\TextInput::make('slug')
                                                    ->label('النطاق الفرعي')
                                                    ->prefix('https://')
                                                    ->suffix('.tabsense.com')
                                                    ->maxLength(50)
                                                    ->unique(ignoreRecord: true)
                                                    ->placeholder('myrestaurant')
                                                    ->columnSpan(1),
                                            ]),

                                        Forms\Components\FileUpload::make('logo')
                                            ->label('شعار الشركة')
                                            ->image()
                                            ->directory('tenant-logos')
                                            ->maxSize(2048)
                                            ->imageResizeMode('cover')
                                            ->imageCropAspectRatio('1:1')
                                            ->imageResizeTargetWidth('200')
                                            ->imageResizeTargetHeight('200'),

                                        Forms\Components\Toggle::make('is_active')
                                            ->label('نشط')
                                            ->default(true)
                                            ->helperText('إلغاء التفعيل سيمنع جميع مستخدمي العميل من الدخول'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('معلومات الفوترة')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('billing_email')
                                            ->label('بريد الفوترة')
                                            ->email()
                                            ->maxLength(255)
                                            ->placeholder('billing@restaurant.com')
                                            ->helperText('اتركه فارغاً لاستخدام البريد الرئيسي'),

                                        Forms\Components\Textarea::make('billing_address')
                                            ->label('عنوان الفوترة')
                                            ->rows(3)
                                            ->maxLength(500),

                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('tax_number')
                                                    ->label('الرقم الضريبي')
                                                    ->maxLength(50)
                                                    ->placeholder('300000000000003'),

                                                Forms\Components\Select::make('country_code')
                                                    ->label('الدولة')
                                                    ->options([
                                                        'SA' => 'السعودية',
                                                        'AE' => 'الإمارات',
                                                        'KW' => 'الكويت',
                                                        'BH' => 'البحرين',
                                                        'OM' => 'عمان',
                                                        'QA' => 'قطر',
                                                        'EG' => 'مصر',
                                                        'JO' => 'الأردن',
                                                    ])
                                                    ->default('SA')
                                                    ->native(false),

                                                Forms\Components\Select::make('preferred_currency')
                                                    ->label('العملة المفضلة')
                                                    ->options([
                                                        'SAR' => 'ريال سعودي (SAR)',
                                                        'USD' => 'دولار أمريكي (USD)',
                                                    ])
                                                    ->default('SAR')
                                                    ->native(false),
                                            ]),

                                        Forms\Components\Select::make('timezone')
                                            ->label('المنطقة الزمنية')
                                            ->options([
                                                'Asia/Riyadh' => 'الرياض (GMT+3)',
                                                'Asia/Dubai' => 'دبي (GMT+4)',
                                                'Asia/Kuwait' => 'الكويت (GMT+3)',
                                                'Africa/Cairo' => 'القاهرة (GMT+2)',
                                                'Asia/Amman' => 'عمّان (GMT+3)',
                                            ])
                                            ->default('Asia/Riyadh')
                                            ->native(false),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('مسؤول الحساب')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->description('سيتم إنشاء حساب مسؤول تلقائياً للعميل الجديد')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('admin_name')
                                                    ->label('اسم المسؤول')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('أحمد محمد')
                                                    ->visible(fn ($operation) => $operation === 'create'),

                                                Forms\Components\TextInput::make('admin_email')
                                                    ->label('بريد المسؤول')
                                                    ->email()
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('admin@restaurant.com')
                                                    ->visible(fn ($operation) => $operation === 'create'),

                                                Forms\Components\TextInput::make('admin_phone')
                                                    ->label('هاتف المسؤول')
                                                    ->tel()
                                                    ->maxLength(20)
                                                    ->placeholder('+966 5X XXX XXXX')
                                                    ->visible(fn ($operation) => $operation === 'create'),

                                                Forms\Components\Toggle::make('send_credentials')
                                                    ->label('إرسال بيانات الدخول')
                                                    ->default(true)
                                                    ->helperText('إرسال بريد إلكتروني ببيانات الدخول للمسؤول')
                                                    ->visible(fn ($operation) => $operation === 'create'),
                                            ]),
                                    ])
                                    ->visible(fn ($operation) => $operation === 'create'),

                                // Show existing users for edit
                                Forms\Components\Section::make('مستخدمو الحساب')
                                    ->description('قائمة المستخدمين المرتبطين بهذا العميل')
                                    ->schema([
                                        Forms\Components\Placeholder::make('users_list')
                                            ->content(function (?Model $record) {
                                                if (!$record) return 'لا يوجد مستخدمين';

                                                $users = $record->users()->get();
                                                if ($users->isEmpty()) return 'لا يوجد مستخدمين';

                                                return view('filament.super-admin.components.users-list', [
                                                    'users' => $users
                                                ]);
                                            }),
                                    ])
                                    ->visible(fn ($operation) => $operation === 'edit'),
                            ]),

                        Forms\Components\Tabs\Tab::make('الاشتراك')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Select::make('plan_id')
                                            ->label('الباقة')
                                            ->options(Plan::active()->pluck('name_ar', 'id'))
                                            ->required()
                                            ->native(false)
                                            ->visible(fn ($operation) => $operation === 'create')
                                            ->helperText('سيتم إنشاء اشتراك تجريبي بهذه الباقة'),

                                        Forms\Components\Toggle::make('start_trial')
                                            ->label('بدء فترة تجريبية')
                                            ->default(true)
                                            ->helperText(fn () => 'فترة تجريبية ' . config('subscription.trial_days', 7) . ' يوم')
                                            ->visible(fn ($operation) => $operation === 'create'),

                                        // Show current subscription for edit
                                        Forms\Components\Placeholder::make('current_subscription')
                                            ->label('الاشتراك الحالي')
                                            ->content(function (?Model $record) {
                                                if (!$record || !$record->currentSubscription) {
                                                    return 'لا يوجد اشتراك';
                                                }

                                                $sub = $record->currentSubscription;
                                                return view('filament.super-admin.components.subscription-info', [
                                                    'subscription' => $sub
                                                ]);
                                            })
                                            ->visible(fn ($operation) => $operation === 'edit'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=6366f1&color=fff')
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->email),

                Tables\Columns\TextColumn::make('currentSubscription.plan.name_ar')
                    ->label('الباقة')
                    ->badge()
                    ->color(fn ($record) => $record->currentSubscription?->plan?->color ?? 'gray')
                    ->default('بدون باقة'),

                Tables\Columns\TextColumn::make('currentSubscription.status')
                    ->label('حالة الاشتراك')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? 'غير مشترك')
                    ->color(fn ($state) => $state?->color() ?? 'gray'),

                Tables\Columns\TextColumn::make('branches_count')
                    ->label('الفروع')
                    ->counts('branches')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('المستخدمين')
                    ->counts('users')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('currentSubscription.expires_at')
                    ->label('ينتهي في')
                    ->date('Y-m-d')
                    ->sortable()
                    ->color(fn ($record) =>
                        $record->currentSubscription?->isExpiringSoon() ? 'warning' : 'gray'
                    )
                    ->description(fn ($record) =>
                        $record->currentSubscription?->daysUntilExpiry()
                            ? $record->currentSubscription->daysUntilExpiry() . ' يوم متبقي'
                            : null
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subscription_status')
                    ->label('حالة الاشتراك')
                    ->options([
                        'trial' => 'فترة تجريبية',
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغي',
                        'none' => 'بدون اشتراك',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!$data['value']) return $query;

                        return match($data['value']) {
                            'none' => $query->whereDoesntHave('currentSubscription'),
                            default => $query->whereHas('currentSubscription', fn ($q) =>
                                $q->where('status', $data['value'])
                            ),
                        };
                    }),

                Tables\Filters\SelectFilter::make('plan')
                    ->label('الباقة')
                    ->relationship('currentSubscription.plan', 'name_ar'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->placeholder('الكل'),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('ينتهي قريباً')
                    ->query(fn (Builder $query) =>
                        $query->whereHas('currentSubscription', fn ($q) =>
                            $q->whereNotNull('expires_at')
                              ->where('expires_at', '<=', now()->addDays(7))
                              ->where('expires_at', '>', now())
                        )
                    ),

                Tables\Filters\Filter::make('created_this_month')
                    ->label('مسجل هذا الشهر')
                    ->query(fn (Builder $query) =>
                        $query->whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year)
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('عرض'),

                    Tables\Actions\EditAction::make()
                        ->label('تعديل'),

                    Tables\Actions\Action::make('impersonate')
                        ->label('الدخول كعميل')
                        ->icon('heroicon-o-arrow-right-on-rectangle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('الدخول كعميل')
                        ->modalDescription(fn ($record) => "سيتم تسجيل دخولك كمسؤول لـ {$record->name}")
                        ->modalSubmitActionLabel('دخول')
                        ->action(function ($record) {
                            $admin = $record->users()->where('role', 'admin')->first();

                            if (!$admin) {
                                Notification::make()
                                    ->title('لا يوجد مسؤول لهذا العميل')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Store super admin session to return later
                            session(['impersonating_from' => auth()->guard('super_admin')->id()]);

                            // Login as tenant admin
                            auth()->guard('web')->login($admin);

                            Notification::make()
                                ->title("تم الدخول كـ {$admin->name}")
                                ->success()
                                ->send();

                            return redirect('/admin');
                        })
                        ->visible(fn ($record) => $record->is_active && $record->users()->exists()),

                    Tables\Actions\Action::make('toggle_active')
                        ->label(fn ($record) => $record->is_active ? 'تعطيل' : 'تفعيل')
                        ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->modalHeading(fn ($record) => $record->is_active ? 'تعطيل العميل' : 'تفعيل العميل')
                        ->modalDescription(fn ($record) => $record->is_active
                            ? "سيتم منع جميع مستخدمي {$record->name} من الدخول"
                            : "سيتم السماح لمستخدمي {$record->name} بالدخول"
                        )
                        ->action(function ($record) {
                            $record->update(['is_active' => !$record->is_active]);

                            Notification::make()
                                ->title($record->is_active ? 'تم تفعيل العميل' : 'تم تعطيل العميل')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('send_credentials')
                        ->label('إرسال بيانات الدخول')
                        ->icon('heroicon-o-envelope')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('إرسال بيانات الدخول')
                        ->modalDescription('سيتم إرسال بريد إلكتروني ببيانات الدخول الجديدة للمسؤول')
                        ->form([
                            Forms\Components\Select::make('user_id')
                                ->label('المستخدم')
                                ->options(fn ($record) => $record->users()->pluck('name', 'id'))
                                ->required(),
                            Forms\Components\Toggle::make('reset_password')
                                ->label('إعادة تعيين كلمة المرور')
                                ->default(true),
                        ])
                        ->action(function ($record, array $data) {
                            $user = User::find($data['user_id']);

                            $password = null;
                            if ($data['reset_password']) {
                                $password = Str::random(12);
                                $user->update(['password' => Hash::make($password)]);
                            }

                            // Send email
                            app(TenantProvisioningService::class)
                                ->sendCredentialsEmail($user, $password, $record);

                            Notification::make()
                                ->title('تم إرسال بيانات الدخول')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->label('حذف')
                        ->requiresConfirmation()
                        ->modalHeading('حذف العميل')
                        ->modalDescription('سيتم حذف العميل وجميع بياناته نهائياً. هذا الإجراء لا يمكن التراجع عنه.'),
                ])
                ->label('إجراءات')
                ->icon('heroicon-m-ellipsis-vertical')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل المحددين')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('تعطيل المحددين')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحددين'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Group::make([
                    Infolists\Components\Section::make('معلومات العميل')
                        ->schema([
                            Infolists\Components\ImageEntry::make('logo')
                                ->label('')
                                ->circular()
                                ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=6366f1&color=fff&size=128'),

                            Infolists\Components\TextEntry::make('name')
                                ->label('الاسم'),

                            Infolists\Components\TextEntry::make('email')
                                ->label('البريد الإلكتروني')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('phone')
                                ->label('الهاتف')
                                ->default('غير محدد'),

                            Infolists\Components\TextEntry::make('slug')
                                ->label('النطاق')
                                ->formatStateUsing(fn ($state) => $state ? "{$state}.tabsense.com" : 'غير محدد'),

                            Infolists\Components\IconEntry::make('is_active')
                                ->label('الحالة')
                                ->boolean()
                                ->trueIcon('heroicon-o-check-circle')
                                ->falseIcon('heroicon-o-x-circle'),

                            Infolists\Components\TextEntry::make('created_at')
                                ->label('تاريخ التسجيل')
                                ->dateTime('Y-m-d H:i'),
                        ])
                        ->columns(2),

                    Infolists\Components\Section::make('معلومات الفوترة')
                        ->schema([
                            Infolists\Components\TextEntry::make('billing_email')
                                ->label('بريد الفوترة')
                                ->default(fn ($record) => $record->email),

                            Infolists\Components\TextEntry::make('billing_address')
                                ->label('العنوان')
                                ->default('غير محدد'),

                            Infolists\Components\TextEntry::make('tax_number')
                                ->label('الرقم الضريبي')
                                ->default('غير محدد'),

                            Infolists\Components\TextEntry::make('country_code')
                                ->label('الدولة')
                                ->formatStateUsing(fn ($state) => match($state) {
                                    'SA' => 'السعودية',
                                    'AE' => 'الإمارات',
                                    'KW' => 'الكويت',
                                    default => $state,
                                }),

                            Infolists\Components\TextEntry::make('preferred_currency')
                                ->label('العملة'),

                            Infolists\Components\TextEntry::make('timezone')
                                ->label('المنطقة الزمنية'),
                        ])
                        ->columns(3)
                        ->collapsible(),
                ])
                ->columnSpan(2),

                Infolists\Components\Group::make([
                    Infolists\Components\Section::make('الاشتراك')
                        ->schema([
                            Infolists\Components\TextEntry::make('currentSubscription.plan.name_ar')
                                ->label('الباقة')
                                ->badge()
                                ->color(fn ($record) => $record->currentSubscription?->plan?->color ?? 'gray')
                                ->default('بدون باقة'),

                            Infolists\Components\TextEntry::make('currentSubscription.status')
                                ->label('الحالة')
                                ->badge()
                                ->formatStateUsing(fn ($state) => $state?->label() ?? 'غير مشترك')
                                ->color(fn ($state) => $state?->color() ?? 'gray'),

                            Infolists\Components\TextEntry::make('currentSubscription.billing_cycle')
                                ->label('دورة الفوترة')
                                ->formatStateUsing(fn ($state) => $state?->label() ?? '-'),

                            Infolists\Components\TextEntry::make('currentSubscription.started_at')
                                ->label('تاريخ البدء')
                                ->date('Y-m-d')
                                ->default('-'),

                            Infolists\Components\TextEntry::make('currentSubscription.expires_at')
                                ->label('تاريخ الانتهاء')
                                ->date('Y-m-d')
                                ->default('-')
                                ->color(fn ($record) =>
                                    $record->currentSubscription?->isExpiringSoon() ? 'warning' : null
                                ),

                            Infolists\Components\TextEntry::make('subscription_days')
                                ->label('الأيام المتبقية')
                                ->state(fn ($record) =>
                                    $record->currentSubscription?->daysUntilExpiry() ?? 0
                                )
                                ->suffix(' يوم')
                                ->color(fn ($state) => $state <= 7 ? 'warning' : 'success'),
                        ]),

                    Infolists\Components\Section::make('الإحصائيات')
                        ->schema([
                            Infolists\Components\TextEntry::make('branches_count')
                                ->label('عدد الفروع')
                                ->state(fn ($record) => $record->branches()->count())
                                ->badge()
                                ->color('info'),

                            Infolists\Components\TextEntry::make('users_count')
                                ->label('عدد المستخدمين')
                                ->state(fn ($record) => $record->users()->count())
                                ->badge()
                                ->color('success'),

                            Infolists\Components\TextEntry::make('reviews_count')
                                ->label('عدد المراجعات')
                                ->state(fn ($record) => $record->reviews()->count())
                                ->badge()
                                ->color('warning'),
                        ])
                        ->columns(3),
                ])
                ->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\BranchesRelationManager::class,
            RelationManagers\SubscriptionsRelationManager::class,
            RelationManagers\InvoicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'view' => Pages\ViewTenant::route('/{record}'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone', 'slug'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'البريد' => $record->email,
            'الباقة' => $record->currentSubscription?->plan?->name_ar ?? 'بدون باقة',
        ];
    }
}
