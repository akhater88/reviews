<?php

namespace App\Services\Competition;

use App\Mail\Competition\WinnerNotificationMail;
use App\Models\Competition\CompetitionParticipant;
use App\Models\Competition\CompetitionWinner;
use App\Services\Infobip\InfobipService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WinnerNotificationService
{
    protected InfobipService $infobipService;

    public function __construct(InfobipService $infobipService)
    {
        $this->infobipService = $infobipService;
    }

    /**
     * Notify winner via all channels
     */
    public function notifyWinner(
        CompetitionParticipant $participant,
        CompetitionWinner $winner,
        string $winnerType
    ): array {
        $results = [
            'whatsapp' => false,
            'email' => false,
            'sms' => false,
        ];

        // Prepare message data
        $messageData = $this->prepareMessageData($participant, $winner, $winnerType);

        // Send WhatsApp notification
        if ($participant->whatsapp_opted_in && $participant->phone) {
            try {
                $results['whatsapp'] = $this->sendWhatsAppNotification($participant, $messageData);
            } catch (\Exception $e) {
                Log::error('WhatsApp winner notification failed', [
                    'participant_id' => $participant->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Send Email notification
        if ($participant->email) {
            try {
                $results['email'] = $this->sendEmailNotification($participant, $messageData);
            } catch (\Exception $e) {
                Log::error('Email winner notification failed', [
                    'participant_id' => $participant->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Update winner notification status
        $winner->markAsNotified('auto', array_keys(array_filter($results)));

        return $results;
    }

    /**
     * Prepare message data based on winner type
     */
    protected function prepareMessageData(
        CompetitionParticipant $participant,
        CompetitionWinner $winner,
        string $winnerType
    ): array {
        $branchName = $winner->competitionBranch?->name ?? 'المطعم';
        $prizeAmount = number_format($winner->prize_amount ?? 0);
        $rank = $winner->prize_rank;

        // Ensure claim code exists
        if (!$winner->claim_code && $winner->prize_amount > 0) {
            $winner->generateClaimCode();
        }

        return [
            'participant_name' => $participant->name ?? 'المشارك',
            'branch_name' => $branchName,
            'prize_amount' => $prizeAmount,
            'rank' => $rank,
            'winner_type' => $winnerType,
            'claim_code' => $winner->claim_code,
            'claim_url' => $winner->claim_code ? route('competition.claim', ['code' => $winner->claim_code]) : null,
        ];
    }

    /**
     * Send WhatsApp notification
     */
    protected function sendWhatsAppNotification(
        CompetitionParticipant $participant,
        array $data
    ): bool {
        $message = $this->buildWhatsAppMessage($data);

        return $this->infobipService->sendWhatsAppMessage($participant->phone, $message);
    }

    /**
     * Build WhatsApp message based on winner type
     */
    protected function buildWhatsAppMessage(array $data): string
    {
        if ($data['winner_type'] === 'lottery') {
            return $this->buildLotteryWinnerMessage($data);
        }

        return $this->buildBranchWinnerMessage($data);
    }

    /**
     * Build message for lottery winners
     */
    protected function buildLotteryWinnerMessage(array $data): string
    {
        $claimSection = '';
        if ($data['claim_url']) {
            $claimSection = <<<SECTION

لاستلام جائزتك، يرجى:
1- الاحتفاظ بهذا الكود
2- زيارة الرابط أدناه
3- إدخال بياناتك البنكية

كود الاستلام: {$data['claim_code']}

{$data['claim_url']}

ملاحظة: يجب استلام الجائزة خلال 30 يوماً
SECTION;
        }

        return <<<MESSAGE
مبروك {$data['participant_name']}!

لقد فزت في سحب مسابقة أفضل مطعم!

الجائزة: {$data['prize_amount']} ريال سعودي

المطعم الذي رشحته: {$data['branch_name']}
{$claimSection}

شكراً لمشاركتك في مسابقة TABsense!
MESSAGE;
    }

    /**
     * Build message for branch nominators
     */
    protected function buildBranchWinnerMessage(array $data): string
    {
        $rankEmoji = match ($data['rank']) {
            1 => '🥇',
            2 => '🥈',
            3 => '🥉',
            default => '🏆',
        };

        $rankText = match ($data['rank']) {
            1 => 'الأول',
            2 => 'الثاني',
            3 => 'الثالث',
            default => $data['rank'],
        };

        return <<<MESSAGE
مبروك {$data['participant_name']}!

المطعم الذي رشحته فاز بالمركز {$rankText}! {$rankEmoji}

{$data['branch_name']}

جائزة المطعم: {$data['prize_amount']} ريال سعودي

أنت جزء من نجاح هذا المطعم!

شكراً لمشاركتك في مسابقة TABsense واختيارك الموفق!

تابعنا للمزيد من المسابقات القادمة!
MESSAGE;
    }

    /**
     * Send Email notification
     */
    protected function sendEmailNotification(
        CompetitionParticipant $participant,
        array $data
    ): bool {
        Mail::to($participant->email)->send(
            new WinnerNotificationMail($participant, $data)
        );

        return true;
    }

    /**
     * Send reminder to unclaimed winners
     */
    public function sendClaimReminder(CompetitionWinner $winner): bool
    {
        if ($winner->prize_claimed || !$winner->participant) {
            return false;
        }

        $participant = $winner->participant;
        $daysLeft = $winner->days_to_claim;

        if ($daysLeft <= 0) {
            return false;
        }

        $message = <<<MESSAGE
تذكير: جائزتك في انتظارك!

مرحباً {$participant->name}،

لم تقم باستلام جائزتك بعد في مسابقة أفضل مطعم.

الجائزة: {$winner->prize_amount} ريال سعودي

المتبقي: {$daysLeft} يوم فقط

كود الاستلام: {$winner->claim_code}

استلم جائزتك الآن:
{$winner->claim_url}

لا تفوّت الفرصة!
MESSAGE;

        $sent = false;
        if ($participant->whatsapp_opted_in && $participant->phone) {
            $sent = $this->infobipService->sendWhatsAppMessage($participant->phone, $message);
        }

        $winner->update(['reminder_sent_at' => now()]);

        return $sent;
    }
}
