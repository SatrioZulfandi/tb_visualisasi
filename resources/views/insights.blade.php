@extends('layouts.app')

@section('title', 'Insights - E-Commerce Analytics')

@section('extra-styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        margin-bottom: 24px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.1);
    }
    .hero-gradient {
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        color: white;
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 32px;
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);
    }
    .hero-metric {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
    }
    .hero-metric .label {
        font-size: 13px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.9;
        margin-bottom: 8px;
    }
    .hero-metric .value {
        font-size: 24px;
        font-weight: 700;
    }
    .section-title {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .section-title i {
        color: var(--accent-brand);
    }
    .insight-pill {
        display: inline-block;
        padding: 6px 12px;
        background: #f0fdf4;
        color: #166534;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        margin-right: 8px;
        margin-bottom: 8px;
    }
    .insight-pill.bad {
        background: #fef2f2;
        color: #991b1b;
    }
    .insight-pill.warning {
        background: #fffbeb;
        color: #92400e;
    }
    .cluster-profile {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        border-radius: 12px;
        background: #f8fafc;
        margin-bottom: 12px;
        border-left: 4px solid transparent;
    }
    .cluster-1 { border-left-color: #3b82f6; } /* Budget */
    .cluster-2 { border-left-color: #f59e0b; } /* Mid-Range */
    .cluster-3 { border-left-color: #10b981; } /* Premium */
    
    .list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .list-item:last-child { border-bottom: none; }
    
    .progress-bar-bg {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        width: 100px;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 4px;
    }
</style>
@endsection

@section('content')
<div class="mb-8">
    <h1 style="font-size: 28px; font-weight: 800; color: #0f172a;">Data Insights</h1>
    <p style="color: #64748b; margin-top: 4px;">Temuan kunci dari analisis data eksploratif, clustering, dan feature importance.</p>
</div>

<!-- SECTION 1: HERO METRICS -->
<div class="hero-gradient">
    <div style="margin-bottom: 24px;">
        <h2 style="font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <i data-feather="zap"></i> Key Takeaways
        </h2>
        <p style="opacity: 0.9;">Ringkasan metrik terpenting dari seluruh dataset.</p>
    </div>
    
    <div class="grid-4">
        <div class="hero-metric">
            <div class="label">Kategori #1</div>
            <div class="value">{{ $heroMetrics['top_category'] }}</div>
        </div>
        <div class="hero-metric">
            <div class="label">Pembayaran Utama</div>
            <div class="value">{{ $heroMetrics['top_payment'] }}</div>
        </div>
        <div class="hero-metric">
            <div class="label">Cancellation Rate</div>
            <div class="value">{{ $heroMetrics['cancellation_rate'] }}%</div>
        </div>
        <div class="hero-metric">
            <div class="label">Faktor Batal #1</div>
            <div class="value" style="font-size: 16px; margin-top: 5px;">{{ $heroMetrics['top_cancel_factor'] }}</div>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- SECTION 2: SALES TREND -->
    <div class="glass-card">
        <h3 class="section-title"><i data-feather="trending-up"></i> Pola Penjualan</h3>
        <div style="margin-bottom: 16px;">
            @if($growthRate > 0)
                <span class="insight-pill">📈 Growth: +{{ $growthRate }}%</span>
            @elseif($growthRate < 0)
                <span class="insight-pill bad">📉 Penurunan: {{ $growthRate }}%</span>
            @endif
            @if($bestMonth)
                <span class="insight-pill">⭐ Bulan Terbaik: {{ $bestMonth->month }}</span>
            @endif
        </div>
        <div id="trendChart"></div>
    </div>

    <!-- SECTION 3: K-MEANS SEGMENTATION -->
    <div class="glass-card">
        <h3 class="section-title"><i data-feather="users"></i> Profil Pelanggan (K-Means)</h3>
        <p style="color: #64748b; font-size: 13px; margin-bottom: 16px;">Data dibagi menjadi 3 segmen berdasarkan perilaku belanja (Spend vs Qty).</p>
        
        @foreach($clusterProfiles as $index => $profile)
            @php
                $clusterClass = 'cluster-1';
                $icon = 'user';
                if (stripos($profile->cluster_label, 'mid') !== false) { $clusterClass = 'cluster-2'; $icon = 'shopping-bag'; }
                if (stripos($profile->cluster_label, 'premium') !== false) { $clusterClass = 'cluster-3'; $icon = 'star'; }
            @endphp
            <div class="cluster-profile {{ $clusterClass }}">
                <div style="background: white; padding: 10px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <i data-feather="{{ $icon }}" style="width: 20px; height: 20px; color: #475569;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: #0f172a;">{{ $profile->cluster_label }}</div>
                    <div style="font-size: 12px; color: #64748b;">
                        Avg Spend: Rp {{ number_format($profile->avg_spend, 0, ',', '.') }} | Qty: {{ round($profile->avg_qty, 1) }}
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 13px; font-weight: 600;">{{ number_format($profile->total_users) }} order</div>
                    <div style="font-size: 11px; color: {{ $profile->cancel_rate > 10 ? '#ef4444' : '#10b981' }};">
                        Batal: {{ $profile->cancel_rate }}%
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="grid-2">
    <!-- SECTION 4: CANCELLATION ANALYSIS -->
    <div class="glass-card">
        <h3 class="section-title"><i data-feather="alert-circle"></i> Analisis Pembatalan</h3>
        <p style="color: #64748b; font-size: 13px; margin-bottom: 16px;">Alasan terbanyak mengapa pesanan dibatalkan (selain alasan tidak jelas).</p>
        
        <div style="margin-bottom: 24px;">
            @foreach($cancelReasons as $reason)
                <div class="list-item">
                    <span style="font-size: 13px; font-weight: 500;">{{ Str::limit($reason->reason, 40) }}</span>
                    <span style="background: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        {{ number_format($reason->total) }}
                    </span>
                </div>
            @endforeach
        </div>

        <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 12px;">Cancel Rate by Payment Method</h4>
        <div>
            @foreach($cancelByPayment as $payment)
                <div class="list-item" style="padding: 8px 0;">
                    <span style="font-size: 13px;">{{ $payment->metode_pembayaran }}</span>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 12px; font-weight: 600;">{{ $payment->cancel_rate }}%</span>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width: {{ min(100, $payment->cancel_rate) }}%; background: {{ $payment->cancel_rate > 15 ? '#ef4444' : ($payment->cancel_rate > 8 ? '#f59e0b' : '#10b981') }};"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- SECTION 5: GEOGRAPHIC -->
    <div class="glass-card">
        <h3 class="section-title"><i data-feather="map-pin"></i> Distribusi Geografis</h3>
        
        <div style="margin-bottom: 24px;">
            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 12px; color: #10b981;">Top 5 Provinsi (Revenue)</h4>
            @foreach($topRevenueProvinces->take(5) as $prov)
                <div class="list-item">
                    <span style="font-size: 13px; font-weight: 500;">{{ $prov->provinsi }}</span>
                    <span style="font-size: 13px; font-weight: 600; color: #0f172a;">Rp {{ number_format($prov->total_revenue / 1000000, 1, ',', '.') }} Jt</span>
                </div>
            @endforeach
        </div>

        <div>
            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 12px; color: #ef4444;">Top 5 Provinsi (Cancel Rate Tertinggi)</h4>
            @foreach($topCancelProvinces->take(5) as $prov)
                <div class="list-item">
                    <span style="font-size: 13px; font-weight: 500;">{{ $prov->provinsi }}</span>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 12px; font-weight: 600; color: #ef4444;">{{ $prov->cancel_rate }}%</span>
                        <span style="font-size: 11px; color: #94a3b8;">({{ number_format($prov->total_orders) }} orders)</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Sales Trend Line Chart
    const trendData = @json($monthlySales);
    const trendOptions = {
        series: [{
            name: 'Revenue',
            data: trendData.map(item => item.revenue)
        }],
        chart: {
            type: 'area',
            height: 250,
            toolbar: { show: false },
            sparkline: { enabled: false }
        },
        colors: ['#6366f1'],
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [50, 100] }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        xaxis: {
            categories: trendData.map(item => item.month),
            labels: { style: { fontSize: '10px' } }
        },
        yaxis: {
            labels: {
                formatter: (value) => "Rp " + (value / 1000000).toFixed(0) + " Jt",
                style: { fontSize: '10px' }
            }
        },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 }
    };
    new ApexCharts(document.querySelector("#trendChart"), trendOptions).render();
</script>
@endsection
