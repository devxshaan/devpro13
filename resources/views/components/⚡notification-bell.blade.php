<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public int $unreadCount = 0;
    public bool $open = false;
    public ?string $selectedId = null;

    public function mount(): void
    {
        $this->unreadCount = auth()->user()->unreadNotifications()->count();
    }

    #[On('echo-private:notifications.{authId},notification.sent')]
    public function onNewNotification(): void
    {
        $previous = $this->unreadCount;
        $this->unreadCount = auth()->user()->unreadNotifications()->count();

        if ($this->unreadCount > $previous) {
            $this->dispatch('playSound');
        }
    }

    public function toggle(): void
    {
        $this->open = !$this->open;
        if ($this->open) {
            $this->unreadCount = auth()->user()->unreadNotifications()->count();
        }
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->unreadCount = 0;
    }

    public function viewNotification(string $id): void
    {
        auth()->user()->notifications()
            ->where('id', $id)
            ->update(['read_at' => now()]);

        $this->unreadCount = auth()->user()->unreadNotifications()->count();
        $this->selectedId = $id;
        $this->open = false;
    }

    public function closeModal(): void
    {
        $this->selectedId = null;
    }

    #[Computed]
    public function selected()
    {
        if (!$this->selectedId) return null;
        return auth()->user()->notifications()->find($this->selectedId);
    }

    #[Computed]
    public function notifications()
    {
        return auth()->user()->notifications()->latest()->take(10)->get();
    }

    public function getAuthIdProperty(): int
    {
        return auth()->id();
    }
}; ?>

<div style="position:relative;" x-data="{ open: @entangle('open') }">

    {{-- Bell Button --}}
    <button wire:click="toggle"
        style="position:relative; padding:6px; background:none; border:none; cursor:pointer;">
        <x-filament::icon
            icon="heroicon-o-bell"
            style="width:22px; height:22px; color: rgb(156 163 175);" />

        @if($unreadCount > 0)
            <span style="
                position:absolute; top:-4px; right:-4px;
                background:#ef4444; color:white;
                border-radius:9999px; width:18px; height:18px;
                font-size:10px; font-weight:700;
                display:flex; align-items:center; justify-content:center;
            ">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open" x-cloak @click.outside="open = false"
        style="
            position:absolute; right:0; top:40px; width:320px;
            background:#111827; border:1px solid rgba(255,255,255,0.1);
            border-radius:12px; box-shadow:0 25px 50px rgba(0,0,0,0.5);
            z-index:9999; overflow:hidden;
        ">

        {{-- Header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid rgba(255,255,255,0.1);">
            <span style="font-weight:600; font-size:14px; color:white;">Notifications</span>
            @if($unreadCount > 0)
                <button wire:click="markAllRead"
                    style="font-size:12px; color:#f59e0b; background:none; border:none; cursor:pointer;">
                    Mark all read
                </button>
            @endif
        </div>

        {{-- List --}}
        <div style="max-height:320px; overflow-y:auto;">
            @forelse($this->notifications as $notification)
                @php
                    $colors = [
                        'success' => '#22c55e',
                        'danger'  => '#ef4444',
                        'warning' => '#f59e0b',
                        'info'    => '#3b82f6',
                    ];
                    $color   = $colors[$notification->data['type']] ?? '#3b82f6';
                    $isUnread = is_null($notification->read_at);
                @endphp

                <div wire:key="{{ $notification->id }}"
                    wire:click="viewNotification('{{ $notification->id }}')"
                    style="
                        padding:12px 16px; display:flex; gap:12px;
                        align-items:flex-start; cursor:pointer;
                        border-bottom:1px solid rgba(255,255,255,0.05);
                        background:{{ $isUnread ? 'rgba(255,255,255,0.05)' : 'transparent' }};
                    ">

                    <div style="
                        width:8px; height:8px; border-radius:9999px;
                        background:{{ $color }}; flex-shrink:0; margin-top:5px;
                    "></div>

                    <div style="flex:1; min-width:0;">
                        <p style="
                            font-size:13px; margin:0 0 4px 0;
                            color:{{ $isUnread ? 'white' : '#9ca3af' }};
                            font-weight:{{ $isUnread ? '500' : '400' }};
                        ">{{ $notification->data['message'] }}</p>
                        <p style="font-size:11px; color:#6b7280; margin:0;">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @empty
                <div style="padding:32px 16px; text-align:center; font-size:13px; color:#6b7280;">
                    No notifications
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <div style="padding:8px 16px; border-top:1px solid rgba(255,255,255,0.1); display:flex; justify-content:space-between;">
            <a href="#" style="font-size:12px; color:#6b7280; text-decoration:none;">View all</a>
            @if($this->notifications->count() > 0)
                <button wire:click="markAllRead"
                    style="font-size:12px; color:#f59e0b; background:none; border:none; cursor:pointer;">
                    Clear all
                </button>
            @endif
        </div>
    </div>

    {{-- Notification Detail Modal --}}
    @if($this->selected)
        @php
            $n = $this->selected;
            $typeColors = [
                'success' => ['dot'=>'#22c55e','text'=>'#22c55e','bg'=>'rgba(34,197,94,0.1)'],
                'danger'  => ['dot'=>'#ef4444','text'=>'#ef4444','bg'=>'rgba(239,68,68,0.1)'],
                'warning' => ['dot'=>'#f59e0b','text'=>'#f59e0b','bg'=>'rgba(245,158,11,0.1)'],
                'info'    => ['dot'=>'#3b82f6','text'=>'#3b82f6','bg'=>'rgba(59,130,246,0.1)'],
            ];
            $tc = $typeColors[$n->data['type']] ?? $typeColors['info'];
        @endphp

        <div wire:click="closeModal"
            style="position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:99998;"></div>

        <div style="
            position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
            width:500px; max-width:90vw; background:#111827;
            border:1px solid rgba(255,255,255,0.1);
            border-radius:16px; z-index:99999; padding:24px;
        ">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:10px; height:10px; border-radius:50%; background:{{ $tc['dot'] }};"></div>
                    <span style="
                        font-size:12px; font-weight:600; padding:3px 10px;
                        border-radius:999px; background:{{ $tc['bg'] }};
                        color:{{ $tc['text'] }}; text-transform:uppercase; letter-spacing:1px;
                    ">{{ $n->data['type'] }}</span>
                </div>
                <button wire:click="closeModal"
                    style="background:none; border:none; cursor:pointer; color:#6b7280; font-size:20px;">✕</button>
            </div>

            <p style="font-size:16px; color:white; line-height:1.6; margin:0 0 20px 0;">
                {{ $n->data['message'] }}
            </p>

            <div style="border-top:1px solid rgba(255,255,255,0.08); padding-top:16px; display:flex; justify-content:space-between;">
                <span style="font-size:12px; color:#6b7280;">{{ $n->created_at->format('d M Y, h:i A') }}</span>
                <span style="font-size:12px; color:#6b7280;">{{ $n->created_at->diffForHumans() }}</span>
            </div>

            @if(!empty($n->data['url']))
                <a href="{{ $n->data['url'] }}"
                    style="display:inline-flex; margin-top:16px; background:#4f46e5; color:white; padding:8px 20px; border-radius:8px; font-size:14px; text-decoration:none;">
                    View Details →
                </a>
            @endif
        </div>
    @endif

    {{-- Sound + Echo --}}
    @script
<script>
    let audioCtx = null;
    let audioBuffer = null;
    let isReady = false;

    // ── Simple Audio Init — User Gesture Par ──────────────────
    async function initAudio() {
        if (isReady) return;
        try {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const res = await fetch("{{ asset('sounds/light-hearted-message-tone.mp3') }}");
            const arrayBuffer = await res.arrayBuffer();
            audioBuffer = await audioCtx.decodeAudioData(arrayBuffer);
            isReady = true;
            console.log('Audio ready ✅');
        } catch(e) {
            console.log('Audio init error:', e);
        }
    }

    function playSound() {
        if (!isReady || !audioBuffer || !audioCtx) return;
        try {
            const source = audioCtx.createBufferSource();
            source.buffer = audioBuffer;
            source.connect(audioCtx.destination);
            source.start(0);
            console.log('Sound played ✅');
        } catch(e) {}
    }

    // ── Pehli Click Par Audio Init ────────────────────────────
    document.addEventListener('click', initAudio, { once: false });
    document.addEventListener('keydown', initAudio, { once: false });

    // ── Reverb ────────────────────────────────────────────────
    window.Echo.private(`notifications.{{ auth()->id() }}`)
        .listen('.notification.sent', () => {
            console.log('Notification received! 🔔');
            $wire.onNewNotification();
            playSound();
        });
</script>
@endscript
</div>