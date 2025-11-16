<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransferService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class TransactionController extends Controller
{
    public function __construct(
        protected TransferService $transferService
    ) {}

    /**
     * Get transaction history and balance for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $transactions = $this->transferService->getTransactionHistory($user);

        return response()->json([
            'balance' => number_format($user->balance, 2, '.', ''),
            'user' => [
                'uid' => $user->uid,
                'name' => $user->name,
            ],
            'transactions' => TransactionResource::collection($transactions->items()),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'last_page' => $transactions->lastPage(),
            ],
        ]);
    }

    /**
     * Execute a new money transfer
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        try {
            $result = $this->transferService->transfer(
                $request->user(),
                $request->validated('receiver_id'),
                $request->validated('amount')
            );

            return response()->json([
                'message' => 'Transfer completed successfully',
                'transaction' => new TransactionResource($result['transaction']),
                'new_balance' => number_format($result['new_balance'], 2, '.', ''),
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Transfer failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
