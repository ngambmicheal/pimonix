<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isSender = $user && $this->sender_id === $user->id;

        return [
            'tuuid' => $this->tuuid,
            'sender' => $this->when($this->sender, [
                'uid' => $this->sender?->uid,
                'name' => $this->sender?->name,
                'email' => $this->sender?->email
            ]),
            'receiver' => $this->when($this->receiver, [
                'uid' => $this->receiver?->uid,
                'name' => $this->receiver?->name,
                'email' => $this->receiver?->email
            ]),
            'amount' => number_format($this->amount, 2, '.', ''),
            'commission_fee' => $this->when($isSender, number_format($this->commission_fee, 2, '.', '')),
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
