<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\StoreItemRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use App\Models\Item;
use GuzzleHttp\Psr7\Query;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Item::query()
            ->with(['category', 'supplier'])
            ->search($request->query('search'))
            ->filterCategory($request->query('category_id'))
            ->filterStockStatus($request->query('stock_status'));

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        } else {
            $query->active();
        }

        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');
        $allowedSorts = ['name', 'sku', 'stock_current', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min((int) $request->query('per_page', 10), 100);
        $items = $query->paginate($perPage);

        return ApiResponse::success(
            ItemResource::collection($items)
                ->response()->getData(true),
            'Items retrieved successfully'
        );
    }

    public function show(Item $item): JsonResponse
    {
        $item->load([
            'category',
            'supplier',
            'stockTransactions' => function ($query) {
                $query->latest('transaction_date')
                    ->limit(10)
                    ->with(['creator', 'approver']);
            },
        ]);

        return ApiResponse::success(
            new ItemResource($item),
            'Item retrieved successfully.'
        );
    }

    public function store(StoreItemRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['sku'])) {
            $category = Category::findOrFail($data['category_id']);
            $data['sku'] = Item::generateSkuForCategory($category);
        }

        $data['stock_current'] = $data['stock_current'] ?? 0;
        $data['stock_minimum'] = $data['stock_minimum'] ?? 0;
        $data['purchase_price'] = $data['purchase_price'] ?? 0;
        $data['selling_price'] = $data['selling_price'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? true;

        $item = Item::create($data);
        $item->load(['category', 'supplier']);

        return ApiResponse::created(
            new ItemResource($item),
            'Item created successfully.'
        );
    }

    public function update(UpdateItemRequest $request, Item $item): JsonResponse
    {
        $data = $request->validated();

        if (array_key_exists('sku', $data) && empty($data['sku'])) {
            $categoryId = $data['category_id'] ?? $item->category_id;
            $category = Category::findOrFail($categoryId);
            $data['sku'] = Item::generateSkuForCategory($category);
        }

        $item->update($data);
        $item->load(['category', 'supplier']);

        return ApiResponse::success(
            new ItemResource($item),
            'Item updated successfully.'
        );

    }

    public function destroy(Item $item): JsonResponse
    {
        if ($item->hasStockTransactions()) {
            $item->update([
                'is_active' => false,
            ]);

            return ApiResponse::success(
                new ItemResource($item->load(['category', 'supplier'])),
                'Item has stock transactions and has been deactivated instead of deleted.'
            );
        }

        $item->delete();

        return ApiResponse::success(
            null,
            'Item deleted successfully.'
        );
    }
}

