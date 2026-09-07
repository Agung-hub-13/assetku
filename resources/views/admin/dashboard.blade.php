@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="relative p-6 min-h-screen text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">

    <!-- 🌟 ELEMEN WATERMARK TRANSPARAN -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <div class="absolute inset-0 opacity-[0.4] dark:opacity-[0.15]"
            style="background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 40px 40px;"></div>

        <div class="text-slate-300 dark:text-slate-800 opacity-[0.25] dark:opacity-[0.2] transform scale-125 md:scale-150 p-4">
            <svg class="w-96 h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke-width="0.5" stroke-dasharray="4 4" />
                <circle cx="12" cy="12" r="7.5" stroke-width="0.75" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M12 2v4m0 12v4M2 12h4m12 0h4" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M12 8.5c-1.5 0-3.5 1-3.5 3.5 0 2.5 2 4 3.5 4.5 1.5-.5 3.5-2 3.5-4.5 0-2.5-2-3.5-3.5-3.5z" />
            </svg>
        </div>
    </div>

    <div class="relative z-10">
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-black text-slate-950 dark:text-white tracking-tight flex items-center gap-2">
                    <span class="h-6 w-1.5 bg-blue-600 rounded-full inline-block"></span>
                    Asset Management Command Center
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Monitoring real-time kuantitas aset, status operasional, alokasi area, dan aktivitas terbaru.</p>
            </div>
        </div>

        {{-- Filter Bar Modern Terintegrasi --}}
        <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl shadow-sm border border-slate-200/60 dark:border-slate-800 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 transition-all duration-300">
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider bg-slate-50 dark:bg-slate-950 px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter Scope
                </div>

                {{-- Dropdown 1: Lokasi Utama --}}
                <div class="w-full sm:w-48">
                    <select id="filter-lokasi" class="w-full text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                        <option value="" class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">Semua Lokasi Utama</option>
                        @if(isset($filterLokasi))
                            @foreach($filterLokasi as $lokasi)
                                <option value="{{ $lokasi->id }}" class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">{{ $lokasi->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- Dropdown 2: Departemen --}}
                <div class="w-full sm:w-48">
                    <select id="filter-departemen" class="w-full text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                        <option value="" class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">Semua Departemen</option>
                        @if(isset($filterDepartemen))
                            @foreach($filterDepartemen as $dept)
                                <option value="{{ $dept->id }}" class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">{{ $dept->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- Dropdown 3: Status Aset --}}
                <div class="w-full sm:w-40">
                    <select id="filter-status" class="w-full text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                        <option value="" class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">Semua Status</option>
                        <option value="active" class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">Aktif</option>
                        <option value="draft" class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">Draft</option>
                        <option value="maintenance" class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">Maintenance</option>
                        <option value="disposed" class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">Disposed</option>
                    </select>
                </div>
            </div>

            {{-- Tombol Terapkan --}}
            <button onclick="refreshDashboardData()" class="text-xs bg-slate-950 hover:bg-slate-900 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-all shadow-sm hover:shadow flex items-center gap-2 w-full sm:w-auto justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.253 8H18" />
                </svg>
                Terapkan Filter
            </button>
        </div>

        {{-- Kumpulan KPI Cards (5 Kolom: Kuantitas, Lokasi, Aktif, Maintenance, Perlu Perhatian) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            {{-- CARD 1: Total Aset --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Kuantitas Aset</p>
                    <h3 id="total-asset-count" class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                        {{ $totalAssetCount ?? 0 }} <span class="text-xs font-normal text-slate-400 dark:text-slate-500">Unit</span>
                    </h3>
                </div>
                <div class="p-3.5 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 rounded-2xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>

            {{-- CARD 2: Total Lokasi / Sebaran Area --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Sebaran Area</p>
                    <h3 id="total-location-count" class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                        {{ isset($locations) ? $locations->count() : 0 }} <span class="text-xs font-normal text-slate-400 dark:text-slate-500">Lokasi</span>
                    </h3>
                </div>
                <div class="p-3.5 bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 rounded-2xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>

            {{-- CARD 3: Aset Aktif --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Aset Aktif</p>
                    <h3 id="total-active-count" class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                        {{ $totalActive ?? 0 }} <span class="text-xs font-normal text-slate-400 dark:text-slate-500">Unit</span>
                    </h3>
                </div>
                <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            {{-- CARD 4: Sedang Maintenance --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Sedang Maintenance</p>
                    <h3 id="total-maintenance-count" class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                        {{ $totalMaintenance ?? 0 }} <span class="text-xs font-normal text-slate-400 dark:text-slate-500">Unit</span>
                    </h3>
                </div>
                <div class="p-3.5 bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 rounded-2xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                </div>
            </div>

            {{-- CARD 5: Aset Perlu Perhatian / Nilai Buku Habis --}}
            <a href="/admin/assets?depreciated=1" class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm flex items-center justify-between transition-all duration-300 hover:border-red-300 dark:hover:border-red-900/60 hover:shadow-md group">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest group-hover:text-red-500 dark:group-hover:text-red-400 transition-colors">Perlu Perhatian</p>
                    <h3 id="total-depreciated-count" class="text-2xl font-black text-red-600 dark:text-red-500 mt-2">
                        {{ $totalBookValueHabis ?? 0 }} <span class="text-xs font-normal text-slate-400 dark:text-slate-500">Asset</span>
                    </h3>
                </div>
                <div class="p-3.5 bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 rounded-2xl group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </a>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Doughnut Chart Lokasi --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm transition-colors duration-300">
                <div class="mb-4">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">Distribusi Aset Per Lokasi</h4>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Proporsi kuantitas unit aset di area utama.</p>
                </div>
                <div class="relative h-64 w-full flex items-center justify-center">
                    <canvas id="chartLokasi"
                        data-labels="{{ json_encode($chartLokasiData['labels'] ?? []) }}"
                        data-values="{{ json_encode($chartLokasiData['values'] ?? []) }}">
                    </canvas>
                </div>
            </div>

            {{-- Bar Chart Departemen (Volume Unit) --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm transition-colors duration-300">
                <div class="mb-4">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">Volume Aset Per Departemen</h4>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Jumlah total unit kepemilikan berdasarkan departemen.</p>
                </div>
                <div class="relative h-64 w-full flex items-center justify-center">
                    <canvas id="chartDepartemen"
                        data-labels="{{ json_encode($chartDeptData['labels'] ?? []) }}"
                        data-values="{{ json_encode($chartDeptData['values'] ?? []) }}">
                    </canvas>
                </div>
            </div>

            {{-- Pie Chart Status Aset --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm transition-colors duration-300">
                <div class="mb-4">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">Breakdown Status Aset</h4>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Komposisi aset berdasarkan status operasional.</p>
                </div>
                <div class="relative h-64 w-full flex items-center justify-center">
                    <canvas id="chartStatus"
                        data-labels="{{ json_encode($chartStatusData['labels'] ?? []) }}"
                        data-values="{{ json_encode($chartStatusData['values'] ?? []) }}">
                    </canvas>
                </div>
            </div>

            {{-- Line Chart Tren Akuisisi Aset --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm transition-colors duration-300">
                <div class="mb-4">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">Tren Akuisisi Aset (12 Bulan)</h4>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Jumlah unit aset baru per bulan.</p>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="chartTrend"
                        data-labels="{{ json_encode($chartTrendData['labels'] ?? []) }}"
                        data-values="{{ json_encode($chartTrendData['values'] ?? []) }}">
                    </canvas>
                </div>
            </div>
        </div>

        {{-- Aktivitas Terbaru --}}
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm mt-6">
            <div class="mb-4">
                <h4 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">Aktivitas Terbaru</h4>
                <p class="text-xs text-slate-400 dark:text-slate-500">Mutasi, peminjaman, dan maintenance terakhir di seluruh sistem.</p>
            </div>
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentActivities as $act)
                <li class="py-3 flex items-start gap-3">
                    <span class="text-xl leading-none">{{ $act['icon'] }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $act['title'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $act['description'] }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $act['status'] }}</span>
                        <p class="text-[10px] text-slate-400 mt-1">{{ optional($act['time'])->diffForHumans() }}</p>
                    </div>
                </li>
                @empty
                <li class="py-6 text-center text-slate-400 text-xs">Belum ada aktivitas.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

{{-- Chart.js Library --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let chartLokasiInstance = null;
    let chartDepartemenInstance = null;
    let chartStatusInstance = null;
    let chartTrendInstance = null;

    const canvasLokasi = document.getElementById('chartLokasi');
    const canvasDept = document.getElementById('chartDepartemen');
    const canvasStatus = document.getElementById('chartStatus');
    const canvasTrend = document.getElementById('chartTrend');

    const initialChartLokasiLabels = canvasLokasi ? JSON.parse(canvasLokasi.getAttribute('data-labels') || '[]') : [];
    const initialChartLokasiValues = canvasLokasi ? JSON.parse(canvasLokasi.getAttribute('data-values') || '[]').map(Number) : [];

    const initialChartDeptLabels = canvasDept ? JSON.parse(canvasDept.getAttribute('data-labels') || '[]') : [];
    const initialChartDeptValues = canvasDept ? JSON.parse(canvasDept.getAttribute('data-values') || '[]').map(Number) : [];

    const initialStatusLabels = canvasStatus ? JSON.parse(canvasStatus.getAttribute('data-labels') || '[]') : [];
    const initialStatusValues = canvasStatus ? JSON.parse(canvasStatus.getAttribute('data-values') || '[]').map(Number) : [];

    const initialTrendLabels = canvasTrend ? JSON.parse(canvasTrend.getAttribute('data-labels') || '[]') : [];
    const initialTrendValues = canvasTrend ? JSON.parse(canvasTrend.getAttribute('data-values') || '[]').map(Number) : [];

    function getChartTextColor() {
        return document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b';
    }

    function getChartBorderColor() {
        return document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff';
    }

    const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

    function initOrUpdateCharts(lokasiLabels, lokasiValues, deptLabels, deptValues) {
        const textColor = getChartTextColor();
        const borderColor = getChartBorderColor();

        // 1. Doughnut Chart Lokasi
        if (canvasLokasi) {
            if (chartLokasiInstance) {
                chartLokasiInstance.data.labels = lokasiLabels;
                chartLokasiInstance.data.datasets[0].data = lokasiValues;
                chartLokasiInstance.data.datasets[0].borderColor = borderColor;
                chartLokasiInstance.options.plugins.legend.labels.color = textColor;
                chartLokasiInstance.update();
            } else {
                chartLokasiInstance = new Chart(canvasLokasi.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: lokasiLabels,
                        datasets: [{
                            data: lokasiValues,
                            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#64748b'],
                            borderWidth: 3,
                            borderColor: borderColor
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 8, usePointStyle: true, font: { size: 10, weight: '600' }, color: textColor, padding: 15 }
                            }
                        }
                    }
                });
            }
        }

        // 2. Bar Chart Departemen
        if (canvasDept) {
            if (chartDepartemenInstance) {
                chartDepartemenInstance.data.labels = deptLabels;
                chartDepartemenInstance.data.datasets[0].data = deptValues;
                chartDepartemenInstance.options.scales.x.ticks.color = textColor;
                chartDepartemenInstance.options.scales.y.ticks.color = textColor;
                chartDepartemenInstance.update();
            } else {
                chartDepartemenInstance = new Chart(canvasDept.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: deptLabels,
                        datasets: [{
                            label: 'Jumlah Unit',
                            data: deptValues,
                            backgroundColor: '#6366f1',
                            hoverBackgroundColor: '#4f46e5',
                            borderRadius: 12,
                            maxBarThickness: 32
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: textColor, font: { size: 10 } } },
                            y: {
                                grid: { color: document.documentElement.classList.contains('dark') ? '#33415533' : '#f1f5f9' },
                                beginAtZero: true,
                                ticks: { color: textColor, font: { size: 10 }, precision: 0 }
                            }
                        }
                    }
                });
            }
        }
    }

    function initOrUpdateStatusChart(labels, values) {
        if (!canvasStatus) return;
        const textColor = getChartTextColor();
        const borderColor = getChartBorderColor();

        if (chartStatusInstance) {
            chartStatusInstance.data.labels = labels;
            chartStatusInstance.data.datasets[0].data = values;
            chartStatusInstance.data.datasets[0].borderColor = borderColor;
            chartStatusInstance.options.plugins.legend.labels.color = textColor;
            chartStatusInstance.update();
            return;
        }

        chartStatusInstance = new Chart(canvasStatus.getContext('2d'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#10b981', '#94a3b8', '#f59e0b', '#ef4444', '#64748b', '#3b82f6'],
                    borderWidth: 3,
                    borderColor: borderColor
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 8, usePointStyle: true, font: { size: 10, weight: '600' }, color: textColor, padding: 15 }
                    }
                }
            }
        });
    }

    function initOrUpdateTrendChart(labels, values) {
        if (!canvasTrend) return;
        const textColor = getChartTextColor();

        if (chartTrendInstance) {
            chartTrendInstance.data.labels = labels;
            chartTrendInstance.data.datasets[0].data = values;
            chartTrendInstance.options.scales.x.ticks.color = textColor;
            chartTrendInstance.options.scales.y.ticks.color = textColor;
            chartTrendInstance.update();
            return;
        }

        chartTrendInstance = new Chart(canvasTrend.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Aset Baru',
                    data: values,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.12)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#3b82f6',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: textColor, font: { size: 10 } } },
                    y: { beginAtZero: true, ticks: { color: textColor, font: { size: 10 }, precision: 0 } }
                }
            }
        });
    }

    function refreshDashboardData() {
        const lokasi = document.getElementById('filter-lokasi').value;
        const departemen = document.getElementById('filter-departemen').value;
        const status = document.getElementById('filter-status').value;

        const params = new URLSearchParams();
        if (lokasi) params.append('lokasi', lokasi);
        if (departemen) params.append('department_id', departemen);
        if (status) params.append('status', status);

        const apiUrl = `/admin/api/dashboard-assets?${params.toString()}`;

        const applyButton = document.querySelector('button[onclick="refreshDashboardData()"]');
        applyButton.disabled = true;
        applyButton.innerHTML = `<span class="animate-spin mr-1">⌛</span> Memuat...`;

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) throw new Error('Respon server bermasalah');
                return response.json();
            })
            .then(data => {
                document.getElementById('total-asset-count').innerHTML = `${formatNumber(data.kpi.total_count)} <span class="text-xs font-normal text-slate-400">Unit</span>`;
                document.getElementById('total-location-count').innerHTML = `${formatNumber(data.kpi.total_location)} <span class="text-xs font-normal text-slate-400">Lokasi</span>`;

                if (data.kpi.total_book_value_habis !== undefined) {
                    document.getElementById('total-depreciated-count').innerHTML = `${formatNumber(data.kpi.total_book_value_habis)} <span class="text-xs font-normal text-slate-400">Asset</span>`;
                }
                if (data.kpi.total_active !== undefined) {
                    document.getElementById('total-active-count').innerHTML = `${formatNumber(data.kpi.total_active)} <span class="text-xs font-normal text-slate-400">Unit</span>`;
                }
                if (data.kpi.total_maintenance !== undefined) {
                    document.getElementById('total-maintenance-count').innerHTML = `${formatNumber(data.kpi.total_maintenance)} <span class="text-xs font-normal text-slate-400">Unit</span>`;
                }

                initOrUpdateCharts(
                    data.chart_lokasi.labels,
                    data.chart_lokasi.values,
                    data.chart_departemen.labels,
                    data.chart_departemen.values.map(Number)
                );

                initOrUpdateStatusChart(data.chart_status.labels, data.chart_status.values.map(Number));
                initOrUpdateTrendChart(data.chart_trend.labels, data.chart_trend.values.map(Number));
            })
            .catch(error => {
                console.error('Gagal memperbarui filter dashboard:', error);
                alert('Ada kendala saat memuat data filter terbaru.');
            })
            .finally(() => {
                applyButton.disabled = false;
                applyButton.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.253 8H18" />
                    </svg>
                    Terapkan Filter
                `;
            });
    }

    document.addEventListener("DOMContentLoaded", function() {
        initOrUpdateCharts(
            initialChartLokasiLabels,
            initialChartLokasiValues,
            initialChartDeptLabels,
            initialChartDeptValues
        );

        initOrUpdateStatusChart(initialStatusLabels, initialStatusValues);
        initOrUpdateTrendChart(initialTrendLabels, initialTrendValues);

        const toggleBtn = document.getElementById('dark-mode-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                setTimeout(() => {
                    if (chartLokasiInstance || chartDepartemenInstance || chartStatusInstance || chartTrendInstance) {
                        initOrUpdateCharts(
                            chartLokasiInstance ? chartLokasiInstance.data.labels : [],
                            chartLokasiInstance ? chartLokasiInstance.data.datasets[0].data : [],
                            chartDepartemenInstance ? chartDepartemenInstance.data.labels : [],
                            chartDepartemenInstance ? chartDepartemenInstance.data.datasets[0].data : []
                        );
                        if (chartStatusInstance) {
                            initOrUpdateStatusChart(chartStatusInstance.data.labels, chartStatusInstance.data.datasets[0].data);
                        }
                        if (chartTrendInstance) {
                            initOrUpdateTrendChart(chartTrendInstance.data.labels, chartTrendInstance.data.datasets[0].data);
                        }
                    }
                }, 50);
            });
        }
    });
</script>
@endsection