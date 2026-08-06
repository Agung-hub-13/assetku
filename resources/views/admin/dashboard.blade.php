@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="relative p-6 min-h-screen text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">

    <!-- 🌟 ELEMEN WATERMARK TRANSPARAN -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <!-- Pola Grid Halus sebagai Dasar -->
        <div class="absolute inset-0 opacity-[0.4] dark:opacity-[0.15]"
            style="background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 40px 40px;"></div>

        <!-- Ikon Geometris Besar di Tengah (Statis & Transparan) -->
        <div class="text-slate-300 dark:text-slate-800 opacity-[0.25] dark:opacity-[0.2] transform scale-125 md:scale-150 p-4">
            <svg class="w-96 h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <!-- Lingkaran Luar Putus-putus -->
                <circle cx="12" cy="12" r="10" stroke-width="0.5" stroke-dasharray="4 4" />
                <!-- Lingkaran Tengah Solid Tipis -->
                <circle cx="12" cy="12" r="7.5" stroke-width="0.75" />
                <!-- Pola Akses Struktur Keamanan Simetris -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M12 2v4m0 12v4M2 12h4m12 0h4" />
                <!-- Inti Perisai Abstrak di Pusat -->
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
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Monitoring real-time aset, alokasi area, visualisasi investasi, dan depresiasi.</p>
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

                {{-- Dropdown 2: Status Aset --}}
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

        {{-- Kumpulan KPI Cards Modern --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- CARD 1: Total Aset --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Kuantitas Aset</p>
                    <h3 id="total-asset-count" class="text-3xl font-black text-slate-900 dark:text-white mt-2">
                        {{ $totalAssetCount ?? 0 }} <span class="text-xs font-normal text-slate-400 dark:text-slate-500">Unit</span>
                    </h3>
                </div>
                <div class="p-3.5 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 rounded-2xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>

            {{-- CARD 2: Total Investasi --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Nilai Investasi</p>
                    <h3 id="total-asset-value" class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2.5 tracking-tight">
                        Rp {{ number_format($totalInvestmentValue ?? 0, 0, ',', '.') }}
                    </h3>
                </div>
                <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            {{-- CARD 3: Total Lokasi --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Sebaran Area</p>
                    <h3 id="total-location-count" class="text-3xl font-black text-slate-900 dark:text-white mt-2">
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

            {{-- CARD 4: Aset Nilai Buku Habis --}}
            <a href="/admin/assets?depreciated=1" class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200/60 dark:border-slate-800 shadow-sm flex items-center justify-between transition-all duration-300 hover:border-red-300 dark:hover:border-red-900/60 hover:shadow-md group">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest group-hover:text-red-500 dark:group-hover:text-red-400 transition-colors">Buku Habis / Rp 0</p>
                    <h3 id="total-depreciated-count" class="text-3xl font-black text-red-600 dark:text-red-500 mt-2">
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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

            {{-- Bar Chart Departemen --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-200/60 dark:border-slate-800 shadow-sm lg:col-span-2 transition-colors duration-300">
                <div class="mb-4">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">Kekuatan Finansial Per Departemen</h4>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Akumulasi nominal nilai investasi aset (Accurate Data).</p>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="chartDepartemen"
                        data-labels="{{ json_encode($chartDeptData['labels'] ?? []) }}"
                        data-values="{{ json_encode($chartDeptData['values'] ?? []) }}">
                    </canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js Library --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let chartLokasiInstance = null;
    let chartDepartemenInstance = null;

    const canvasLokasi = document.getElementById('chartLokasi');
    const canvasDept = document.getElementById('chartDepartemen');

    const initialChartLokasiLabels = canvasLokasi ? JSON.parse(canvasLokasi.getAttribute('data-labels') || '[]') : [];
    const initialChartLokasiValues = canvasLokasi ? JSON.parse(canvasLokasi.getAttribute('data-values') || '[]').map(Number) : [];

    const initialChartDeptLabels = canvasDept ? JSON.parse(canvasDept.getAttribute('data-labels') || '[]') : [];
    const initialChartDeptValues = canvasDept ? JSON.parse(canvasDept.getAttribute('data-values') || '[]').map(Number) : [];

    function getChartTextColor() {
        return document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b';
    }

    function getChartBorderColor() {
        return document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff';
    }

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
                const ctxLokasi = canvasLokasi.getContext('2d');
                chartLokasiInstance = new Chart(ctxLokasi, {
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
                                labels: {
                                    boxWidth: 8,
                                    usePointStyle: true,
                                    font: {
                                        size: 10,
                                        weight: '600'
                                    },
                                    color: textColor,
                                    padding: 15
                                }
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
                const ctxDept = canvasDept.getContext('2d');
                chartDepartemenInstance = new Chart(ctxDept, {
                    type: 'bar',
                    data: {
                        labels: deptLabels,
                        datasets: [{
                            label: 'Investasi',
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
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: textColor,
                                    font: {
                                        size: 10
                                    }
                                }
                            },
                            y: {
                                grid: {
                                    color: document.documentElement.classList.contains('dark') ? '#33415533' : '#f1f5f9'
                                },
                                beginAtZero: true,
                                ticks: {
                                    color: textColor,
                                    font: {
                                        size: 10
                                    },
                                    callback: function(value) {
                                        if (value >= 1e9) return 'Rp ' + (value / 1e9).toFixed(1) + 'B';
                                        if (value >= 1e6) return 'Rp ' + (value / 1e6).toFixed(0) + 'M';
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    }

    function refreshDashboardData() {
        const lokasi = document.getElementById('filter-lokasi').value;
        const status = document.getElementById('filter-status').value;

        const params = new URLSearchParams();
        if (lokasi) params.append('lokasi', lokasi);
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
                const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num);

                // Update KPI Cards
                document.getElementById('total-asset-count').innerHTML = `${formatNumber(data.kpi.total_count)} <span class="text-xs font-normal text-slate-400">Unit</span>`;
                document.getElementById('total-asset-value').innerText = data.kpi.total_value;
                document.getElementById('total-location-count').innerHTML = `${formatNumber(data.kpi.total_location)} <span class="text-xs font-normal text-slate-400">Lokasi</span>`;

                if (data.kpi.total_book_value_habis !== undefined) {
                    document.getElementById('total-depreciated-count').innerHTML = `${formatNumber(data.kpi.total_book_value_habis)} <span class="text-xs font-normal text-slate-400">Asset</span>`;
                }

                // Render & Update data grafik
                initOrUpdateCharts(
                    data.chart_lokasi.labels,
                    data.chart_lokasi.values,
                    data.chart_departemen.labels,
                    data.chart_departemen.values.map(Number)
                );
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

        const toggleBtn = document.getElementById('dark-mode-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                setTimeout(() => {
                    if (chartLokasiInstance || chartDepartemenInstance) {
                        initOrUpdateCharts(
                            chartLokasiInstance ? chartLokasiInstance.data.labels : [],
                            chartLokasiInstance ? chartLokasiInstance.data.datasets[0].data : [],
                            chartDepartemenInstance ? chartDepartemenInstance.data.labels : [],
                            chartDepartemenInstance ? chartDepartemenInstance.data.datasets[0].data : []
                        );
                    }
                }, 50);
            });
        }
    });
</script>
@endsection