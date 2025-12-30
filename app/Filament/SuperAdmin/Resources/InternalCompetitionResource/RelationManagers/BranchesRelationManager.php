<?php

namespace App\Filament\SuperAdmin\Resources\InternalCompetitionResource\RelationManagers;

use App\Enums\InternalCompetition\CompetitionScope;
use App\Enums\InternalCompetition\ParticipantStatus;
use App\Models\Branch;
use App\Services\InternalCompetition\ParticipantService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BranchesRelationManager extends RelationManager
{
    protected static string $relationship = 'competitionBranches';

    protected static ?string $title = 'الفروع المشاركة';

    protected static ?string $modelLabel = 'فرع';

    protected static ?string $pluralModelLabel = 'الفروع';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('المستأجر')
                    ->searchable()
                    ->visible(fn () => $this->ownerRecord->scope === CompetitionScope::MULTI_TENANT),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),

                Tables\Columns\TextColumn::make('enrolled_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(ParticipantStatus::class),

                Tables\Filters\SelectFilter::make('tenant_id')
                    ->label('المستأجر')
                    ->relationship('tenant', 'name')
                    ->visible(fn () => $this->ownerRecord->scope === CompetitionScope::MULTI_TENANT),
            ])
            ->headerActions([
                Tables\Actions\Action::make('enroll_branch')
                    ->label('إضافة فرع')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Forms\Components\Select::make('branch_id')
                            ->label('الفرع')
                            ->options(function () {
                                $enrolledIds = $this->ownerRecord->competitionBranches()->pluck('branch_id');

                                $query = Branch::whereNotIn('id', $enrolledIds);

                                // Filter by tenant for single tenant competitions
                                if ($this->ownerRecord->scope === CompetitionScope::SINGLE_TENANT) {
                                    $query->where('tenant_id', $this->ownerRecord->tenant_id);
                                } else {
                                    // For multi-tenant, only show branches from enrolled tenants
                                    $enrolledTenantIds = $this->ownerRecord->activeTenants()->pluck('tenant_id');
                                    $query->whereIn('tenant_id', $enrolledTenantIds);
                                }

                                return $query->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->multiple(),
                    ])
                    ->action(function (array $data) {
                        try {
                            $service = app(ParticipantService::class);

                            foreach ((array) $data['branch_id'] as $branchId) {
                                $service->enrollBranch($this->ownerRecord, $branchId);
                            }

                            Notification::make()
                                ->title('تم تسجيل الفروع بنجاح')
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
                            app(ParticipantService::class)->withdrawBranch(
                                $this->ownerRecord,
                                $record->branch_id
                            );
                            Notification::make()
                                ->title('تم سحب الفرع')
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
