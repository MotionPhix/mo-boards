<?php

namespace App\Events;

use App\Models\SystemNotification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class SystemNotificationCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(public SystemNotification $notification)
    {
    }

    public function broadcastOn(): array
    {
        $channels = [];
        if ($this->notification->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->notification->user_id);
        }
        if ($this->notification->company_id) {
            $channels[] = new PrivateChannel('company.' . $this->notification->company_id);
        }
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'SystemNotificationCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'level' => $this->notification->level,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'data' => $this->notification->data,
            'is_read' => $this->notification->is_read,
            'is_dismissed' => $this->notification->is_dismissed,
            'created_at' => $this->notification->created_at?->toISOString(),
        ];
    }
}
