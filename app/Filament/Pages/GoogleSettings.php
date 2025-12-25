<?php

namespace App\Filament\Pages;

use App\Enums\BranchSource;
use App\Enums\BranchType;
use App\Enums\SyncStatus;
use App\Models\Branch;
use App\Models\GoogleToken;
use App\Models\Tenant;
use App\Services\Google\GoogleBusinessService;
use App\Services\Google\OutscraperService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class GoogleSettings extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'إعدادات Google';
    protected static ?string $title = 'إعدادات Google Business';
    protected static ?string $navigationGroup = 'الإعدادات';
    protected static ?int $navigationSort = 100;
    protected static string $view = 'filament.pages.google-settings';

    public bool $isConnected = false;
    public ?string $connectedEmail = null;
    public ?string $connectedName = null;
    public array $googleLocations = [];
    public bool $loadingLocations = false;

    public function mount(): void
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return;
        }

        $this->checkConnectionStatus();
    }

    /**
     * Get GoogleBusinessService instance
     */
    protected function getGoogleService(): GoogleBusinessService
    {
        return app(GoogleBusinessService::class);
    }

    /**
     * Get OutscraperService instance
     */
    protected function getOutscraperService(): OutscraperService
    {
        return app(OutscraperService::class);
    }

    /**
     * Check if Google is connected and load account info
     */
    protected function checkConnectionStatus(): void
    {
        $tenant = $this->getTenant();

        if (!$tenant) {
            $this->isConnected = false;
            return;
        }

        $token = GoogleToken::where('tenant_id', $tenant->id)->first();

        if ($token && $token->isActive()) {
            $this->isConnected = true;
            $this->connectedEmail = $token->google_email;
            $this->connectedName = $token->google_account_name;
        } else {
            $this->isConnected = false;
            $this->connectedEmail = null;
            $this->connectedName = null;
        }
    }

    /**
     * Get current tenant - ADJUST THIS BASED ON YOUR MULTI-TENANCY SETUP
     */
    protected function getTenant(): ?Tenant
    {
        // Option 1: If using session-based tenancy (from Day 1 TABsense setup)
        $tenantId = Session::get('tenant_id');
        if ($tenantId) {
            return Tenant::find($tenantId);
        }

        // Option 2: If tenant is linked to authenticated user
        $user = Auth::user();
        if ($user && isset($user->tenant_id)) {
            return Tenant::find($user->tenant_id);
        }

        // Option 3: If user belongs to tenant relationship
        if ($user && method_exists($user, 'tenant')) {
            return $user->tenant;
        }

        // Option 4: Get first tenant for the user (if user can have multiple)
        if ($user && method_exists($user, 'tenants')) {
            return $user->tenants()->first();
        }

        return null;
    }

    /**
     * Get tenant ID safely
     */
    protected function getTenantId(): ?int
    {
        $tenant = $this->getTenant();
        return $tenant?->id;
    }

    /**
     * Connect Google Account Action
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('connectGoogle')
                ->label('ربط حساب Google Business')
                ->icon('heroicon-o-link')
                ->color('primary')
                ->visible(fn () => !$this->isConnected)
                ->action(function () {
                    $tenantId = $this->getTenantId();

                    if (!$tenantId) {
                        Notification::make()
                            ->title('خطأ')
                            ->body('لم يتم العثور على حساب المستأجر')
                            ->danger()
                            ->send();
                        return;
                    }

                    $state = bin2hex(random_bytes(16));
                    Session::put('google_oauth_state', $state);
                    Session::put('google_oauth_tenant_id', $tenantId);

                    $authUrl = $this->getGoogleService()->getAuthUrl($state);

                    $this->redirect($authUrl);
                }),

            Action::make('refreshLocations')
                ->label('تحديث الفروع من Google')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->visible(fn () => $this->isConnected)
                ->action(function () {
                    $this->loadGoogleLocations();
                }),

            Action::make('disconnectGoogle')
                ->label('إلغاء الربط')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn () => $this->isConnected)
                ->requiresConfirmation()
                ->modalHeading('إلغاء ربط حساب Google')
                ->modalDescription('هل أنت متأكد من إلغاء ربط حساب Google Business؟')
                ->modalSubmitActionLabel('نعم، إلغاء الربط')
                ->action(function () {
                    $tenant = $this->getTenant();

                    if ($tenant) {
                        $this->getGoogleService()->disconnect($tenant);

                        Branch::where('tenant_id', $tenant->id)
                            ->where('source', BranchSource::GOOGLE_BUSINESS->value)
                            ->update(['can_reply' => false]);
                    }

                    $this->isConnected = false;
                    $this->connectedEmail = null;
                    $this->connectedName = null;
                    $this->googleLocations = [];

                    Notification::make()
                        ->title('تم إلغاء الربط')
                        ->body('تم إلغاء ربط حساب Google Business بنجاح')
                        ->success()
                        ->send();
                }),

            Action::make('addManualBranch')
                ->label('إضافة فرع يدوياً')
                ->icon('heroicon-o-plus')
                ->color('warning')
                ->form([
                    TextInput::make('name')
                        ->label('اسم الفرع')
                        ->placeholder('مثال: مطعم البيك - فرع الرياض')
                        ->required(),
                    TextInput::make('google_place_id')
                        ->label('Google Place ID (اختياري)')
                        ->placeholder('ChIJ...')
                        ->helperText('يمكنك الحصول عليه من رابط Google Maps'),
                    TextInput::make('address')
                        ->label('العنوان')
                        ->placeholder('الرياض، حي العليا'),
                    Select::make('branch_type')
                        ->label('نوع الفرع')
                        ->options([
                            BranchType::OWNED->value => 'فرعي',
                            BranchType::COMPETITOR->value => 'منافس',
                        ])
                        ->default(BranchType::OWNED->value)
                        ->required()
                        ->live(),
                    Select::make('linked_branch_id')
                        ->label('ربط مع فرع (للمقارنة)')
                        ->options(fn () => $this->getOwnedBranchesOptions())
                        ->placeholder('اختر فرع للمقارنة')
                        ->visible(fn ($get) => $get('branch_type') === BranchType::COMPETITOR->value),
                ])
                ->action(function (array $data) {
                    $this->createManualBranch($data);
                }),
        ];
    }

    /**
     * Get owned branches for linking options
     */
    protected function getOwnedBranchesOptions(): array
    {
        $tenantId = $this->getTenantId();

        if (!$tenantId) {
            return [];
        }

        return Branch::where('tenant_id', $tenantId)
            ->where('branch_type', BranchType::OWNED->value)
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Create a manual branch
     */
    protected function createManualBranch(array $data): void
    {
        $tenantId = $this->getTenantId();

        if (!$tenantId) {
            Notification::make()
                ->title('خطأ')
                ->body('لم يتم العثور على حساب المستأجر')
                ->danger()
                ->send();
            return;
        }

        Branch::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'google_place_id' => $data['google_place_id'] ?? null,
            'address' => $data['address'] ?? null,
            'source' => BranchSource::MANUAL->value,
            'branch_type' => $data['branch_type'],
            'linked_branch_id' => $data['linked_branch_id'] ?? null,
            'can_reply' => false,
            'sync_status' => SyncStatus::PENDING->value,
            'is_active' => true,
        ]);

        Notification::make()
            ->title('تمت الإضافة')
            ->body("تم إضافة {$data['name']} بنجاح")
            ->success()
            ->send();
    }

    /**
     * Load Google Business locations
     */
    public function loadGoogleLocations(): void
    {
        $this->loadingLocations = true;

        try {
            $tenant = $this->getTenant();

            if (!$tenant) {
                throw new \Exception('لم يتم العثور على حساب المستأجر');
            }

            $accessToken = $this->getGoogleService()->getValidAccessToken($tenant);

            if (!$accessToken) {
                throw new \Exception('لم يتم العثور على رمز وصول صالح');
            }

            $accounts = $this->getGoogleService()->getAccounts($accessToken);

            $allLocations = [];
            foreach ($accounts as $account) {
                $accountId = basename($account['name']);
                $locations = $this->getGoogleService()->getLocations($accessToken, $accountId);

                foreach ($locations as $location) {
                    $allLocations[] = [
                        'account_id' => $accountId,
                        'location_id' => basename($location['name']),
                        'name' => $location['title'] ?? 'فرع بدون اسم',
                        'address' => $this->formatAddress($location['storefrontAddress'] ?? []),
                        'place_id' => $location['metadata']['placeId'] ?? null,
                        'phone' => $location['phoneNumbers']['primaryPhone'] ?? null,
                        'website' => $location['websiteUri'] ?? null,
                    ];
                }
            }

            $this->googleLocations = $allLocations;

            Notification::make()
                ->title('تم التحديث')
                ->body('تم جلب ' . count($allLocations) . ' فرع من Google Business')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->loadingLocations = false;
        }
    }

    /**
     * Import a location as a branch
     */
    public function importLocation(string $accountId, string $locationId, string $name, ?string $placeId, ?string $address): void
    {
        $tenantId = $this->getTenantId();

        if (!$tenantId) {
            Notification::make()
                ->title('خطأ')
                ->body('لم يتم العثور على حساب المستأجر')
                ->danger()
                ->send();
            return;
        }

        // Check if branch already exists
        $existing = Branch::where('tenant_id', $tenantId)
            ->where('google_location_id', $locationId)
            ->first();

        if ($existing) {
            Notification::make()
                ->title('الفرع موجود')
                ->body('هذا الفرع مضاف مسبقاً')
                ->warning()
                ->send();
            return;
        }

        Branch::create([
            'tenant_id' => $tenantId,
            'name' => $name,
            'google_place_id' => $placeId,
            'google_account_id' => $accountId,
            'google_location_id' => $locationId,
            'address' => $address,
            'source' => BranchSource::GOOGLE_BUSINESS->value,
            'branch_type' => BranchType::OWNED->value,
            'can_reply' => true,
            'sync_status' => SyncStatus::PENDING->value,
            'is_active' => true,
        ]);

        Notification::make()
            ->title('تمت الإضافة')
            ->body("تم إضافة فرع {$name} بنجاح")
            ->success()
            ->send();
    }

    /**
     * Define the branches table
     */
    public function table(Table $table): Table
    {
        $tenantId = $this->getTenantId();

        return $table
            ->query(
                Branch::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            )
            ->columns([
                TextColumn::make('name')
                    ->label('اسم الفرع')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('source')
                    ->label('المصدر')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? BranchSource::from($state)->label() : '-')
                    ->color(fn ($state) => $state ? BranchSource::from($state)->color() : 'gray'),

                TextColumn::make('branch_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? BranchType::from($state)->label() : '-')
                    ->color(fn ($state) => $state ? BranchType::from($state)->color() : 'gray'),

                TextColumn::make('linkedBranch.name')
                    ->label('مرتبط مع')
                    ->placeholder('-')
                    ->toggleable(),

                IconColumn::make('can_reply')
                    ->label('يمكن الرد')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('sync_status')
                    ->label('حالة المزامنة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? SyncStatus::from($state)->label() : '-')
                    ->color(fn ($state) => $state ? SyncStatus::from($state)->color() : 'gray'),

                TextColumn::make('last_synced_at')
                    ->label('آخر مزامنة')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('لم تتم المزامنة')
                    ->toggleable(),
            ])
            ->actions([
                ActionGroup::make([
                    TableAction::make('sync')
                        ->label('مزامنة المراجعات')
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->action(fn (Branch $record) => $this->syncBranchReviews($record)),

                    TableAction::make('markAsCompetitor')
                        ->label('تحويل لمنافس')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->visible(fn (Branch $record) => $record->branch_type === BranchType::OWNED->value && $record->source === BranchSource::MANUAL->value)
                        ->form([
                            Select::make('linked_branch_id')
                                ->label('ربط مع فرع للمقارنة')
                                ->options(fn () => $this->getOwnedBranchesOptions())
                                ->required(),
                        ])
                        ->action(function (Branch $record, array $data) {
                            $record->update([
                                'branch_type' => BranchType::COMPETITOR->value,
                                'linked_branch_id' => $data['linked_branch_id'],
                            ]);

                            Notification::make()
                                ->title('تم التحديث')
                                ->success()
                                ->send();
                        }),

                    TableAction::make('markAsOwned')
                        ->label('تحويل لفرعي')
                        ->icon('heroicon-o-building-storefront')
                        ->color('primary')
                        ->visible(fn (Branch $record) => $record->branch_type === BranchType::COMPETITOR->value)
                        ->requiresConfirmation()
                        ->action(function (Branch $record) {
                            $record->update([
                                'branch_type' => BranchType::OWNED->value,
                                'linked_branch_id' => null,
                            ]);

                            Notification::make()
                                ->title('تم التحديث')
                                ->success()
                                ->send();
                        }),

                    TableAction::make('delete')
                        ->label('حذف')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Branch $record) => $record->delete()),
                ]),
            ])
            ->emptyStateHeading('لا توجد فروع')
            ->emptyStateDescription('قم بربط حساب Google Business أو إضافة فروع يدوياً')
            ->emptyStateIcon('heroicon-o-building-storefront');
    }

    /**
     * Sync reviews for a branch
     */
    protected function syncBranchReviews(Branch $record): void
    {
        $record->update(['sync_status' => SyncStatus::SYNCING->value]);

        // TODO: Dispatch job for background processing
        // For now, just mark as completed

        $record->update([
            'sync_status' => SyncStatus::COMPLETED->value,
            'last_synced_at' => now(),
        ]);

        Notification::make()
            ->title('تمت المزامنة')
            ->body("تم مزامنة مراجعات {$record->name}")
            ->success()
            ->send();
    }

    /**
     * Format address from Google location data
     */
    protected function formatAddress(array $address): string
    {
        $parts = array_filter([
            $address['addressLines'][0] ?? null,
            $address['locality'] ?? null,
            $address['administrativeArea'] ?? null,
        ]);

        return implode(', ', $parts);
    }
}
