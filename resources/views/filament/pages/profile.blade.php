<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Phone Verification Alert --}}
        @if(!$this->getPhoneVerified())
            <div class="p-4 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-xl">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-warning-600 dark:text-warning-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-warning-800 dark:text-warning-200">
                            التحقق من رقم الجوال مطلوب
                        </h3>
                        <p class="mt-1 text-sm text-warning-700 dark:text-warning-300">
                            يرجى التحقق من رقم جوالك للوصول إلى جميع ميزات النظام. سيتم إرسال رمز التحقق عبر واتساب.
                        </p>
                        <button
                            wire:click="openPhoneVerificationModal"
                            class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-warning-600 hover:bg-warning-700 text-white font-medium rounded-lg transition-colors duration-200"
                        >
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                            التحقق الآن
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Phone Verification Status Card --}}
        <x-filament::section>
            <x-slot name="heading">
                التحقق من رقم الجوال
            </x-slot>
            <x-slot name="description">
                حالة التحقق من رقم الجوال الخاص بك
            </x-slot>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 p-3 rounded-full {{ $this->getPhoneVerified() ? 'bg-success-100 dark:bg-success-900/30' : 'bg-gray-100 dark:bg-gray-800' }}">
                        <svg class="w-6 h-6 {{ $this->getPhoneVerified() ? 'text-success-600 dark:text-success-400' : 'text-gray-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">رقم الجوال</p>
                        <p class="font-medium text-gray-900 dark:text-white" dir="ltr">
                            {{ $this->getPhone() ?? 'لم يتم إضافة رقم جوال' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if($this->getPhoneVerified())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-success-700 dark:text-success-400 bg-success-100 dark:bg-success-900/30 rounded-full">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                            تم التحقق
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-warning-700 dark:text-warning-400 bg-warning-100 dark:bg-warning-900/30 rounded-full">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                            غير مُحقق
                        </span>
                        <button
                            wire:click="openPhoneVerificationModal"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors duration-200"
                        >
                            تحقق الآن
                        </button>
                    @endif
                </div>
            </div>
        </x-filament::section>

        {{-- Profile Form --}}
        <x-filament::section>
            <x-slot name="heading">
                المعلومات الشخصية
            </x-slot>
            <x-slot name="description">
                تحديث معلومات حسابك
            </x-slot>

            <form wire:submit="updateProfile">
                {{ $this->profileForm }}

                <div class="mt-6 flex justify-end">
                    <x-filament::button type="submit">
                        حفظ التغييرات
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Password Form --}}
        <x-filament::section>
            <x-slot name="heading">
                تغيير كلمة المرور
            </x-slot>
            <x-slot name="description">
                تأكد من استخدام كلمة مرور قوية للحفاظ على أمان حسابك
            </x-slot>

            <form wire:submit="updatePassword">
                {{ $this->passwordForm }}

                <div class="mt-6 flex justify-end">
                    <x-filament::button type="submit">
                        تحديث كلمة المرور
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    </div>

    {{-- Phone Verification Modal --}}
    @livewire('phone-verification-modal')
</x-filament-panels::page>
