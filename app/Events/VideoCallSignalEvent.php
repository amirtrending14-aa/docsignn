<?php

namespace App\Events;

use App\Models\VideoCallSignal;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class VideoCallSignalEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $signal;
    public $toUserId;

    public function __construct(VideoCallSignal $signal, int $toUserId)
    {
        $this->signal = $signal;
        $this->toUserId = $toUserId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel("video-calls.{$this->toUserId}");
    }

    public function broadcastAs()
    {
        return 'video-call-signal';
    }

    public function broadcastWith()
    {
        return [
            'id'              => $this->signal->id,
            'call_id'         => $this->signal->call_id,
            'conversation_id' => $this->signal->conversation_id,
            'from_user_id'    => $this->signal->from_user_id,
            'to_user_id'      => $this->signal->to_user_id,
            'type'            => $this->signal->signal_type,
            'data'            => $this->signal->data,
        ];
    }
}