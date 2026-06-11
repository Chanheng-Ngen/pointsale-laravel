<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        return Order::with(['items.product', 'transactions'])->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'total' => 'required|numeric',
            'payment_method' => 'required|string',
            'customer_name' => 'nullable|string',
            'status' => 'nullable|in:pending,completed,cancelled',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);

        return DB::transaction(function () use ($validated) {

            // Validate stock for all items first
            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                if ($product->quantity < $item['quantity']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => ['Not enough stock for ' . $product->name],
                    ]);
                }
            }

            $itemCount = collect($validated['items'])
                ->sum('quantity');

            $order = Order::create([
                'status' => $validated['status'] ?? 'pending',
                'item_count' => $itemCount,
                'subtotal' => $validated['subtotal'],
                'tax' => $validated['tax'],
                'total' => $validated['total'],
                'payment_method' => $validated['payment_method'],
                'customer_name' => $validated['customer_name'] ?? 'Customer',
            ]);

            $order->update([
                'order_number' => 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                $product->decrement(
                    'quantity',
                    $item['quantity']
                );
            }

            if ($order->status === 'completed') {
                $this->createTransaction($order, 'sale');
            }

            return response()->json(
                $order->load(['items.product', 'transactions']),
                201
            );
        });
    }

    public function updateStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        
        $order->update([
            'status' => $validated['status'],
        ]);

        if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
            $this->createTransaction($order, 'sale');
        }

        return response()->json([
            'message' => 'Order status updated',
            'order' => $order->load('items'),
        ]);
    }

    private function createTransaction(Order $order, $type = 'sale')
    {
        return Transaction::create([
            'type' => $type,
            'description' => 'Order #' . ($order->order_number ?? $order->id),
            'payment_method' => $order->payment_method,
            'amount' => $order->total,
            'item_count' => $order->item_count,
            'order_id' => $order->id,
        ]);
    }

    public function show(string $id)
    {
        return Order::with(['items.product', 'transactions'])
            ->findOrFail($id);
    }

    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);

        $order->delete();

        return response()->json([
            'message' => 'Order deleted'
        ]);
    }
}