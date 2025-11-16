<?php

namespace App\Services;

use App\Events\TransactionCreated;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TransferService
{
    const COMMISSION_RATE = 0.015; // 1.5%

    /**
     * Execute a money transfer between users with concurrency control
     *
     * @param User $sender
     * @param int $receiverId
     * @param float $amount
     * @return array
     * @throws Exception
     */
    public function transfer(User $sender, int $receiverId, float $amount): array
    {
        // Validate sender can't send to themselves
        if ($sender->id === $receiverId) {
            throw new Exception('Cannot transfer to yourself');
        }

        // Calculate commission
        $commissionFee = round($amount * self::COMMISSION_RATE, 2);
        $totalDeduction = $amount + $commissionFee;

        // Start database transaction
        return DB::transaction(function () use ($sender, $receiverId, $amount, $commissionFee, $totalDeduction) {
            // Lock users in consistent order to prevent deadlocks
            // Always lock lower ID first
            $ids = [$sender->id, $receiverId];
            sort($ids);

            $lockedUsers = User::whereIn('id', $ids)
                ->lockForUpdate()
                ->orderBy('id')
                ->get()
                ->keyBy('id');

            // Get locked sender and receiver
            $lockedSender = $lockedUsers[$sender->id];
            $receiver = $lockedUsers[$receiverId] ?? null;

            // Validate receiver exists
            if (!$receiver) {
                throw new Exception('Receiver not found');
            }

            // Validate sender has sufficient balance
            if ($lockedSender->balance < $totalDeduction) {
                throw new Exception('Insufficient balance');
            }

            // Get admin user for commission (lock it too)
            $admin = User::where('is_admin', true)
                ->lockForUpdate()
                ->first();

            if (!$admin) {
                throw new Exception('System admin not found');
            }

            // Perform the transfer
            $lockedSender->balance -= $totalDeduction;
            $lockedSender->save();

            $receiver->balance += $amount;
            $receiver->save();

            $admin->balance += $commissionFee;
            $admin->save();

            // Create transaction records
            $transaction = Transaction::create([
                'sender_id' => $lockedSender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'commission_fee' => $commissionFee,
                'type' => 'transfer',
                'status' => 'completed',
                'description' => "Transfer from {$lockedSender->name} to {$receiver->name}",
            ]);

            // Create commission transaction record
            Transaction::create([
                'sender_id' => $lockedSender->id,
                'receiver_id' => $admin->id,
                'amount' => $commissionFee,
                'commission_fee' => 0,
                'type' => 'commission',
                'status' => 'completed',
                'description' => "Commission fee for transaction {$transaction->tuuid}",
            ]);

            Log::info('Transfer completed', [
                'transaction_id' => $transaction->id,
                'tuuid' => $transaction->tuuid,
                'sender_id' => $lockedSender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'commission' => $commissionFee,
            ]);

            // Broadcast transaction event
            broadcast(new TransactionCreated($transaction))->toOthers();

            return [
                'success' => true,
                'transaction' => $transaction->load(['sender', 'receiver']),
                'new_balance' => $lockedSender->balance,
            ];
        });
    }

    /**
     * Get transaction history for a user
     *
     * @param User $user
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getTransactionHistory(User $user, int $perPage = 20)
    {
        return Transaction::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
        })
        ->with(['sender', 'receiver'])
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
    }
}
