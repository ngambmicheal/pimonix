<?php

namespace App\Events;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Transaction $transaction
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // Broadcast to sender's private channel
        if ($this->transaction->sender_id) {
            $channels[] = new PrivateChannel('user.' . $this->transaction->sender_id);
        }

        // Broadcast to receiver's private channel
        if ($this->transaction->receiver_id) {
            $channels[] = new PrivateChannel('user.' . $this->transaction->receiver_id);
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'transaction' => new TransactionResource($this->transaction->load(['sender', 'receiver'])),
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
