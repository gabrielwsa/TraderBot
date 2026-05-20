<x-layouts.app title="Dashboard">
    <div class="space-y-4">

        {{-- Today's stats --}}
        <div wire:poll.10s>
            <livewire:today-stats />
        </div>

        {{-- All-time stats + Bot Control --}}
        <div wire:poll.3s>
            <livewire:bot-status />
        </div>

        {{-- P&L Chart --}}
        <div wire:poll.30s>
            <livewire:pnl-chart />
        </div>

        {{-- Positions + Wallet + Log --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch">
            <div class="col-span-1 lg:col-span-5 flex flex-col" wire:poll.4s>
                <livewire:active-positions class="flex-1" />
            </div>
            <div class="col-span-1 lg:col-span-3 flex flex-col">
                <livewire:wallet-info class="flex-1" />
            </div>
            <div class="col-span-1 lg:col-span-4 flex flex-col" wire:poll.3s>
                <livewire:activity-log class="flex-1" />
            </div>
        </div>

        {{-- Scanner + Fee Tracker --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch">
            <div class="col-span-1 lg:col-span-8 flex flex-col">
                <livewire:market-scanner class="flex-1" />
            </div>
            <div class="col-span-1 lg:col-span-4 flex flex-col">
                <livewire:fee-tracker class="flex-1" />
            </div>
        </div>

        {{-- Signal History --}}
        <div>
            <livewire:signal-history />
        </div>

        {{-- Trade History --}}
        <div wire:poll.10s>
            <livewire:trade-history />
        </div>

    </div>
</x-layouts.app>
