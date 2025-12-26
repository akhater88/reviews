<?php

namespace App\Livewire;

use App\Services\PhoneOtpService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class PhoneVerificationModal extends Component
{
    public bool $showModal = false;
    public string $phone = '';
    public string $countryCode = '+966';
    public string $otp = '';
    public string $step = 'phone'; // 'phone' | 'otp'
    public int $countdown = 0;
    public bool $canResend = true;
    public int $attempts = 0;
    public int $maxAttempts = 3;
    public ?string $error = null;
    public ?string $success = null;

    protected PhoneOtpService $phoneOtpService;

    public function boot(PhoneOtpService $phoneOtpService): void
    {
        $this->phoneOtpService = $phoneOtpService;
    }

    public function mount(): void
    {
        $user = Auth::user();

        // Pre-fill phone if user has one
        if ($user->phone) {
            $this->parseExistingPhone($user->phone);
        }

        // Auto-show modal if user is not verified
        if (!$user->isPhoneVerified()) {
            $this->showModal = true;
        }
    }

    /**
     * Parse existing phone number into country code and number.
     */
    protected function parseExistingPhone(string $phone): void
    {
        // Remove any spaces
        $phone = preg_replace('/\s+/', '', $phone);

        // Common country codes
        $countryCodes = ['+966', '+971', '+965', '+974', '+973', '+968', '+962', '+20', '+1'];

        foreach ($countryCodes as $code) {
            if (str_starts_with($phone, $code)) {
                $this->countryCode = $code;
                $this->phone = substr($phone, strlen($code));
                return;
            }
        }

        // If no country code found, assume the whole thing is the number
        $this->phone = ltrim($phone, '+');
    }

    /**
     * Get full phone number with country code.
     */
    public function getFullPhone(): string
    {
        return $this->countryCode . preg_replace('/\s+/', '', $this->phone);
    }

    /**
     * Send OTP to the phone number.
     */
    public function sendOtp(): void
    {
        $this->error = null;
        $this->success = null;

        // Validate phone number
        $this->validate([
            'phone' => ['required', 'regex:/^[0-9]{7,12}$/'],
        ], [
            'phone.required' => 'رقم الجوال مطلوب',
            'phone.regex' => 'رقم الجوال غير صالح',
        ]);

        $fullPhone = $this->getFullPhone();

        // Check rate limit
        $rateLimit = $this->phoneOtpService->getRateLimitInfo($fullPhone);
        if ($rateLimit['is_rate_limited']) {
            $this->error = "انتظر {$rateLimit['remaining_time']} ثانية قبل إعادة الإرسال";
            $this->countdown = $rateLimit['remaining_time'];
            $this->canResend = false;
            $this->dispatch('start-countdown', seconds: $this->countdown);
            return;
        }

        // Generate and send OTP
        $result = $this->phoneOtpService->generateOtp($fullPhone);

        if (!$result['success']) {
            $this->error = $result['message'];
            return;
        }

        // Update user's phone number
        Auth::user()->update(['phone' => $fullPhone]);

        $this->success = $result['message'];
        $this->step = 'otp';
        $this->otp = '';
        $this->attempts = 0;
        $this->countdown = PhoneOtpService::COOLDOWN_SECONDS;
        $this->canResend = false;

        // Start countdown timer
        $this->dispatch('start-countdown', seconds: $this->countdown);

        Notification::make()
            ->title('تم إرسال رمز التحقق')
            ->body('تم إرسال رمز التحقق إلى رقم الجوال عبر واتساب')
            ->success()
            ->send();
    }

    /**
     * Verify the OTP.
     */
    public function verifyOtp(): void
    {
        $this->error = null;
        $this->success = null;

        // Validate OTP
        $this->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'رمز التحقق مطلوب',
            'otp.digits' => 'رمز التحقق يجب أن يتكون من 6 أرقام',
        ]);

        $fullPhone = $this->getFullPhone();

        // Verify OTP
        $result = $this->phoneOtpService->verifyOtp($fullPhone, $this->otp);

        if (!$result['success']) {
            $this->error = $result['message'];
            $this->attempts++;

            if ($result['verification']) {
                $this->attempts = $result['verification']->attempts;
            }

            // Check if max attempts reached
            if ($this->attempts >= $this->maxAttempts) {
                $this->step = 'phone';
                $this->otp = '';
                $this->error = 'تم تجاوز الحد الأقصى للمحاولات. اطلب رمزًا جديدًا.';
            }

            return;
        }

        // OTP verified successfully
        Auth::user()->update([
            'phone' => $fullPhone,
            'phone_verified_at' => now(),
        ]);

        $this->success = 'تم التحقق بنجاح';
        $this->showModal = false;

        Notification::make()
            ->title('تم التحقق بنجاح')
            ->body('تم التحقق من رقم الجوال بنجاح')
            ->success()
            ->send();

        // Redirect to dashboard
        $this->redirect(route('filament.admin.pages.dashboard'));
    }

    /**
     * Resend OTP.
     */
    public function resendOtp(): void
    {
        if (!$this->canResend) {
            return;
        }

        $this->step = 'phone';
        $this->otp = '';
        $this->sendOtp();
    }

    /**
     * Handle countdown finished event.
     */
    #[On('countdown-finished')]
    public function onCountdownFinished(): void
    {
        $this->countdown = 0;
        $this->canResend = true;
    }

    /**
     * Update countdown value from JavaScript.
     */
    #[On('update-countdown')]
    public function updateCountdown(int $value): void
    {
        $this->countdown = $value;
        if ($value <= 0) {
            $this->canResend = true;
        }
    }

    /**
     * Go back to phone step.
     */
    public function goBack(): void
    {
        $this->step = 'phone';
        $this->otp = '';
        $this->error = null;
        $this->success = null;
    }

    /**
     * Open the modal.
     */
    #[On('open-phone-verification-modal')]
    public function openModal(): void
    {
        $this->showModal = true;
    }

    /**
     * Close the modal.
     */
    public function closeModal(): void
    {
        // Only allow closing if verified
        if (Auth::user()->isPhoneVerified()) {
            $this->showModal = false;
        }
    }

    /**
     * Get available country codes.
     */
    public function getCountryCodes(): array
    {
        return [
            '+966' => 'السعودية (+966)',
            '+971' => 'الإمارات (+971)',
            '+965' => 'الكويت (+965)',
            '+974' => 'قطر (+974)',
            '+973' => 'البحرين (+973)',
            '+968' => 'عمان (+968)',
            '+962' => 'الأردن (+962)',
            '+20' => 'مصر (+20)',
            '+1' => 'أمريكا/كندا (+1)',
        ];
    }

    public function render()
    {
        return view('livewire.phone-verification-modal', [
            'countryCodes' => $this->getCountryCodes(),
        ]);
    }
}
