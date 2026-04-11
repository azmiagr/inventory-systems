<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Http\Responses\ApiResponse;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Str;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::withCount('items');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');
        $allowedSorts = ['name', 'created_at', 'update_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min((int) $request->query('per_page', 10), 100);
        $suppliers = $query->paginate($perPage);

        return ApiResponse::success(
            SupplierResource::collection($suppliers)->response()->getData(true),
            'Suppliers retrieved successfully.'
        );

    }


    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->loadCount('items');

        return ApiResponse::success(
            new SupplierResource($supplier),
            'Supplier retrieved successfully.'
        );

    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create($request->validated());
        $supplier->loadCount('items');

        return ApiResponse::created(
            new SupplierResource($supplier),
            'Supplier created successfully.'
        );
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $supplier->update($data);
        $supplier->loadCount('items');

        return ApiResponse::success(
            new SupplierResource($supplier),
            'Supplier updated successfully.'
        );
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        if ($supplier->items()->exists()) {
            return ApiResponse::conflict('Supplier has items and cannot be deleted.');
        }

        $supplier->delete();

        return ApiResponse::success(
            null,
            'Supplier deleted successfully.'
        );
    }
}


