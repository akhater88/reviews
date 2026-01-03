<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'المراجعات';

    protected static ?string $modelLabel = 'مراجعة';

    protected static ?string $pluralModelLabel = 'المراجعات';

    protected static ?string $navigationGroup = 'إدارة المراجعات';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المراجعة')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('الفرع')
                            ->relationship('branch', 'name')
                            ->required()
                            ->disabled(),

                        Forms\Components\TextInput::make('reviewer_name')
                            ->label('اسم المراجع')
                            ->disabled(),

                        Forms\Components\TextInput::make('rating')
                            ->label('التقييم')
                            ->disabled(),

                        Forms\Components\Textarea::make('text')
                            ->label('نص المراجعة')
                            ->rows(4)
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('الرد')
                    ->schema([
                        Forms\Components\Textarea::make('owner_reply')
                            ->label('رد المالك')
                            ->rows(4)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('owner_reply_date')
                            ->label('تاريخ الرد')
                            ->disabled(),

                        Forms\Components\Toggle::make('replied_via_tabsense')
                            ->label('تم الرد عبر TABsense')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('التحليل')
                    ->schema([
                        Forms\Components\Select::make('sentiment')
                            ->label('المشاعر')
                            ->options([
                                'positive' => 'إيجابي',
                                'neutral' => 'محايد',
                                'negative' => 'سلبي',
                            ])
                            ->disabled(),

                        Forms\Components\TextInput::make('sentiment_score')
                            ->label('درجة المشاعر')
                            ->disabled(),

                        Forms\Components\Textarea::make('ai_summary')
                            ->label('ملخص AI')
                            ->rows(2)
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reviewer_name')
                    ->label('المراجع')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('rating')
                    ->label('التقييم')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (int $state): string => str_repeat('⭐', $state)),

                Tables\Columns\TextColumn::make('text')
                    ->label('المراجعة')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->text)
                    ->placeholder('تقييم بدون نص'),

                Tables\Columns\TextColumn::make('sentiment')
                    ->label('المشاعر')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'positive' => 'success',
                        'negative' => 'danger',
                        'neutral' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'positive' => 'إيجابي',
                        'negative' => 'سلبي',
                        'neutral' => 'محايد',
                        default => '-',
                    }),

                Tables\Columns\IconColumn::make('owner_reply')
                    ->label('تم الرد')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn ($record) => !empty($record->owner_reply)),

                Tables\Columns\TextColumn::make('review_date')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name'),

                Tables\Filters\SelectFilter::make('rating')
                    ->label('التقييم')
                    ->options([
                        5 => '⭐⭐⭐⭐⭐ (5)',
                        4 => '⭐⭐⭐⭐ (4)',
                        3 => '⭐⭐⭐ (3)',
                        2 => '⭐⭐ (2)',
                        1 => '⭐ (1)',
                    ]),

                Tables\Filters\SelectFilter::make('sentiment')
                    ->label('المشاعر')
                    ->options([
                        'positive' => 'إيجابي',
                        'neutral' => 'محايد',
                        'negative' => 'سلبي',
                    ]),

                Tables\Filters\TernaryFilter::make('has_reply')
                    ->label('تم الرد')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('owner_reply')->where('owner_reply', '!=', ''),
                        false: fn ($query) => $query->where(function ($q) {
                            $q->whereNull('owner_reply')->orWhere('owner_reply', '');
                        }),
                    ),

                Tables\Filters\Filter::make('recent')
                    ->label('آخر 30 يوم')
                    ->query(fn (Builder $query): Builder => $query->where('review_date', '>=', now()->subDays(30))),
            ])
            ->actions([
                // Reply Action - Opens Modal
                Action::make('reply')
                    ->label(fn (Review $record): string => $record->hasOwnerReply() ? 'عرض الرد' : 'رد')
                    ->icon(fn (Review $record): string => $record->hasOwnerReply() ? 'heroicon-o-eye' : 'heroicon-o-chat-bubble-left-ellipsis')
                    ->color(fn (Review $record): string => $record->hasOwnerReply() ? 'success' : 'primary')
                    ->action(function (Review $record, $livewire) {
                        $livewire->dispatch('openReplyModal', reviewId: $record->id);
                    }),

                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
            ])
            ->bulkActions([
                // Add bulk actions if needed
            ])
            ->defaultSort('review_date', 'desc')
            ->emptyStateHeading('لا يوجد مراجعات')
            ->emptyStateDescription('قم بمزامنة المراجعات من صفحة الفروع')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات المراجعة')
                    ->schema([
                        Infolists\Components\TextEntry::make('branch.name')
                            ->label('الفرع'),

                        Infolists\Components\TextEntry::make('reviewer_name')
                            ->label('اسم المراجع'),

                        Infolists\Components\TextEntry::make('rating')
                            ->label('التقييم')
                            ->formatStateUsing(fn (int $state): string => str_repeat('⭐', $state)),

                        Infolists\Components\TextEntry::make('review_date')
                            ->label('تاريخ المراجعة')
                            ->dateTime('Y-m-d H:i'),

                        Infolists\Components\TextEntry::make('language')
                            ->label('اللغة')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'ar' => 'العربية',
                                'en' => 'الإنجليزية',
                                default => $state ?? 'غير محدد',
                            }),
                    ])->columns(3),

                Infolists\Components\Section::make('نص المراجعة')
                    ->schema([
                        Infolists\Components\TextEntry::make('text')
                            ->label('')
                            ->placeholder('تقييم بدون نص')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('رد المالك')
                    ->schema([
                        Infolists\Components\TextEntry::make('owner_reply')
                            ->label('')
                            ->placeholder('لا يوجد رد')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('owner_reply_date')
                            ->label('تاريخ الرد')
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('-'),

                        Infolists\Components\IconEntry::make('replied_via_tabsense')
                            ->label('تم الرد عبر TABsense')
                            ->boolean(),
                    ])->columns(2),

                Infolists\Components\Section::make('التحليل')
                    ->schema([
                        Infolists\Components\TextEntry::make('sentiment')
                            ->label('المشاعر')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'positive' => 'success',
                                'negative' => 'danger',
                                'neutral' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'positive' => 'إيجابي',
                                'negative' => 'سلبي',
                                'neutral' => 'محايد',
                                default => 'غير محدد',
                            }),

                        Infolists\Components\TextEntry::make('sentiment_score')
                            ->label('درجة المشاعر')
                            ->placeholder('-'),

                        Infolists\Components\TextEntry::make('quality_score')
                            ->label('درجة الجودة')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state * 100, 0) . '%' : '-'),

                        Infolists\Components\TextEntry::make('ai_summary')
                            ->label('ملخص AI')
                            ->placeholder('لا يوجد ملخص')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('keywords')
                            ->label('الكلمات المفتاحية')
                            ->badge()
                            ->separator(',')
                            ->placeholder('-'),
                    ])->columns(3),

                Infolists\Components\Section::make('معلومات إضافية')
                    ->schema([
                        Infolists\Components\TextEntry::make('collected_at')
                            ->label('تاريخ الجمع')
                            ->dateTime('Y-m-d H:i'),

                        Infolists\Components\IconEntry::make('is_spam')
                            ->label('سبام')
                            ->boolean(),

                        Infolists\Components\IconEntry::make('is_hidden')
                            ->label('مخفي')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('google_review_id')
                            ->label('معرف المراجعة')
                            ->placeholder('-'),
                    ])->columns(3)
                    ->collapsed(),
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
            'index' => Pages\ListReviews::route('/'),
            'view' => Pages\ViewReview::route('/{record}'),
        ];
    }

    /**
     * Filter reviews based on user access level.
     * Admins see all reviews, managers only see reviews from branches they manage.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user && $user->isManager()) {
            $query->whereIn('branch_id', $user->branches()->pluck('branches.id'));
        }

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
}
