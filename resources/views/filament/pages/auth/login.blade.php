<x-filament-panels::page.simple>
    {{-- Tenant Logo --}}
    @if($this->getTenantLogoUrl())
        <div class="flex justify-center mb-6">
            <img
                src="{{ $this->getTenantLogoUrl() }}"
                alt="{{ $this->getTenantName() }}"
                class="h-20 w-20 object-contain rounded-lg"
            >
        </div>
        @if($this->getTenantName())
            <h2 class="text-center text-xl font-semibold text-gray-900 dark:text-white mb-6">
                {{ $this->getTenantName() }}
            </h2>
        @endif
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
