{{-- Sticky CTA on Scroll (Mobile) --}}
<div
    x-data="{ showSticky: false }"
    x-init="window.addEventListener('scroll', () => { showSticky = window.scrollY > 600 })"
    x-show="showSticky"
    x-transition:enter="transition transform ease-out duration-300"
    x-transition:enter-start="translate-y-full"
    x-transition:enter-end="translate-y-0"
    x-transition:leave="transition transform ease-in duration-200"
    x-transition:leave-start="translate-y-0"
    x-transition:leave-end="translate-y-full"
    x-cloak
    class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40 py-3 px-4 sm:hidden"
>
    <a
        href="{{ route('get-started') }}"
        class="block w-full bg-[#df625b] hover:bg-[#c55550] text-white py-3 rounded-full font-semibold text-center"
    >
        {{ __('app.stickyCTAButton') }}
    </a>
</div>
