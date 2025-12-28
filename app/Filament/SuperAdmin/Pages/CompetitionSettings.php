<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Models\Competition\CompetitionSetting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class CompetitionSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'المسابقة';

    protected static ?string $navigationLabel = 'إعدادات المسابقة';

    protected static ?string $title = 'إعدادات المسابقة';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.super-admin.pages.competition-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getSettings());
    }

    protected function getSettings(): array
    {
        $settings = CompetitionSetting::all()->mapWithKeys(fn ($s) => [$s->key => $s->value])->toArray();

        return $settings;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('عام')
                            ->icon('heroicon-o-cog')
                            ->schema([
                                Forms\Components\Toggle::make('is_enabled')
                                    ->label('تفعيل المسابقة')
                                    ->helperText('تفعيل أو إيقاف المسابقة بشكل كامل')
                                    ->default(true),

                                Forms\Components\TextInput::make('competition_title')
                                    ->label('عنوان المسابقة')
                                    ->default('Best Restaurant Competition'),

                                Forms\Components\TextInput::make('competition_title_ar')
                                    ->label('عنوان المسابقة (عربي)')
                                    ->default('مسابقة أفضل مطعم'),

                                Forms\Components\Textarea::make('competition_description')
                                    ->label('وصف المسابقة')
                                    ->rows(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('الجوائز')
                            ->icon('heroicon-o-trophy')
                            ->schema([
                                Forms\Components\TextInput::make('prize_1')
                                    ->label('الجائزة الأولى')
                                    ->numeric()
                                    ->prefix('ر.س')
                                    ->default(2000),

                                Forms\Components\TextInput::make('prize_2')
                                    ->label('الجائزة الثانية')
                                    ->numeric()
                                    ->prefix('ر.س')
                                    ->default(1500),

                                Forms\Components\TextInput::make('prize_3')
                                    ->label('الجائزة الثالثة')
                                    ->numeric()
                                    ->prefix('ر.س')
                                    ->default(1000),

                                Forms\Components\TextInput::make('prize_others')
                                    ->label('جائزة المرشح')
                                    ->numeric()
                                    ->prefix('ر.س')
                                    ->default(500),

                                Forms\Components\TextInput::make('winner_count')
                                    ->label('عدد الفائزين')
                                    ->numeric()
                                    ->default(10),
                            ])->columns(3),

                        Forms\Components\Tabs\Tab::make('الشروط')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->schema([
                                Forms\Components\TextInput::make('min_reviews')
                                    ->label('الحد الأدنى للمراجعات')
                                    ->numeric()
                                    ->default(10)
                                    ->helperText('عدد المراجعات المطلوبة للمشاركة'),

                                Forms\Components\TextInput::make('min_rating')
                                    ->label('الحد الأدنى للتقييم')
                                    ->numeric()
                                    ->step(0.1)
                                    ->default(1.0),

                                Forms\Components\TextInput::make('max_nominations_per_participant')
                                    ->label('أقصى ترشيحات للمشارك')
                                    ->numeric()
                                    ->default(1)
                                    ->helperText('لكل فترة'),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('النقاط')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Forms\Components\TextInput::make('score_weight_rating')
                                    ->label('وزن التقييم (%)')
                                    ->numeric()
                                    ->default(25)
                                    ->suffix('%'),

                                Forms\Components\TextInput::make('score_weight_sentiment')
                                    ->label('وزن المشاعر (%)')
                                    ->numeric()
                                    ->default(30)
                                    ->suffix('%'),

                                Forms\Components\TextInput::make('score_weight_response')
                                    ->label('وزن معدل الرد (%)')
                                    ->numeric()
                                    ->default(15)
                                    ->suffix('%'),

                                Forms\Components\TextInput::make('score_weight_volume')
                                    ->label('وزن الحجم (%)')
                                    ->numeric()
                                    ->default(10)
                                    ->suffix('%'),

                                Forms\Components\TextInput::make('score_weight_trend')
                                    ->label('وزن الاتجاه (%)')
                                    ->numeric()
                                    ->default(10)
                                    ->suffix('%'),

                                Forms\Components\TextInput::make('score_weight_keywords')
                                    ->label('وزن الكلمات (%)')
                                    ->numeric()
                                    ->default(10)
                                    ->suffix('%'),
                            ])->columns(3),

                        Forms\Components\Tabs\Tab::make('الإشعارات')
                            ->icon('heroicon-o-bell')
                            ->schema([
                                Forms\Components\Toggle::make('notify_winners_whatsapp')
                                    ->label('إشعار الفائزين (واتساب)')
                                    ->default(true),

                                Forms\Components\Toggle::make('notify_winners_email')
                                    ->label('إشعار الفائزين (بريد)')
                                    ->default(false),

                                Forms\Components\Toggle::make('notify_nominators')
                                    ->label('إشعار المرشحين')
                                    ->default(true),

                                Forms\Components\Textarea::make('winner_notification_template')
                                    ->label('قالب إشعار الفائز')
                                    ->rows(4)
                                    ->helperText('استخدم: {name}, {prize}, {branch}')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('التكامل')
                            ->icon('heroicon-o-puzzle-piece')
                            ->schema([
                                Forms\Components\TextInput::make('outscraper_api_key')
                                    ->label('Outscraper API Key')
                                    ->password()
                                    ->revealable(),

                                Forms\Components\Select::make('ai_provider')
                                    ->label('مزود الذكاء الاصطناعي')
                                    ->options([
                                        'openai' => 'OpenAI',
                                        'anthropic' => 'Anthropic',
                                    ])
                                    ->default('anthropic'),

                                Forms\Components\TextInput::make('ai_model')
                                    ->label('نموذج AI')
                                    ->default('claude-sonnet-4-20250514'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            $setting = CompetitionSetting::where('key', $key)->first();
            if ($setting) {
                $setting->update([
                    'value' => is_array($value) ? json_encode($value) : (is_bool($value) ? ($value ? '1' : '0') : (string) $value),
                    'updated_at' => now(),
                ]);
                Cache::forget('competition_setting:' . $key);
            }
        }

        Notification::make()
            ->success()
            ->title('تم حفظ الإعدادات')
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearCache')
                ->label('مسح الكاش')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    $keys = CompetitionSetting::pluck('key');
                    foreach ($keys as $key) {
                        Cache::forget('competition_setting:' . $key);
                    }
                    Notification::make()
                        ->success()
                        ->title('تم مسح الكاش')
                        ->send();
                }),
        ];
    }
}
