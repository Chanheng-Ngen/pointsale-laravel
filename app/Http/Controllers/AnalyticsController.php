<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'week');
        $startDate = $this->getStartDate($period);

        return response()->json([
            'summary' => $this->getSummary($startDate),
            'sales_trends' => $this->getSalesTrends($startDate, $period),
            'category_sales' => $this->getCategorySales($startDate),
            'top_products' => $this->getTopProducts($startDate),
        ]);
    }

    public function dashboard()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();

        $todaySales = Transaction::where('type', 'sale')
            ->whereDate('created_at', $today)
            ->sum('amount');

        $todayTransactions = Transaction::where('type', 'sale')
            ->whereDate('created_at', $today)
            ->count();

        $totalProducts = Product::count();
        $lowStockItems = Product::where('quantity', '<', 10)->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        
        $thisWeekSales = Transaction::where('type', 'sale')
            ->where('created_at', '>=', $thisWeek)
            ->sum('amount');

        return response()->json([
            'today_sales' => (float)$todaySales,
            'today_transactions' => $todayTransactions,
            'total_products' => $totalProducts,
            'low_stock_items' => $lowStockItems,
            'pending_orders' => $pendingOrders,
            'this_week_sales' => (float)$thisWeekSales,
        ]);
    }

    private function getStartDate($period)
    {
        return match ($period) {
            'month' => Carbon::now()->subMonth(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subWeek(),
        };
    }

    private function getSummary($startDate)
    {
        $transactions = Transaction::where('type', 'sale')
            ->where('created_at', '>=', $startDate)
            ->get();

        $totalRevenue = $transactions->sum('amount');
        $orderCount = $transactions->count();
        $avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;
        $totalItems = $transactions->sum('item_count');

        // Simple mock for change percentage
        return [
            'total_revenue' => $totalRevenue,
            'avg_order_value' => $avgOrderValue,
            'order_count' => $orderCount,
            'total_items' => $totalItems,
            'revenue_change' => '+12.5%', // Mocked for now
            'order_change' => '+8.2%', // Mocked for now
        ];
    }

    private function getSalesTrends($startDate, $period)
    {
        $format = $period === 'year' ? '%Y-%m' : '%Y-%m-%d';
        
        $trends = Transaction::where('type', 'sale')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '$format') as date"),
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $trends;
    }

    private function getCategorySales($startDate)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.status', 'completed')
            ->where('orders.created_at', '>=', $startDate)
            ->select('categories.name', DB::raw('SUM(order_items.quantity * order_items.price) as revenue'), DB::raw('SUM(order_items.quantity) as count'))
            ->groupBy('categories.id', 'categories.name')
            ->get();
    }

    private function getTopProducts($startDate)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('orders.created_at', '>=', $startDate)
            ->select('order_items.name', DB::raw('SUM(order_items.quantity) as sold_count'), DB::raw('SUM(order_items.quantity * order_items.price) as revenue'))
            ->groupBy('order_items.product_id', 'order_items.name')
            ->orderByDesc('sold_count')
            ->limit(5)
            ->get();
    }
}
