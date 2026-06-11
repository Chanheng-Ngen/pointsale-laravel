<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(request()->user()->products()->latest()->get()->load('category'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = $request->user()->products()->create($validated);


        if (!$product->sku_code) {
            $product->update([
                'sku_code' => 'PRD-' . str_pad($product->id, 6, '0', STR_PAD_LEFT),
            ]);
        }

        return response()->json(
            $product->load('category'),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $product = request()->user()->products()->findOrFail($id);
        return response()->json(
            $product->load('category')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = request()->user()->products()->findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return response()->json(
            $product->load('category')
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = request()->user()->products()->findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Product deleted'], 200);
    }
}
