<div class="bg-gray-900 rounded-xl border border-gray-700 h-full" wire:poll.3s>
    <div class="px-5 py-4 border-b border-gray-700">
        <h2 class="font-semibold text-white">Activity Log</h2>
    </div>
    <div class="divide-y divide-gray-700 h-64 overflow-y-auto">
        @forelse ($logs as $log)
        <div class="px-5 py-2.5 flex gap-3 items-start">
            @php
                $badge = match($log->level) {
                    'trade'   => 'bg-green-950 text-green-400 border border-green-800',
                    'error'   => 'bg-red-950 text-red-400 border border-red-800',
                    'warning' => 'bg-yellow-950 text-yellow-400 border border-yellow-800',
                    default   => 'bg-blue-950 text-blue-400 border border-blue-800',
                };
            @endphp
            <span class="text-xs mt-0.5 shrink-0 px-1.5 py-0.5 rounded font-medium {{ $badge }}">
                {{ strtoupper($log->level) }}
            </span>
            <span class="text-gray-300 text-sm flex-1">{{ $log->message }}</span>
            <span class="text-gray-500 text-xs shrink-0">{{ $log->created_at->format('H:i:s') }}</span>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-gray-400">No activity yet</div>
        @endforelse
    </div>
</div>
