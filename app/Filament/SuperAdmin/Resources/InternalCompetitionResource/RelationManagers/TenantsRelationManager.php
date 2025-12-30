<?php

namespace App\Filament\SuperAdmin\Resources\InternalCompetitionResource\RelationManagers;

use App\Enums\InternalCompetition\CompetitionScope;
use App\Enums\InternalCompetition\ParticipantStatus;
use App\Models\Tenant;
use App\Services\InternalCompetition\ParticipantService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TenantsRelationManager extends RelationManager
{
    protected static string $relationship = 'competitionTenants';

    protected static ?string $title = 'المستأجرين المشاركين';

    protected static ?string $modelLabel = 'مستأجر';

    protected static ?string $pluralModelLabel = 'المستأجرين';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->scope === CompetitionScope::MULTI_TENANT;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->label('المستأجر')
                    ->options(function () {
                        $enrolledIds = $this->ownerRecord->competitionTenants()->pluck('tenant_id');
                        return Tenant::whereNotIn('id', $enrolledIds)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tenant.name')
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('المستأجر')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),

                Tables\Columns\TextColumn::make('branches_count')
                    ->label('الفروع المسجلة')
                    ->state(function ($record) {
                        return $this->ownerRecord->activeBranches()
                            ->where('tenant_id', $record->tenant_id)
                            ->count();
                    }),

                Tables\Columns\TextColumn::make('enrolled_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d H:i'),

                Tables\Columns\TextColumn::make('enrolled_by_user.name')
                    ->label('مسجل بواسطة')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(ParticipantStatus::class),
            ])
            ->headerActions([
                Tables\Actions\Action::make('enroll_tenant')
                    ->label('إضافة مستأجر')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Forms\Components\Select::make('tenant_id')
                            ->label('المستأجر')
                            ->options(function () {
                                $enrolledIds = $this->ownerRecord->competitionTenants()->pluck('tenant_id');
                                return Tenant::whereNotIn('id', $enrolledIds)
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable(),

                        Forms\Components\Toggle::make('enroll_all_branches')
                            ->label('تسجيل جميع الفروع تلقائياً')
                            ->default(true),
                    ])
                    ->action(function (array $data) {
                        try {
                            $service = app(ParticipantService::class);
                            $service->enrollTenant($this->ownerRecord, $data['tenant_id']);

                            if ($data['enroll_all_branches'] ?? false) {
                                $service->enrollAllTenantBranches($this->ownerRecord, $data['tenant_id']);
                            }

                            Notification::make()
                                ->title('تم تسجيل المستأجر بنجاح')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('خطأ')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn () => $this->ownerRecord->status->canEdit() || $this->ownerRecord->status->value === 'active'),
            ])
            ->actions([
                Tables\Actions\Action::make('withdraw')
                    ->label('سحب')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            app(ParticipantService::class)->withdrawTenant(
                                $this->ownerRecord,
                                $record->tenant_id
                            );
                            Notification::make()
                                ->title('تم سحب المستأجر')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('خطأ')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => $record->status === ParticipantStatus::ACTIVE),
            ])
            ->bulkActions([]);
    }
}
