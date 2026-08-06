@php
    $menuGroups = [
        'Utama' => [
            ['route' => 'admin.dashboard', 'icon' => 'layout-grid', 'label' => 'Dashboard', 'permission' => null],
        ],
        'Master Data' => [
            ['route' => 'admin.asset_locations.index', 'icon' => 'map-pin', 'label' => 'Lokasi Asset', 'permission' => 'asset-locations.view'],
            ['route' => 'admin.asset_departments.index', 'icon' => 'building-2', 'label' => 'Departemen', 'permission' => 'asset-departments.view'],
            ['route' => 'admin.asset_categories.index', 'icon' => 'tags', 'label' => 'Kategori Asset', 'permission' => 'asset-categories.view'],
        ],
        'Operasional Asset' => [
            ['route' => 'admin.assets.index', 'icon' => 'package', 'label' => 'Data Asset', 'permission' => 'asset.view'],
            ['route' => 'admin.asset_transfers.index', 'icon' => 'arrow-left-right', 'label' => 'Mutasi & Transfer', 'permission' => 'transfer.view'],
            ['route' => 'admin.asset_loans.index', 'icon' => 'hand-coins', 'label' => 'Peminjaman Asset', 'permission' => 'loan.view'],
        ],
        'Pemeliharaan & Log' => [
            ['route' => 'admin.asset_maintenances.index', 'icon' => 'wrench', 'label' => 'Perbaikan & Servis', 'permission' => 'maintenance.view'],
            // KOREKSI PERMISSION: logs.view
            ['route' => 'admin.asset_logs.index', 'icon' => 'history', 'label' => 'Log Aktivitas', 'permission' => 'logs.view'],
        ],
        'Laporan & Audit' => [
            // KOREKSI PERMISSION: reports.view
            ['route' => 'admin.asset_reports.index', 'icon' => 'file-bar-chart', 'label' => 'Laporan Asset', 'permission' => 'reports.view'],
            ['route' => 'admin.asset_audits.index', 'icon' => 'clipboard-check', 'label' => 'Stock Opname', 'permission' => 'audit.view'],
        ],
        'Sistem' => [
            ['route' => 'admin.accurate_tokens.index', 'icon' => 'key-round', 'label' => 'Token Accurate', 'permission' => 'accurate.manage'],
            ['route' => 'admin.users.index', 'icon' => 'users', 'label' => 'Manajemen User', 'permission' => 'user.view'],
            ['route' => 'admin.roles.permissions', 'icon' => 'shield-check', 'label' => 'Permission', 'permission' => 'role.view'],
            ['route' => 'admin.roles.index', 'icon' => 'shield', 'label' => 'Roles', 'permission' => 'role.view'],
        ]
    ];

    $activeGroup = 'Admin Panel';
    $activeLabel = View::hasSection('title') ? View::yieldContent('title') : 'Dashboard';

    // Helper closure untuk mengecek route aktif
    $isRouteActive = function($routeName) {
        if (!Route::has($routeName)) return false;
        if (request()->routeIs($routeName)) return true;
        
        if (str_ends_with($routeName, '.index')) {
            $prefix = substr($routeName, 0, -6);
            if (request()->routeIs($prefix . '.*') && !request()->routeIs('admin.roles.permissions*')) {
                return true;
            }
        }
        return false;
    };

    // Helper closure untuk mengecek permission user
    $canAccessMenu = function($permission) {
        if (empty($permission)) return true;
        if (!auth()->check()) return false;
        
        $user = auth()->user();
        
        if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
            return true;
        }

        return $user->can($permission);
    };

    // Tentukan activeGroup & activeLabel
    foreach ($menuGroups as $groupLabel => $menus) {
        foreach ($menus as $menu) {
            if ($canAccessMenu($menu['permission']) && $isRouteActive($menu['route'])) {
                $activeGroup = $groupLabel;
                $activeLabel = $menu['label'];
                break 2;
            }
        }
    }

    view()->share('activeGroup', $activeGroup);
    view()->share('activeLabel', $activeLabel);
@endphp

<!-- BACKDROP MOBILE -->
<div x-show="sidebarOpen" 
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false" 
     class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-md lg:hidden"
     x-cloak>
</div>

<!-- SIDEBAR MAIN -->
<aside :class="{
           'translate-x-0': sidebarOpen, 
           '-translate-x-full lg:translate-x-0': !sidebarOpen,
           'lg:w-64': !sidebarCollapsed,
           'lg:w-20': sidebarCollapsed
       }"
       class="fixed top-0 left-0 z-50 h-screen transition-all duration-300 ease-in-out flex flex-col bg-white/90 dark:bg-slate-900/90 backdrop-blur-2xl border-r border-slate-200/80 dark:border-slate-800/80 shadow-2xl lg:shadow-none">
    
    <!-- WATERMARK ACCENT -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-[0.02] dark:opacity-[0.03]">
        <span class="absolute -right-16 bottom-10 text-8xl font-black uppercase tracking-widest select-none origin-bottom-right -rotate-90 text-slate-900 dark:text-white">
            ASSET
        </span>
    </div>

    <!-- HEADER / LOGO -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-slate-200/70 dark:border-slate-800/70 bg-slate-50/50 dark:bg-slate-950/40">
        <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}" class="flex items-center gap-3 overflow-hidden">
            <div class="relative flex-shrink-0">
                <div class="absolute inset-0 bg-blue-500/40 blur-md rounded-xl"></div>
                <div class="relative w-9 h-9 bg-gradient-to-tr from-blue-600 via-indigo-600 to-indigo-500 rounded-xl flex items-center justify-center shadow-md shadow-blue-500/20">
                    <i data-lucide="box" class="w-5 h-5 text-white"></i>
                </div>
            </div>

            <div x-show="!sidebarCollapsed" x-transition class="flex flex-col whitespace-nowrap">
                <span class="text-base font-extrabold tracking-tight text-slate-800 dark:text-white leading-tight">
                    Asset<span class="text-blue-600 dark:text-blue-400">Ku</span>
                </span>
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">PT SLP System</span>
            </div>
        </a>

        <!-- Desktop Collapse Button -->
        <button @click="sidebarCollapsed = !sidebarCollapsed" 
                class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 dark:hover:text-slate-200 transition-colors"
                :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'">
            <i data-lucide="panel-left-close" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': sidebarCollapsed }"></i>
        </button>

        <!-- Mobile Close Button -->
        <button @click="sidebarOpen = false" 
                class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- NAVIGATION MENU -->
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-5 custom-scrollbar">
        @foreach($menuGroups as $groupLabel => $menus)
            @php
                // Filter hanya menu yang diizinkan untuk user aktif
                $visibleMenus = array_filter($menus, function($m) use ($canAccessMenu) {
                    return $canAccessMenu($m['permission']);
                });
            @endphp

            {{-- Hanya tampilkan kelompok menu jika ada minimal 1 menu yang diizinkan --}}
            @if(count($visibleMenus) > 0)
                <div class="space-y-1">
                    <!-- Group Header -->
                    <div x-show="!sidebarCollapsed" class="flex items-center px-3 mb-2 gap-2">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest whitespace-nowrap">
                            {{ $groupLabel }}
                        </span>
                        <div class="h-[1px] flex-1 bg-slate-200/80 dark:bg-slate-800/80"></div>
                    </div>

                    <!-- Divider Mini Mode -->
                    <div x-show="sidebarCollapsed" class="h-[1px] bg-slate-200/80 dark:bg-slate-800/80 my-2 mx-2"></div>

                    <!-- Menu Items -->
                    @foreach($visibleMenus as $menu)
                        @php
                            $isActive = $isRouteActive($menu['route']);
                            $hasRoute = Route::has($menu['route']);
                        @endphp

                        <div class="relative group/item">
                            <a href="{{ $hasRoute ? route($menu['route']) : '#' }}"
                               class="relative flex items-center h-10 px-3 rounded-xl transition-all duration-200 
                               {{ $isActive 
                                   ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-500/25 dark:shadow-blue-600/20' 
                                   : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' 
                               }}
                               {{ !$hasRoute ? 'opacity-50 cursor-not-allowed' : '' }}">

                                <!-- Icon -->
                                <div class="flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="{{ $menu['icon'] }}"
                                       class="w-4 h-4 transition-transform duration-200 group-hover/item:scale-110 
                                       {{ $isActive ? 'text-white' : 'text-slate-400 dark:text-slate-500 group-hover/item:text-slate-700 dark:group-hover/item:text-slate-200' }}"></i>
                                </div>

                                <!-- Text Label -->
                                <span x-show="!sidebarCollapsed" 
                                      x-transition
                                      class="ml-3 text-xs tracking-tight whitespace-nowrap">
                                    {{ $menu['label'] }}
                                </span>

                                <!-- Active Indicator Dot (Mini Mode) -->
                                @if($isActive)
                                    <div x-show="sidebarCollapsed" class="absolute right-2 w-1.5 h-1.5 bg-white rounded-full shadow-sm"></div>
                                @endif
                            </a>

                            <!-- Floating Tooltip (Mini Mode) -->
                            <div x-show="sidebarCollapsed" 
                                 class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold rounded-lg opacity-0 pointer-events-none group-hover/item:opacity-100 transition-opacity z-50 whitespace-nowrap shadow-xl">
                                {{ $menu['label'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>

    <!-- USER FOOTER -->
    <div class="p-3 border-t border-slate-200/70 dark:border-slate-800/70 bg-slate-50/50 dark:bg-slate-950/40">
        <div class="flex items-center justify-between gap-2">
            
            <!-- User Info -->
            <div x-show="!sidebarCollapsed" class="flex items-center gap-2.5 overflow-hidden">
                <div class="relative shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-500 to-indigo-500 text-white font-bold flex items-center justify-center text-xs shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full"></span>
                </div>
                <div class="flex flex-col overflow-hidden">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate leading-tight">
                        {{ auth()->user()->name ?? 'Administrator' }}
                    </span>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 truncate">
                        {{ auth()->user()->email ?? 'admin@system.com' }}
                    </span>
                </div>
            </div>

            <!-- Logout Form -->
            <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}" class="w-full lg:w-auto">
                @csrf
                <button type="submit"
                        :class="sidebarCollapsed ? 'w-full justify-center' : ''"
                        class="flex items-center justify-center p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 dark:hover:text-rose-400 rounded-lg transition-colors group relative"
                        title="Keluar Aplikasi">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
</aside>