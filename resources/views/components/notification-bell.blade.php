@auth
    @php
        $notificationUser = auth()->user();
        $notificationItems = $notificationUser->notifications()->latest()->limit(5)->get();
        $unreadNotificationCount = $notificationUser->unreadNotifications()->count();
    @endphp

    <details class="relative">
        <summary class="relative flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-lg text-white transition hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-white" aria-label="Buka notifikasi" title="Notifikasi">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-5-5.9V4a1 1 0 0 0-2 0v1.1A6 6 0 0 0 6 11v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9" />
            </svg>
            @if($unreadNotificationCount > 0)
                <span class="absolute right-0 top-0 flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-400 px-1 text-xs font-bold leading-none text-teal-950">
                    {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                </span>
            @endif
        </summary>

        <div class="absolute right-0 top-full z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-gray-200 bg-white text-teal-900 shadow-xl">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3">
                <div>
                    <p class="font-bold">Notifikasi</p>
                    <p class="text-xs text-gray-500">{{ $unreadNotificationCount }} belum dibaca</p>
                </div>
                @if($unreadNotificationCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-teal-700 hover:text-teal-900">Tandai semua dibaca</button>
                    </form>
                @endif
            </div>

            @forelse($notificationItems as $notification)
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                    @csrf
                    <button type="submit" class="block w-full border-b border-gray-100 px-4 py-3 text-left transition hover:bg-cream-50 {{ $notification->read_at ? 'bg-white' : 'bg-teal-50' }}">
                        <span class="flex items-start justify-between gap-2">
                            <span class="text-sm font-semibold text-teal-900">{{ $notification->data['title'] ?? 'Notifikasi RuangRileks' }}</span>
                            @if(! $notification->read_at)
                                <span class="shrink-0 rounded-full bg-teal-100 px-2 py-0.5 text-xs font-semibold text-teal-800">Baru</span>
                            @endif
                        </span>
                        <span class="mt-1 block text-sm leading-5 text-gray-600">{{ $notification->data['message'] ?? '' }}</span>
                        <span class="mt-2 block text-xs text-gray-400">{{ $notification->created_at->copy()->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span>
                    </button>
                </form>
            @empty
                <div class="px-4 py-6 text-center">
                    <p class="text-sm font-semibold text-gray-700">Belum ada notifikasi</p>
                    <p class="mt-1 text-xs text-gray-500">Informasi terbaru akan muncul di sini.</p>
                </div>
            @endforelse
        </div>
    </details>
@endauth
