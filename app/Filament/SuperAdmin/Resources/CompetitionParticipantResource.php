<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\CompetitionParticipantResource\Pages;
use App\Models\Competition\CompetitionParticipant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompetitionParticipantResource extends Resource
{
    protected static ?string $model = CompetitionParticipant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'المسابقة';

    protected static ?string $navigationLabel = 'المشاركون';

    protected static ?string $modelLabel = 'مشارك';

    protected static ?string $pluralModelLabel = 'المشاركون';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المشارك')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الجوال')
                            ->required()
                            ->tel()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),

                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('city')
                            ->label('المدينة')
                            ->maxLength(100),

                        Forms\Components\DateTimePicker::make('phone_verified_at')
                            ->label('تاريخ التحقق')
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Section::make('الحالة')
                    ->schema([
                        Forms\Components\Toggle::make('is_blocked')
                            ->label('محظور')
                            ->helperText('حظر المشارك من المشاركة في المسابقة'),

                        Forms\Components\Textarea::make('blocked_reason')
                            ->label('سبب الحظر')
                            ->rows(2)
                            ->visible(fn ($get) => $get('is_blocked')),

                        Forms\Components\Toggle::make('whatsapp_opted_in')
                            ->label('موافق على واتساب')
                            ->default(true),

                        Forms\Components\Toggle::make('sms_opted_in')
                            ->label('موافق على SMS')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('الإحالة')
                    ->schema([
                        Forms\Components\TextInput::make('referral_code')
                            ->label('كود الإحالة')
                            ->disabled()
                            ->maxLength(20),

                        Forms\Components\Select::make('referred_by_id')
                            ->label('أُحيل بواسطة')
                            ->relationship('referrer', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الجوال')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => substr($state, 0, 4) . '****' . substr($state, -4)),

                Tables\Columns\TextColumn::make('city')
                    ->label('المدينة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nominations_count')
                    ->label('الترشيحات')
                    ->counts('nominations')
                    ->sortable(),

                Tables\Columns\IconColumn::make('phone_verified_at')
                    ->label('موثق')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->phone_verified_at !== null),

                Tables\Columns\IconColumn::make('is_blocked')
                    ->label('محظور')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('referrals_count')
                    ->label('الإحالات')
                    ->counts('referrals')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التسجيل')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_blocked')
                    ->label('محظور'),

                Tables\Filters\TernaryFilter::make('phone_verified')
                    ->label('موثق')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('phone_verified_at'),
                        false: fn ($query) => $query->whereNull('phone_verified_at'),
                    ),

                Tables\Filters\SelectFilter::make('city')
                    ->label('المدينة')
                    ->options(fn () => CompetitionParticipant::distinct()->pluck('city', 'city')->filter()),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('block')
                        ->label('حظر')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('blocked_reason')
                                ->label('سبب الحظر')
                                ->required(),
                        ])
                        ->visible(fn (CompetitionParticipant $record) => ! $record->is_blocked)
                        ->action(function (CompetitionParticipant $record, array $data) {
                            $record->update([
                                'is_blocked' => true,
                                'blocked_reason' => $data['blocked_reason'],
                                'blocked_at' => now(),
                            ]);
                        }),

                    Tables\Actions\Action::make('unblock')
                        ->label('إلغاء الحظر')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (CompetitionParticipant $record) => $record->is_blocked)
                        ->action(fn (CompetitionParticipant $record) => $record->update([
                            'is_blocked' => false,
                            'blocked_reason' => null,
                            'blocked_at' => null,
                        ])),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('blockSelected')
                        ->label('حظر المحدد')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('blocked_reason')
                                ->label('سبب الحظر')
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each->update([
                                'is_blocked' => true,
                                'blocked_reason' => $data['blocked_reason'],
                                'blocked_at' => now(),
                            ]);
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompetitionParticipants::route('/'),
            'create' => Pages\CreateCompetitionParticipant::route('/create'),
            'view' => Pages\ViewCompetitionParticipant::route('/{record}'),
            'edit' => Pages\EditCompetitionParticipant::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }
}
