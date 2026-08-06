@extends('layouts.admin')

@section('title', 'Detail Peminjaman - #' . ($assetLoan->loan_number ?? $assetLoan->id))

@section('content')
<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">

    <!-- Background Watermark SVG -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <div class="absolute inset-0 opacity-[0.15] dark:opacity-[0.08]"
            style="background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>

        <div class="text-slate-900 dark:text-white opacity-[0.03] dark:opacity-[0.025] transform -rotate-12 scale-100 sm:scale-125 md:scale-150 p-4 transition-transform duration-700">
            <svg class="w-72 h-72 sm:w-96 sm:h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
        </div>
    </div>

    <!-- Efek Soft Glow Background -->
    <div class="absolute top-0 right-1/4 w-72 sm:w-[500px] h-72 sm:h-[500px] bg-indigo-500/10 dark:bg-indigo-500/5 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <div class="relative z-10 max-w-7xl mx-auto space-y-6">

        {{-- Top Bar & Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-6 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 transition-all">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/50">
                        #{{ $assetLoan->loan_number ?? $assetLoan->id }}
                    </span>
                    <span class="text-xs text-slate-400 flex items-center gap-1">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        Dibuat: {{ $assetLoan->created_at ? $assetLoan->created_at->format('d M Y, H:i') : '-' }}
                    </span>
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ $assetLoan->title ?? 'Peminjaman ' . ($assetLoan->asset->name ?? 'Aset') }}
                </h1>
            </div>
            <a href="{{ route('admin.asset_loans.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-xl transition-all flex items-center gap-2 border border-slate-200 dark:border-slate-700/60 shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke Index
            </a>
        </div>

        {{-- Alert Success / Error --}}
        @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-xl text-xs font-semibold flex items-center gap-3 backdrop-blur-md">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        {{-- Action Card & Workflow Status --}}
        <div class="bg-slate-900/90 dark:bg-slate-900/95 backdrop-blur-xl text-white p-6 rounded-2xl shadow-xl border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex items-center gap-4 relative z-10">
                <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-md border border-white/10 text-indigo-400 shadow-inner">
                    <i data-lucide="repeat" class="w-7 h-7"></i>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase tracking-widest font-black block">Status Peminjaman Saat Ini</span>
                    <div class="flex items-center gap-2 mt-1">
                        @php
                        $statusBadges = [
                        'pending' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                        'approved' => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
                        'borrowed' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
                        'active' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
                        'returned' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                        'overdue' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                        'rejected' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                        'cancelled' => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                        ];
                        @endphp
                        <span class="px-3 py-1 rounded-lg text-xs font-extrabold uppercase tracking-wider border {{ $statusBadges[$assetLoan->status] ?? 'bg-slate-800 text-slate-300 border-slate-700' }}">
                            {{ str_replace('_', ' ', $assetLoan->status) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Dynamic Quick Action Buttons --}}
            <div class="flex items-center gap-3 w-full md:w-auto relative z-10">
                @if($assetLoan->status === 'pending')
                {{-- Button: Setujui / Serahkan Aset --}}
                <form method="POST" action="{{ route('admin.asset_loans.update', $assetLoan->id) }}" class="w-full md:w-auto">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="quick_update" value="1">
                    <input type="hidden" name="status" value="borrowed">
                    <input type="hidden" name="asset_location_id" value="{{ $assetLoan->asset_location_id }}">
                    <input type="hidden" name="asset_id" value="{{ $assetLoan->asset_id }}">
                    <input type="hidden" name="start_date" value="{{ $assetLoan->start_date }}">
                    <input type="hidden" name="expected_return_date" value="{{ $assetLoan->expected_return_date }}">
                    <input type="hidden" name="condition_before" value="{{ $assetLoan->condition_before ?? 'good' }}">

                    <button type="submit" onclick="return confirm('Setujui dan serahkan aset kepada peminjam?')" class="w-full md:w-auto px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2">
                        <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                        Serahkan Aset
                    </button>
                </form>
                @elseif(in_array($assetLoan->status, ['borrowed', 'active', 'approved', 'overdue']))
                {{-- Button: Buka Modal Pengembalian --}}
                <button type="button" onclick="openReturnModal()" class="w-full md:w-auto px-5 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    Proses Pengembalian
                </button>
                @elseif($assetLoan->status === 'returned')
                <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold uppercase tracking-wider bg-emerald-500/10 px-4 py-2.5 rounded-xl border border-emerald-500/20">
                    <i data-lucide="check-check" class="w-4 h-4"></i>
                    Aset Telah Dikembalikan
                </div>
                @endif

                @if(!in_array($assetLoan->status, ['returned', 'rejected', 'cancelled']))
                {{-- Button Option: Batalkan / Tolak --}}
                <form method="POST" action="{{ route('admin.asset_loans.update', $assetLoan->id) }}" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="quick_update" value="1">
                    <input type="hidden" name="status" value="rejected">
                    <input type="hidden" name="asset_location_id" value="{{ $assetLoan->asset_location_id }}">
                    <input type="hidden" name="asset_id" value="{{ $assetLoan->asset_id }}">
                    <input type="hidden" name="start_date" value="{{ $assetLoan->start_date }}">
                    <input type="hidden" name="expected_return_date" value="{{ $assetLoan->expected_return_date }}">
                    <input type="hidden" name="condition_before" value="{{ $assetLoan->condition_before ?? 'good' }}">

                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menolak / membatalkan peminjaman ini?')" class="px-4 py-3 bg-slate-800 hover:bg-rose-950/60 text-slate-300 hover:text-rose-300 text-xs font-bold uppercase tracking-wider rounded-xl transition border border-slate-700/80">
                        Batalkan / Tolak
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Grid Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left / Main Column --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Detail Informasi Peminjaman --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-6 space-y-6">
                    <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800/80 pb-3 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-indigo-500"></i>
                        Informasi Peminjaman
                    </h2>

                    <div>
                        <span class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1">Keperluan & Catatan Peminjaman</span>
                        <p class="text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/40 p-4 rounded-xl text-xs border border-slate-200/60 dark:border-slate-800 leading-relaxed whitespace-pre-line font-medium">
                            {{ $assetLoan->purpose ?? $assetLoan->notes ?? $assetLoan->description ?? 'Tidak ada catatan atau keperluan khusus yang dicantumkan.' }}
                        </p>
                    </div>

                    {{-- Section Note Pengembalian --}}
                    @if($assetLoan->status === 'returned' || $assetLoan->notes)
                    <div>
                        <span class="block text-[10px] font-black uppercase text-emerald-600 dark:text-emerald-400 tracking-wider mb-1 flex items-center gap-1.5">
                            <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i>
                            Keterangan / Catatan Pengembalian
                        </span>
                        <p class="text-slate-700 dark:text-slate-200 bg-emerald-500/5 dark:bg-emerald-500/10 p-4 rounded-xl text-xs border border-emerald-500/20 leading-relaxed whitespace-pre-line font-medium">
                            {{ $assetLoan->notes ?? 'Aset dikembalikan dalam keadaan lengkap dan baik tanpa catatan khusus.' }}
                        </p>
                    </div>
                    @endif

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-2">
                        <div class="p-3.5 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200/60 dark:border-slate-800">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Jumlah (Qty)</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-1 block">
                                {{ $assetLoan->quantity ?? 1 }} Unit
                            </span>
                        </div>

                        <div class="p-3.5 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200/60 dark:border-slate-800">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Tgl Pinjam</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-1 block">
                                {{ $assetLoan->loan_date ? \Carbon\Carbon::parse($assetLoan->loan_date)->format('d/m/Y') : ($assetLoan->start_date ? \Carbon\Carbon::parse($assetLoan->start_date)->format('d/m/Y') : '-') }}
                            </span>
                        </div>

                        <div class="p-3.5 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200/60 dark:border-slate-800">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Estimasi Kembali</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-1 block">
                                {{ $assetLoan->due_date ? \Carbon\Carbon::parse($assetLoan->due_date)->format('d/m/Y') : ($assetLoan->expected_return_date ? \Carbon\Carbon::parse($assetLoan->expected_return_date)->format('d/m/Y') : '-') }}
                            </span>
                        </div>

                        <div class="p-3.5 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200/60 dark:border-slate-800">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Tgl Dikembalikan</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-1 block">
                                {{ $assetLoan->actual_return_date ? \Carbon\Carbon::parse($assetLoan->actual_return_date)->format('d/m/Y H:i') : '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Peminjam / Penanggung Jawab --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-6">
                    <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800/80 pb-3 mb-4 flex items-center gap-2">
                        <i data-lucide="user-check" class="w-4 h-4 text-indigo-500"></i>
                        Identitas Peminjam
                    </h2>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-lg shadow-sm">
                            {{ strtoupper(substr($assetLoan->borrower->name ?? $assetLoan->user->name ?? $assetLoan->borrower_name ?? 'P', 0, 1)) }}
                        </div>
                        <div>
                            @if($assetLoan->borrower || $assetLoan->user)
                            <h4 class="font-bold text-slate-900 dark:text-white text-sm">
                                {{ $assetLoan->borrower->name ?? $assetLoan->user->name }}
                            </h4>
                            <p class="text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold mt-0.5">
                                {{ $assetLoan->borrower->department ?? $assetLoan->user->email ?? 'Pengguna Sistem' }}
                            </p>
                            @elseif($assetLoan->borrower_name)
                            <h4 class="font-bold text-slate-900 dark:text-white text-sm">{{ $assetLoan->borrower_name }}</h4>
                            <p class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold mt-0.5">Peminjam Eksternal / Manual</p>
                            @else
                            <p class="text-xs text-slate-400 italic">Belum ada peminjam yang tercatat.</p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column (Asset Info & Return Condition) --}}
            <div class="space-y-6">

                {{-- Detail Aset --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-6 space-y-4">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800/80 pb-3 flex items-center gap-2">
                        <i data-lucide="box" class="w-4 h-4 text-indigo-500"></i>
                        Aset Dipinjam
                    </h3>

                    <div class="space-y-3">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-black tracking-wider block">Nama Aset</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-100 mt-0.5 block">{{ $assetLoan->asset->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-black tracking-wider block">Kode Aset</span>
                            <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2.5 py-1 rounded-md border border-indigo-200/60 dark:border-indigo-800/50 inline-block mt-1">
                                {{ $assetLoan->asset->code ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Status Kondisi Aset --}}
                <!-- Status Kondisi & Lokasi Aset -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-6 space-y-4">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800/80 pb-3 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-indigo-500"></i>
                        Kondisi & Lokasi Aset
                    </h3>

                    <div class="space-y-3.5 text-xs">
                        @php
                        // Mapping warna dan label kondisi
                        $conditionMap = [
                        'good' => ['label' => 'Baik / Normal', 'class' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'],
                        'bagus' => ['label' => 'Baik / Normal', 'class' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'],
                        'slightly_damaged' => ['label' => 'Rusak Ringan', 'class' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20'],
                        'rusak_ringan' => ['label' => 'Rusak Ringan', 'class' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20'],
                        'severely_damaged' => ['label' => 'Rusak Berat', 'class' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20'],
                        'rusak_berat' => ['label' => 'Rusak Berat', 'class' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20'],
                        'lost' => ['label' => 'Hilang', 'class' => 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20'],
                        ];

                        $condBeforeKey = strtolower($assetLoan->condition_before ?? $assetLoan->initial_condition ?? 'good');
                        $condAfterKey = strtolower($assetLoan->condition_after ?? $assetLoan->return_condition ?? '');

                        // Normalisasi lokasi (mencegah error JSON string / array / object)
                        $rawLoc = $assetLoan->location;
                        $locObj = null;

                        if (is_string($rawLoc)) {
                        $decoded = json_decode($rawLoc);
                        if (json_last_error() === JSON_ERROR_NONE && is_object($decoded)) {
                        $locObj = $decoded;
                        }
                        } elseif (is_object($rawLoc)) {
                        $locObj = $rawLoc;
                        } elseif (is_array($rawLoc)) {
                        $locObj = (object) $rawLoc;
                        }

                        $locName = $locObj->name ?? (is_string($rawLoc) && !$locObj ? $rawLoc : ($assetLoan->asset->location->name ?? 'Internal'));
                        $locBuilding = $locObj->building ?? null;
                        $locFloor = $locObj->floor ?? null;
                        $locRoom = $locObj->room ?? null;
                        @endphp

                        <!-- Kondisi Awal -->
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Kondisi Awal</span>
                            <span class="px-2.5 py-1 rounded-md text-[11px] font-bold border {{ $conditionMap[$condBeforeKey]['class'] ?? 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700' }}">
                                {{ $conditionMap[$condBeforeKey]['label'] ?? ucfirst($condBeforeKey) }}
                            </span>
                        </div>

                        <!-- Kondisi Pengembalian -->
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Kondisi Pengembalian</span>
                            @if($condAfterKey && $condAfterKey !== '-')
                            <span class="px-2.5 py-1 rounded-md text-[11px] font-bold border {{ $conditionMap[$condAfterKey]['class'] ?? 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700' }}">
                                {{ $conditionMap[$condAfterKey]['label'] ?? ucfirst($condAfterKey) }}
                            </span>
                            @else
                            <span class="text-slate-400 dark:text-slate-500 italic font-medium px-2 py-0.5">-</span>
                            @endif
                        </div>

                        <!-- Detail Lokasi/Tujuan -->
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                            <span class="block text-[10px] font-black uppercase text-slate-400 tracking-wider">Lokasi / Tujuan</span>

                            <div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200/60 dark:border-slate-800/80 space-y-1.5">
                                <div class="font-bold text-slate-900 dark:text-slate-100 text-xs flex items-center gap-2">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-indigo-500 shrink-0"></i>
                                    <span>{{ $locName }}</span>
                                </div>

                                @if($locBuilding || $locFloor || $locRoom)
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 pl-6 text-[11px] text-slate-500 dark:text-slate-400 font-medium border-t border-slate-200/40 dark:border-slate-700/40 pt-2 mt-1">
                                    @if($locBuilding)
                                    <span>Gedung: <strong class="text-slate-700 dark:text-slate-300">{{ $locBuilding }}</strong></span>
                                    @endif
                                    @if($locFloor)
                                    <span>Lantai: <strong class="text-slate-700 dark:text-slate-300">{{ $locFloor }}</strong></span>
                                    @endif
                                    @if($locRoom)
                                    <span>Ruang: <strong class="text-slate-700 dark:text-slate-300">{{ $locRoom }}</strong></span>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

{{-- MODAL PROSES PENGEMBALIAN --}}
<div id="returnModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5 transform transition-all scale-95 opacity-0 duration-200" id="returnModalContent">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
            <div class="flex items-center gap-2.5">
                <div class="p-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </div>
                <h3 class="font-bold text-base text-slate-900 dark:text-white">Proses Pengembalian Aset</h3>
            </div>
            <button type="button" onclick="closeReturnModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.asset_loans.update', $assetLoan->id) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="quick_update" value="1">
            <input type="hidden" name="status" value="returned">
            <input type="hidden" name="asset_location_id" value="{{ $assetLoan->asset_location_id }}">
            <input type="hidden" name="asset_id" value="{{ $assetLoan->asset_id }}">
            <input type="hidden" name="start_date" value="{{ $assetLoan->start_date }}">
            <input type="hidden" name="expected_return_date" value="{{ $assetLoan->expected_return_date }}">
            <input type="hidden" name="condition_before" value="{{ $assetLoan->condition_before ?? 'good' }}">

            {{-- Kondisi Setelah Dikembalikan --}}
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1.5">
                    Kondisi Aset Saat Dikembalikan <span class="text-rose-500">*</span>
                </label>
                <select name="condition_after" required class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 p-3 focus:ring-2 focus:ring-indigo-500 font-medium">
                    <option value="good">Baik / Normal</option>
                    <option value="slightly_damaged">Rusak Ringan</option>
                    <option value="severely_damaged">Rusak Berat</option>
                    <option value="lost">Hilang</option>
                </select>
            </div>

            {{-- Catatan / Keterangan Pengembalian --}}
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1.5">
                    Catatan / Keterangan Pengembalian
                </label>
                <textarea name="notes" rows="3" placeholder="Contoh: Aset telah dikembalikan dalam keadaan bersih dan lengkap beserta aksesorisnya." class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 p-3 focus:ring-2 focus:ring-indigo-500 font-medium placeholder:text-slate-400"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeReturnModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition shadow-lg shadow-emerald-600/30 flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Pengembalian
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReturnModal() {
        const modal = document.getElementById('returnModal');
        const content = document.getElementById('returnModalContent');
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeReturnModal() {
        const modal = document.getElementById('returnModal');
        const content = document.getElementById('returnModalContent');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }
</script>
@endsection