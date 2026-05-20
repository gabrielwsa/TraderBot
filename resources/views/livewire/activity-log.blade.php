<div class="bg-gray-900 rounded-xl border border-gray-700 h-full" wire:poll.3s>
    <div class="px-5 py-4 border-b border-gray-700">
        <h2 class="font-semibold text-white">Activity Log</h2>
    </div>
    <div class="divide-y divide-gray-700 h-64 overflow-y-auto">
        @forelse ($logs as $log)
        <div class="px-5 py-2.5 flex gap-3 items-start">
            <span class="text-xs mt-0.5 shrink-0
                {{ $log->level === 'trade' ? 'text-green-400' : '' }}
                {{ $log->level === 'error' ? 'text-red-400' : '' }}
                {{ $log->level === 'warning' ? 'text-yellow-400' : '' }}
                {{ $log->level === 'info' ? 'text-gray-400' : '' }}
            ">{{ strtoupper($log->level) }}</span>
            <span class="text-gray-300 text-sm flex-1">{{ $log->message }}</span>
            <span class="text-gray-400 text-xs shrink-0">{{ $log->created_at->format('H:i:s') }}</span>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-gray-400">No activity yet</div>
        @endforelse
    </div>
</div>
