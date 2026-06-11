<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        return Transaction::with('order.items.product')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:sale',
            'description' => 'required|string',
            'payment_method' => 'required|string',
            'amount' => 'required|numeric',
            'item_count' => 'nullable|integer',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        $transaction = Transaction::create($validated);

        return response()->json($transaction, 201);
    }

    public function stats()
    {
        $totalSales = Transaction::where('type', 'sale')->sum('amount');
        $totalItems = Transaction::where('type', 'sale')->sum('item_count');
        $transactionCount = Transaction::where('type', 'sale')->count();

        return response()->json([
            'total_sales' => $totalSales,
            'total_items' => $totalItems,
            'transaction_count' => $transactionCount,
            'average_transaction_value' => $transactionCount > 0 ? $totalSales / $transactionCount : 0,
        ]);
    }

    public function show(string $id)
    {
        return Transaction::with('order.items.product')->findOrFail($id);
    }
}
