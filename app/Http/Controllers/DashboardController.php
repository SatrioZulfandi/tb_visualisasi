<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'month' => $request->input('month'),
            'provinsi' => $request->input('provinsi')
        ];

        // Base query for orders
        $query = DB::table('orders');

        if (!empty($filters['month'])) {
            $query->where('waktu_pesanan_dibuat', 'like', $filters['month'] . '%');
        }
        
        if (!empty($filters['provinsi'])) {
            $query->where('provinsi', $filters['provinsi']);
        }

        // 1. KPI Summary
        $summary = [
            'total_revenue' => (clone $query)->where('status_pesanan', 'Selesai')->sum('total_pembayaran'),
            'total_orders' => (clone $query)->count(),
            'total_canceled' => (clone $query)->where('status_pesanan', '!=', 'Selesai')->count(),
        ];
        $summary['cancellation_rate'] = $summary['total_orders'] > 0 
            ? round(($summary['total_canceled'] / $summary['total_orders']) * 100, 2) 
            : 0;

        // 2. Bar Chart: Top Kategori Produk (By Revenue)
        $barChart = (clone $query)
            ->where('status_pesanan', 'Selesai')
            ->select('product_categories as category', DB::raw('SUM(total_pembayaran) as total_revenue'))
            ->groupBy('category')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();

        // 3. Line Chart: Tren Penjualan Per Bulan
        $lineChart = (clone $query)
            ->where('status_pesanan', 'Selesai')
            ->select(DB::raw('substr(waktu_pesanan_dibuat, 1, 7) as month'), DB::raw('SUM(total_pembayaran) as revenue'))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // 4. Donut Chart: Metode Pembayaran
        $donutChart = (clone $query)
            ->select('metode_pembayaran', DB::raw('COUNT(*) as total'))
            ->groupBy('metode_pembayaran')
            ->orderBy('total', 'desc')
            ->get();

        // 5. K-Means Clustering: Aggregated stats per cluster
        $clusterStats = (clone $query)
            ->select(
                'cluster_label',
                DB::raw('AVG(total_qty) as avg_qty'),
                DB::raw('AVG(total_pembayaran) as avg_spend'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->whereNotNull('cluster_label')
            ->groupBy('cluster_label')
            ->orderBy('cluster_label')
            ->get();

        // 6. Random Forest Feature Importance (Read from JSON)
        $rfJsonPath = public_path('data/rf_feature_importance.json');
        $featureImportance = [];
        if (File::exists($rfJsonPath)) {
            $featureImportance = json_decode(File::get($rfJsonPath), true);
        }

        // Filter Options
        $months = DB::table('orders')->select(DB::raw('substr(waktu_pesanan_dibuat, 1, 7) as month'))->distinct()->orderBy('month')->pluck('month');
        $provinces = DB::table('orders')->select('provinsi')->distinct()->orderBy('provinsi')->pluck('provinsi');

        return view('dashboard', compact(
            'summary', 'barChart', 'lineChart', 'donutChart', 
            'clusterStats', 'featureImportance', 
            'months', 'provinces', 'filters'
        ));
    }
}
