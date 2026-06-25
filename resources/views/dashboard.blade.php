@extends('layouts.app')

@section('title', 'E-Commerce Dashboard')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h2 style="font-size: 24px; font-weight: 700; color: #0f172a;">E-Commerce Sales Analytics</h2>
        <p style="color: #64748b; margin-top: 4px;">Analisis Performa Penjualan & Perilaku Pelanggan</p>
    </div>
    
    <form action="/" method="GET" style="display: flex; gap: 12px; align-items: center;">
        <select name="month" class="form-select">
            <option value="">Semua Bulan</option>
            @foreach($months as $m)
                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
        </select>
        
        <select name="provinsi" class="form-select">
            <option value="">Semua Provinsi</option>
            @foreach($provinces as $p)
                <option value="{{ $p }}" {{ request('provinsi') == $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
        
        <button type="submit" class="btn">Filter</button>
        <a href="/" class="btn" style="background: #e2e8f0; color: #475569; text-decoration: none;">Reset</a>
    </form>
</div>

{{-- KPI CARDS --}}
<div class="grid-4" style="margin-bottom: 24px;">
    <div class="card" style="margin-bottom: 0;">
        <p style="color: #64748b; font-size: 14px; font-weight: 600;">Total Revenue</p>
        <h3 style="font-size: 28px; font-weight: 700; color: #0f172a; margin-top: 8px;">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</h3>
    </div>
    <div class="card" style="margin-bottom: 0;">
        <p style="color: #64748b; font-size: 14px; font-weight: 600;">Total Orders</p>
        <h3 style="font-size: 28px; font-weight: 700; color: #0f172a; margin-top: 8px;">{{ number_format($summary['total_orders']) }}</h3>
    </div>
    <div class="card" style="margin-bottom: 0;">
        <p style="color: #64748b; font-size: 14px; font-weight: 600;">Orders Canceled</p>
        <h3 style="font-size: 28px; font-weight: 700; color: #ef4444; margin-top: 8px;">{{ number_format($summary['total_canceled']) }}</h3>
    </div>
    <div class="card" style="margin-bottom: 0;">
        <p style="color: #64748b; font-size: 14px; font-weight: 600;">Cancellation Rate</p>
        <h3 style="font-size: 28px; font-weight: 700; color: #f59e0b; margin-top: 8px;">{{ $summary['cancellation_rate'] }}%</h3>
    </div>
</div>

{{-- CHARTS ROW 1 --}}
<div class="grid-2">
    <div class="card">
        <h3 class="card-title">Tren Penjualan (Bulanan)</h3>
        <div id="lineChart"></div>
    </div>
    <div class="card">
        <h3 class="card-title">Top 10 Kategori Produk (By Revenue)</h3>
        <div id="barChart"></div>
    </div>
</div>

{{-- CHARTS ROW 2 --}}
<div class="grid-2">
    <div class="card">
        <h3 class="card-title">Proporsi Metode Pembayaran</h3>
        <div id="donutChart"></div>
    </div>
    <div class="card">
        <h3 class="card-title">K-Means: Customer Segmentation</h3>
        <p style="font-size: 12px; color: #94a3b8; margin-bottom: 12px;">Perbandingan rata-rata Quantity & Spend per segmen pelanggan.</p>
        <div id="clusterBarChart"></div>
    </div>
</div>

{{-- CHARTS ROW 3 (Random Forest) --}}
<div class="card">
    <h3 class="card-title">Random Forest: Faktor Utama Pembatalan Pesanan</h3>
    <p style="font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Seberapa besar pengaruh masing-masing faktor terhadap pembatalan pesanan (dalam persen).</p>
    <p style="font-size: 11px; color: #f59e0b; font-style: italic; margin-bottom: 12px;">
        <svg style="width: 12px; height: 12px; display: inline-block; vertical-align: middle; margin-right: 2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        *Insight ini merupakan hasil model prediktif dari keseluruhan data historis dan tidak terpengaruh oleh filter bulan atau provinsi di atas.
    </p>
    <div id="rfChart"></div>
    <div id="rfInsight" style="margin-top: 16px; padding: 14px 18px; background: linear-gradient(135deg, #f0f4ff, #ede9fe); border-left: 4px solid #8b5cf6; border-radius: 8px; font-size: 13px; color: #334155; line-height: 1.6; display: none;">
        <strong style="color: #6d28d9;">💡 Insight:</strong>
        <span id="rfInsightText"></span>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // 1. Line Chart (Tren Penjualan)
    const lineData = @json($lineChart);
    const lineOptions = {
        series: [{
            name: 'Revenue (Rp)',
            data: lineData.map(item => item.revenue)
        }],
        chart: { type: 'area', height: 320, toolbar: { show: false } },
        colors: ['#3b82f6'],
        xaxis: { categories: lineData.map(item => item.month) },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth' },
        yaxis: {
            labels: {
                formatter: (value) => { return "Rp " + (value / 1000000).toFixed(1) + " Jt" }
            }
        }
    };
    new ApexCharts(document.querySelector("#lineChart"), lineOptions).render();

    // 2. Bar Chart (Top Categories)
    const barData = @json($barChart);
    const barOptions = {
        series: [{
            name: 'Revenue (Rp)',
            data: barData.map(item => item.total_revenue)
        }],
        chart: { type: 'bar', height: 320, toolbar: { show: false } },
        colors: ['#10b981'],
        plotOptions: {
            bar: { borderRadius: 4, horizontal: true }
        },
        dataLabels: { enabled: false },
        xaxis: { categories: barData.map(item => item.category) }
    };
    new ApexCharts(document.querySelector("#barChart"), barOptions).render();

    // 3. Donut Chart (Metode Pembayaran)
    const donutData = @json($donutChart);
    const donutOptions = {
        series: donutData.map(item => item.total),
        labels: donutData.map(item => item.metode_pembayaran),
        chart: { type: 'donut', height: 320 },
        colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'],
        plotOptions: { pie: { donut: { size: '60%' } } },
        legend: { position: 'bottom' }
    };
    new ApexCharts(document.querySelector("#donutChart"), donutOptions).render();

    // 4. Grouped Bar Chart (K-Means Cluster Stats)
    const clusterData = @json($clusterStats);
    const clusterLabels = clusterData.map(item => item.cluster_label);
    const clusterBarOptions = {
        series: [
            {
                name: 'Rata-rata Quantity',
                data: clusterData.map(item => Number(Number(item.avg_qty).toFixed(1)))
            },
            {
                name: 'Rata-rata Spend (Rp)',
                data: clusterData.map(item => Math.round(Number(item.avg_spend)))
            }
        ],
        chart: { 
            type: 'bar', 
            height: 320, 
            toolbar: { show: false },
            fontFamily: 'Inter, sans-serif'
        },
        colors: ['#3b82f6', '#f59e0b'],
        plotOptions: {
            bar: { 
                borderRadius: 6, 
                columnWidth: '50%',
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: { 
            enabled: true, 
            offsetY: -20,
            style: { fontSize: '11px', colors: ['#334155'] },
            formatter: function(val, opts) {
                if (opts.seriesIndex === 1) return 'Rp ' + (val / 1000).toFixed(0) + 'K';
                return val;
            }
        },
        xaxis: { 
            categories: clusterLabels,
            labels: { style: { fontWeight: 600 } }
        },
        yaxis: [
            {
                title: { text: 'Rata-rata Quantity' },
                labels: { formatter: (val) => val.toFixed(0) }
            },
            {
                opposite: true,
                title: { text: 'Rata-rata Spend (Rp)' },
                labels: { formatter: (val) => 'Rp ' + (val / 1000).toFixed(0) + 'K' }
            }
        ],
        legend: { position: 'top' },
        tooltip: {
            y: {
                formatter: function(val, opts) {
                    if (opts.seriesIndex === 1) return 'Rp ' + val.toLocaleString('id-ID');
                    return val + ' item';
                }
            }
        },
        grid: { borderColor: '#e2e8f0', strokeDashArray: 4 }
    };
    new ApexCharts(document.querySelector("#clusterBarChart"), clusterBarOptions).render();

    // 5. Horizontal Bar Chart (Random Forest Feature Importance)
    const rfData = @json($featureImportance);
    if(rfData && rfData.features) {
        // Convert to percentage
        const totalImportance = rfData.importances.reduce((a, b) => a + b, 0);
        const rfPercentages = rfData.importances.map(val => Number(((val / totalImportance) * 100).toFixed(1)));
        
        // Sort descending
        const rfCombined = rfData.features.map((f, i) => ({ feature: f, pct: rfPercentages[i] }));
        rfCombined.sort((a, b) => b.pct - a.pct);
        
        // Gradient colors: most important = darkest purple
        const rfColors = rfCombined.map((item, i) => {
            const opacity = 1 - (i * 0.13);
            return `rgba(139, 92, 246, ${Math.max(opacity, 0.25)})`;
        });

        const rfOptions = {
            series: [{
                name: 'Pengaruh',
                data: rfCombined.map(item => item.pct)
            }],
            chart: { 
                type: 'bar', 
                height: 300, 
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            colors: rfColors,
            plotOptions: {
                bar: { 
                    borderRadius: 6, 
                    horizontal: true, 
                    barHeight: '55%',
                    distributed: true,
                    dataLabels: { position: 'right' }
                }
            },
            dataLabels: { 
                enabled: true, 
                formatter: function(val) { return val + '%'; },
                offsetX: 5,
                style: { fontSize: '12px', fontWeight: 700, colors: ['#334155'] }
            },
            xaxis: { 
                categories: rfCombined.map(item => item.feature),
                max: 100,
                labels: { 
                    formatter: (val) => val + '%',
                    style: { colors: '#94a3b8' }
                },
                axisBorder: { show: false }
            },
            yaxis: {
                labels: { 
                    style: { fontSize: '13px', fontWeight: 600, colors: '#334155' } 
                }
            },
            legend: { show: false },
            tooltip: {
                y: {
                    formatter: function(val) { return val + '% pengaruh terhadap pembatalan'; }
                }
            },
            grid: { 
                borderColor: '#f1f5f9', 
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } },
                padding: { left: 10, right: 30 }
            }
        };
        new ApexCharts(document.querySelector("#rfChart"), rfOptions).render();

        // Generate insight text
        const topFactor = rfCombined[0];
        const insightEl = document.getElementById('rfInsight');
        const insightText = document.getElementById('rfInsightText');
        insightText.innerHTML = `Faktor <strong>"${topFactor.feature}"</strong> memiliki pengaruh paling besar (${topFactor.pct}%) terhadap pembatalan pesanan. ` +
            (rfCombined.length > 1 ? `Faktor kedua adalah <strong>"${rfCombined[1].feature}"</strong> (${rfCombined[1].pct}%), ` : '') +
            `sementara faktor lainnya memiliki pengaruh yang relatif kecil.`;
        insightEl.style.display = 'block';
    }
</script>
@endsection
