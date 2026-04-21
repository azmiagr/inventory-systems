<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockTransaction\RejectStockTransactionRequest;
use App\Http\Requests\StockTransaction\StoreStockTransactionRequest;
use App\Http\Resources\StockTransactionResource;
use App\Http\Responses\ApiResponse;
use App\Models\Item;
use App\Models\StockAlert;
use App\Models\StockTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = StockTransaction::query()
            ->with(['item', 'creator', 'approver'])
            ->filterType($request->query('type'))
            ->filterStatus($request->query('status'))
            ->filterItem($request->query('item_id'))
            ->filterDateFrom($request->query('date_from'))
            ->filterDateTo($request->query('date_to'));

        if ($user->isStaff()) {
            $query->forUser($user->id);
        }

        $perPage = min((int) $request->query('per_page', 10), 100);
        $transactions = $query->latest('transaction_date')->paginate($perPage);

        return ApiResponse::success(
            StockTransactionResource::collection($transactions)->response()->getData(true),
            'Transactions retrieved successfully.'
        );
    }

    public function show(StockTransaction $transaction): JsonResponse
    {
        $transaction->load(['item', 'creator', 'approver']);

        return ApiResponse::success(
            new StockTransactionResource($transaction),
            'Transaction retrieved successfully.',
        );
    }

    public function store(StoreStockTransactionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $item = Item::findOrFail($data['item_id']);

        $stockBefore = $item->stock_current;
        $stockAfter = $data['type'] === 'in'
            ? $stockBefore + $data['quantity']
            : $stockBefore - $data['quantity'];

        $transaction = StockTransaction::create([
            ...$data,
            'created_by' => $request->user()->id,
            'status' => 'pending',
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
        ]);

        $transaction->load(['item', 'creator']);

        return ApiResponse::created(
            new StockTransactionResource($transaction),
            'Transaction created successfully.',
        );
    }

    public function approve(Request $request, StockTransaction $transaction): JsonResponse
    {
        if (!$transaction->isPending()) {
            return ApiResponse::badRequest('Only pending transactions can be approved.');
        }

        $approver = $request->user();
        $item = $transaction->item;

        if ($transaction->isOut() && $item->stock_current < $transaction->quantity) {
            $transaction->update([
                'status' => 'rejected',
                'notes' => 'Stok tidak mencukupi. Stok saat ini: ' . $item->stock_current,
                'approved_by' => $approver->id,
            ]);

            return ApiResponse::badRequest(
                'Stok tidak mencukupi. Transaksi otomatis ditolak.',
                new StockTransactionResource($transaction->load(['item', 'creator', 'approver']))
            );
        }

        $stockBefore = $item->stock_current;
        $stockAfter = $transaction->isIn()
            ? $stockBefore + $transaction->quantity
            : $stockBefore - $transaction->quantity;

        $item->update(['stock_current' => $stockAfter]);

        $transaction->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
        ]);

        if ($item->stock_current <= $item->stock_minimum) {
            StockAlert::create([
                'item_id' => $item->id,
                'status' => 'unread',
                'stock_at_alert' => $item->stock_current,
            ]);
        }

        $transaction->load(['item', 'creator', 'approver']);

        return ApiResponse::success(
            new StockTransactionResource($transaction),
            'Transaction approved successfully.'
        );
    }

    public function reject(RejectStockTransactionRequest $request, StockTransaction $transaction): JsonResponse
    {
        if (!$transaction->isPending()) {
            return ApiResponse::badRequest('Only pending transactions can be rejected.');
        }

        $transaction->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'notes' => $request->input('notes') ?? $transaction->notes,
        ]);

        $transaction->load(['item', 'creator', 'approver']);

        return ApiResponse::success(
            new StockTransactionResource($transaction),
            'Transaction rejected successfully.'
        );
    }

    public function destroy(StockTransaction $transaction): JsonResponse
    {
        $user = request()->user();

        if (!$transaction->isPending()) {
            return ApiResponse::badRequest('Only pending transactions can be deleted.');
        }

        if ($user->isStaff() && $transaction->created_by !== $user->id) {
            return ApiResponse::forbidden('You can only delete your own transactions.');
        }

        $transaction->delete();

        return ApiResponse::success(null, 'Transaction deleted successfully.');
    }
}
