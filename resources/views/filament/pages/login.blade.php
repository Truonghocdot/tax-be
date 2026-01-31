<x-filament-panels::page.simple>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ \Filament\Support\Facades\FilamentView::renderHook(
          \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
          scopes: $this->getRenderHookScopes(),
      ) }}

    <form wire:submit.prevent="authenticate" class="space-y-6">
        {{ $this->form }}
        <x-filament::actions :actions="$this->getFormActions()" alignment="right" class="flex justify-center" />
    </form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(
        \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
        scopes: $this->getRenderHookScopes(),
    ) }}
</x-filament-panels::page.simple>