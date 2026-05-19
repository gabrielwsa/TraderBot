<x-layouts.app title="Dashboard">
    <div class="space-y-4">

        {{-- Stats + Bot Control --}}
        <div wire:poll.3s>
            <livewire:bot-status />
        </div>

        {{-- Middle: positions (wide) + wallet + log --}}
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-5" wire:poll.4s>
                <livewire:active-positions />
            </div>
            <div class="col-span-3">
                <livewire:wallet-info />
            </div>
            <div class="col-span-4" wire:poll.3s>
                <livewire:activity-log />
            </div>
        </div>

        {{-- Trade history full width --}}
        <div wire:poll.10s>
            <livewire:trade-history />
        </div>

    </div>
</x-layouts.app>
