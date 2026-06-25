<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InsightController extends Controller
{
    public function index()
    {
        $query = DB::table('orders');

        // 1. Hero Cards Metrics
        $topCategory = (clone $query)->where('status_pesanan', 'Selesai')
            ->select('product_categories', DB::raw('SUM(total_pembayaran) as revenue'))
            ->groupBy('product_categories')
            ->orderByDesc('revenue')
            ->first();

        $topPayment = (clone $query)
            ->select('metode_pembayaran', DB::raw('COUNT(*) as total'))
            ->groupBy('metode_pembayaran')
            ->orderByDesc('total')
            ->first();

        $totalOrders = (clone $query)->count();
        $canceledOrders = (clone $query)->where('status_pesanan', '!=', 'Selesai')->count();
        $cancellationRate = $totalOrders > 0 ? round(($canceledOrders / $totalOrders) * 100, 2) : 0;
        
        $rfJsonPath = public_path('data/rf_feature_importance.json');
        $topFactor = "Total Pembayaran";
        $topFactorScore = "88.8%";
        if (File::exists($rfJsonPath)) {
            $rfData = json_decode(File::get($rfJsonPath), true);
            if (!empty($rfData['features']) && !empty($rfData['importances'])) {
                $totalImportance = array_sum($rfData['importances']);
                $topFactor = $rfData['features'][0];
                $topFactorScore = round(($rfData['importances'][0] / $totalImportance) * 100, 1) . '%';
            }
        }

        $heroMetrics = [
            'top_category' => $topCategory ? $topCategory->product_categories : '-',
            'top_payment' => $topPayment ? $topPayment->metode_pembayaran : '-',
            'cancellation_rate' => $cancellationRate,
            'top_cancel_factor' => "$topFactor ($topFactorScore)"
        ];

        // 2. Sales Trend (Best vs Worst Month)
        $monthlySales = (clone $query)->where('status_pesanan', 'Selesai')
            ->select(DB::raw('substr(waktu_pesanan_dibuat, 1, 7) as month'), DB::raw('SUM(total_pembayaran) as revenue'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
            
        // Calculate growth rate if more than 1 month
        $growthRate = 0;
        $bestMonth = null;
        if ($monthlySales->count() > 1) {
            $firstMonth = $monthlySales->first()->revenue;
            $lastMonth = $monthlySales->last()->revenue;
            if ($firstMonth > 0) {
                $growthRate = round((($lastMonth - $firstMonth) / $firstMonth) * 100, 2);
            }
        }
        if ($monthlySales->count() > 0) {
            $bestMonth = $monthlySales->sortByDesc('revenue')->first();
        }

        // 3. K-Means Segment Profiles
        $clusterProfiles = (clone $query)
            ->whereNotNull('cluster_label')
            ->select(
                'cluster_label',
                DB::raw('AVG(total_qty) as avg_qty'),
                DB::raw('AVG(total_pembayaran) as avg_spend'),
                DB::raw('COUNT(*) as total_users'),
                DB::raw('SUM(CASE WHEN is_batal = 1 THEN 1 ELSE 0 END) as total_canceled')
            )
            ->groupBy('cluster_label')
            ->orderBy('avg_spend', 'asc') // Budget to Premium
            ->get();
            
        foreach ($clusterProfiles as $profile) {
            $profile->cancel_rate = $profile->total_users > 0 
                ? round(($profile->total_canceled / $profile->total_users) * 100, 1) 
                : 0;
        }

        // 4. Cancellation Analysis
        $cancelReasons = (clone $query)
            ->where('is_batal', 1)
            ->whereNotNull('alasan_pembatalan')
            ->where('alasan_pembatalan', '!=', 'Tidak Batal')
            ->select('alasan_pembatalan as reason', DB::raw('COUNT(*) as total'))
            ->groupBy('reason')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $cancelByPayment = (clone $query)
            ->select(
                'metode_pembayaran',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN is_batal = 1 THEN 1 ELSE 0 END) as total_canceled')
            )
            ->groupBy('metode_pembayaran')
            ->having('total_orders', '>', 50)
            ->get()
            ->map(function ($item) {
                $item->cancel_rate = round(($item->total_canceled / $item->total_orders) * 100, 1);
                return $item;
            })
            ->sortByDesc('cancel_rate')
            ->values()
            ->take(5);

        // 5. Geographic Distribution
        $geoStats = (clone $query)
            ->select(
                'provinsi',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN status_pesanan = "Selesai" THEN total_pembayaran ELSE 0 END) as total_revenue'),
                DB::raw('SUM(CASE WHEN is_batal = 1 THEN 1 ELSE 0 END) as total_canceled')
            )
            ->groupBy('provinsi')
            ->having('total_orders', '>', 10)
            ->get()
            ->map(function ($item) {
                $item->cancel_rate = round(($item->total_canceled / $item->total_orders) * 100, 1);
                return $item;
            });

        $topRevenueProvinces = $geoStats->sortByDesc('total_revenue')->take(10)->values();
        $topCancelProvinces = $geoStats->sortByDesc('cancel_rate')->take(10)->values();

        return view('insights', compact(
            'heroMetrics', 
            'monthlySales', 
            'bestMonth', 
            'growthRate',
            'clusterProfiles',
            'cancelReasons',
            'cancelByPayment',
            'topRevenueProvinces',
            'topCancelProvinces'
        ));
    }
}
