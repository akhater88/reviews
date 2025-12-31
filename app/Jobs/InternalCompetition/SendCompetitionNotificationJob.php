<?php

namespace App\Jobs\InternalCompetition;

use App\Enums\InternalCompetition\NotificationChannel;
use App\Enums\InternalCompetition\NotificationEvent;
use App\Enums\InternalCompetition\NotificationStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCompetitionNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 120;

    public function __construct(
        public int $competitionId,
        public string $eventType, // start, reminder, progress, ending_soon, ended, winner
        public ?int $recipientUserId = null,
        public ?array $customData = null
    ) {
        $this->onQueue('internal-competition');
    }

    public function handle(): void
    {
        $competition = InternalCompetition::find($this->competitionId);

        if (!$competition) {
            Log::warning('Competition not found for notification', [
                'competition_id' => $this->competitionId,
            ]);
            return;
        }

        $event = NotificationEvent::from($this->eventType);

        Log::info('Sending competition notification', [
            'competition_id' => $this->competitionId,
            'event_type' => $this->eventType,
            'recipient_user_id' => $this->recipientUserId,
        ]);

        try {
            if ($this->recipientUserId) {
                // Send to specific user
                $this->sendToUser($competition, $event, $this->recipientUserId);
            } else {
                // Send to all relevant recipients
                $this->sendToAllRecipients($competition, $event);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send competition notification', [
                'competition_id' => $this->competitionId,
                'event_type' => $this->eventType,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function sendToAllRecipients(
        InternalCompetition $competition,
        NotificationEvent $event
    ): void {
        // Get notification settings
        $settings = $competition->notification_settings ?? [];

        // Get all relevant users (tenant admins and branch managers)
        $users = $this->getRecipientsForCompetition($competition);

        foreach ($users as $user) {
            $this->sendToUser($competition, $event, $user->id);
        }

        Log::info('Notifications sent to all recipients', [
            'competition_id' => $competition->id,
            'event_type' => $event->value,
            'recipients_count' => $users->count(),
        ]);
    }

    protected function sendToUser(
        InternalCompetition $competition,
        NotificationEvent $event,
        int $userId
    ): void {
        $user = User::find($userId);
        if (!$user) {
            return;
        }

        $settings = $competition->notification_settings ?? [];

        // Determine which channels to use
        $channels = $this->getEnabledChannels($settings, $event);

        foreach ($channels as $channel) {
            $this->createAndSendNotification($competition, $event, $user, $channel);
        }
    }

    protected function getEnabledChannels(array $settings, NotificationEvent $event): array
    {
        $channels = [];

        // Check WhatsApp
        if (($settings['whatsapp']['enabled'] ?? true) &&
            in_array($event->value, $settings['whatsapp']['events'] ?? ['start', 'ended', 'winner'])) {
            $channels[] = NotificationChannel::WHATSAPP;
        }

        // Check Email
        if (($settings['email']['enabled'] ?? true) &&
            in_array($event->value, $settings['email']['events'] ?? ['start', 'ended', 'winner'])) {
            $channels[] = NotificationChannel::EMAIL;
        }

        return $channels;
    }

    protected function createAndSendNotification(
        InternalCompetition $competition,
        NotificationEvent $event,
        User $user,
        NotificationChannel $channel
    ): void {
        // Generate content
        $content = $this->generateNotificationContent($competition, $event, $user);

        // Create notification record
        $notification = InternalCompetitionNotification::create([
            'competition_id' => $competition->id,
            'recipient_type' => $this->getRecipientType($user),
            'recipient_user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'channel' => $channel,
            'event_type' => $event,
            'subject' => $content['subject'],
            'content' => $content['body'],
            'template_data' => $this->customData,
            'status' => NotificationStatus::PENDING,
        ]);

        // Send based on channel
        try {
            if ($channel === NotificationChannel::WHATSAPP) {
                $this->sendWhatsApp($user, $content);
            } else {
                $this->sendEmail($user, $content);
            }

            $notification->markAsSent();

        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            Log::warning('Failed to send notification', [
                'notification_id' => $notification->id,
                'channel' => $channel->value,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function generateNotificationContent(
        InternalCompetition $competition,
        NotificationEvent $event,
        User $user
    ): array {
        $competitionName = $competition->display_name;

        return match ($event) {
            NotificationEvent::START => [
                'subject' => "🏆 بدء المسابقة: {$competitionName}",
                'body' => "مرحباً {$user->name}،\n\nتم بدء المسابقة \"{$competitionName}\"!\n\nالمدة: من {$competition->start_date->format('Y-m-d')} إلى {$competition->end_date->format('Y-m-d')}\n\nنتمنى لكم التوفيق!",
            ],
            NotificationEvent::REMINDER => [
                'subject' => "⏰ تذكير: المسابقة {$competitionName}",
                'body' => "مرحباً {$user->name}،\n\nهذا تذكير بأن المسابقة \"{$competitionName}\" مستمرة.\n\nالأيام المتبقية: {$competition->remaining_days}\n\nاستمر في تحسين أدائك!",
            ],
            NotificationEvent::PROGRESS => [
                'subject' => "📊 تحديث التقدم: {$competitionName}",
                'body' => "مرحباً {$user->name}،\n\nتم تحديث نتائج المسابقة \"{$competitionName}\".\n\nقم بزيارة التطبيق لمعرفة ترتيبك الحالي.",
            ],
            NotificationEvent::ENDING_SOON => [
                'subject' => "⚠️ قرب انتهاء المسابقة: {$competitionName}",
                'body' => "مرحباً {$user->name}،\n\nالمسابقة \"{$competitionName}\" ستنتهي خلال {$competition->remaining_days} يوم!\n\nاستغل الوقت المتبقي لتحسين ترتيبك.",
            ],
            NotificationEvent::ENDED => [
                'subject' => "🏁 انتهت المسابقة: {$competitionName}",
                'body' => "مرحباً {$user->name}،\n\nانتهت المسابقة \"{$competitionName}\"!\n\nتم تحديد الفائزين. قم بزيارة التطبيق لمعرفة النتائج.",
            ],
            NotificationEvent::WINNER => [
                'subject' => "🎉 تهانينا! فزت في المسابقة: {$competitionName}",
                'body' => "مرحباً {$user->name}،\n\n🎊 تهانينا! لقد فزت في المسابقة \"{$competitionName}\"!\n\nقم بزيارة التطبيق لمعرفة تفاصيل جائزتك.",
            ],
        };
    }

    protected function sendWhatsApp(User $user, array $content): void
    {
        // Integration with Infobip WhatsApp API
        // This is a placeholder - implement based on your Infobip setup

        if (!$user->phone) {
            throw new \Exception('User has no phone number');
        }

        // Example Infobip integration:
        // $infobip = app(InfobipService::class);
        // $infobip->sendWhatsApp($user->phone, $content['body']);

        Log::info('WhatsApp notification would be sent', [
            'phone' => $user->phone,
            'content' => $content['body'],
        ]);
    }

    protected function sendEmail(User $user, array $content): void
    {
        // Send email using Laravel Mail
        if (!$user->email) {
            throw new \Exception('User has no email');
        }

        // \Mail::to($user->email)->send(new CompetitionNotificationMail($content));

        Log::info('Email notification would be sent', [
            'email' => $user->email,
            'subject' => $content['subject'],
        ]);
    }

    protected function getRecipientsForCompetition(InternalCompetition $competition): \Illuminate\Support\Collection
    {
        $userIds = collect();

        // Get tenant admins
        if ($competition->is_multi_tenant) {
            $tenantIds = $competition->activeTenants()->pluck('tenant_id');
            $tenantAdmins = User::whereIn('tenant_id', $tenantIds)
                ->where('role', 'admin')
                ->pluck('id');
            $userIds = $userIds->merge($tenantAdmins);
        } else {
            $tenantAdmins = User::where('tenant_id', $competition->tenant_id)
                ->where('role', 'admin')
                ->pluck('id');
            $userIds = $userIds->merge($tenantAdmins);
        }

        // Get branch managers for enrolled branches
        $branchIds = $competition->activeBranches()->pluck('branch_id');
        $branchManagers = User::whereIn('branch_id', $branchIds)
            ->where('role', 'manager')
            ->pluck('id');
        $userIds = $userIds->merge($branchManagers);

        return User::whereIn('id', $userIds->unique())->get();
    }

    protected function getRecipientType(User $user): string
    {
        if ($user->is_super_admin ?? false) {
            return 'super_admin';
        }
        if ($user->role === 'admin') {
            return 'tenant_admin';
        }
        return 'branch_manager';
    }

    public function tags(): array
    {
        return [
            'internal-competition',
            'notification',
            "competition:{$this->competitionId}",
            "event:{$this->eventType}",
        ];
    }
}
