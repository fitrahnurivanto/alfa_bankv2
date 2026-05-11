<!-- Sidebar with Mobile Responsive -->
<div x-data="{ sidebarOpen: false }" @keydown.escape="sidebarOpen = false">
    <!-- Mobile Menu Button -->
    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-lg bg-white shadow-lg border border-gray-200 hover:bg-gray-50 transition">
        <i class="fas fa-bars text-xl text-gray-700" x-show="!sidebarOpen"></i>
        <i class="fas fa-times text-xl text-gray-700" x-show="sidebarOpen" x-cloak></i>
    </button>

    <!-- Overlay for mobile -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"></div>

    <!-- Sidebar -->
    <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
         class="fixed left-0 top-0 bottom-0 w-64 bg-white text-gray-800 overflow-y-auto shadow-xl z-50 border-r border-gray-200 transition-transform duration-300 ease-in-out lg:translate-x-0">
    
    @php
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Get company logo
        $currentLogo = \App\Models\Setting::get('company_logo');
        $companyName = \App\Models\Setting::get('company_name', 'ALFA BANK');
        
        // Check if logo exists and is not empty
        $logoExists = !empty($currentLogo);
        if ($logoExists) {
            // Check if logo is full URL (Supabase) or local path
            if (strpos($currentLogo, 'supabase.co') !== false || strpos($currentLogo, 'http') === 0) {
                // Supabase URL - use directly
                $logoUrl = $currentLogo;
            } elseif (strpos($currentLogo, '/storage/') === 0) {
                // Local storage path
                $logoUrl = asset($currentLogo);
            } else {
                // Other local path - use Storage helper
                $logoUrl = \Storage::url($currentLogo);
            }
        }
    @endphp

    <!-- Logo -->
    <div class="px-4 lg:px-5 py-6 lg:py-8 border-b border-gray-200 flex items-center justify-center">
        @if($logoExists)
            <img src="{{ $logoUrl }}" 
                 alt="{{ $companyName }}" 
                 class="h-16 lg:h-20 w-auto max-w-full object-contain"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <div class="text-center" style="display:none;">
                <h1 class="text-2xl font-bold text-gray-800">{{ $companyName }}</h1>
                <p class="text-xs text-gray-500 mt-1">Management Kelas</p>
            </div>
        @else
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-800">{{ $companyName }}</h1>
                <p class="text-xs text-gray-500 mt-1">Management Kelas</p>
            </div>
        @endif
    </div>
    
    <ul class="py-4 lg:py-5 px-0 list-none">
        @if(\Illuminate\Support\Facades\Auth::user()->role === 'admin')
            <!-- Dashboard -->
            <li class="mx-2 lg:mx-2.5 my-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 lg:px-4 py-2.5 lg:py-3 text-sm lg:text-base text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-home w-5 lg:w-6 text-base lg:text-lg"></i>
                    <span class="ml-2 lg:ml-2.5">Dashboard</span>
                </a>
            </li>

            <!-- Kelas -->
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.classes.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.classes.index') || request()->routeIs('admin.classes.create') || request()->routeIs('admin.classes.edit') || request()->routeIs('admin.classes.show') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-chalkboard-teacher w-6 text-lg"></i>
                    <span class="ml-2.5">Kelas</span>
                </a>
            </li>
            
            <!-- Kelas Berjalan -->
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.classes.showclas') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-green-50 hover:text-green-600 hover:translate-x-1 {{ request()->routeIs('admin.classes.showclas') ? 'bg-green-50 text-green-600 font-semibold' : '' }}">
                    <i class="fas fa-play-circle w-6 text-lg"></i>
                    <span class="ml-2.5">Kelas Berjalan</span>
                    @php
                        $activeClassCount = \App\Models\Clas::where('status', 'approved')->count();
                    @endphp
                    @if($activeClassCount > 0)
                    <span class="ml-auto bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">{{ $activeClassCount }}</span>
                    @endif
                </a>
            </li>

            {{-- Peserta/Client - DISEMBUNYIKAN SEMENTARA --}}
            {{-- 
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.clients.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.clients.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-user-graduate w-6 text-lg"></i>
                    <span class="ml-2.5">Peserta</span>
                </a>
            </li>
            --}}

            <!-- Trainer/Pengajar -->
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.trainers.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.trainers.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-chalkboard-user w-6 text-lg"></i>
                    <span class="ml-2.5">Pengajar</span>
                </a>
            </li>

            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.trainer-attendance.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-green-50 hover:text-green-600 hover:translate-x-1 {{ request()->routeIs('admin.trainer-attendance.*') ? 'bg-green-50 text-green-600 font-semibold' : '' }}">
                    <i class="fas fa-user-check w-6 text-lg"></i>
                    <span class="ml-2.5">Absen Pengajar</span>
                </a>
            </li>

            <!-- Laporan -->
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.laporan.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.laporan.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-chart-line w-6 text-lg"></i>
                    <span class="ml-2.5">Laporan</span>
                </a>
            </li>

            <!-- Payment Requests -->
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.payment-requests.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.payment-requests.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-money-bill-wave w-6 text-lg"></i>
                    <span class="ml-2.5">Payment Requests</span>
                    @php
                        $pendingPayments = \App\Models\PaymentRequest::where('status', 'pending')->whereNotNull('class_id')->count();
                    @endphp
                    @if($pendingPayments > 0)
                    <span class="ml-auto bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-1 rounded-full">{{ $pendingPayments }}</span>
                    @endif
                </a>
            </li>
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->role === 'admin')
            <!-- Inventaris Barang -->
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.inventaris.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-purple-50 hover:text-purple-600 hover:translate-x-1 {{ request()->routeIs('admin.inventaris.*') ? 'bg-purple-50 text-purple-600 font-semibold' : '' }}">
                    <i class="fas fa-cube w-6 text-lg"></i>
                    <span class="ml-2.5">Inventaris Barang</span>
                </a>
            </li>
            
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 {{ request()->routeIs('admin.settings.*') || request()->routeIs('admin.positions.*') || request()->routeIs('admin.trainings.*') || request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <div class="flex items-center">
                        <i class="fas fa-cog w-6 text-lg"></i>
                        <span class="ml-2.5">Pengaturan</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>
                
                <!-- Submenu -->
                <div x-show="open" x-collapse class="mt-1 ml-4 space-y-1">
                    <a href="{{ route('admin.settings.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-600 no-underline rounded-lg transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.settings.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                        <i class="fas fa-building w-5 text-sm"></i>
                        <span class="ml-2">Info Perusahaan</span>
                    </a>
                    
                    <a href="{{ route('admin.trainings.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-600 no-underline rounded-lg transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.trainings.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                        <i class="fas fa-graduation-cap w-5 text-sm"></i>
                        <span class="ml-2">Nama Pelatihan</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-600 no-underline rounded-lg transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                        <i class="fas fa-users w-5 text-sm"></i>
                        <span class="ml-2">Manajemen Pengguna</span>
                    </a>
                </div>
            </li>

        @elseif(\Illuminate\Support\Facades\Auth::user()->role === 'marketing')
            <!-- Marketing Menu -->
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-home w-6 text-lg"></i>
                    <span class="ml-2.5">Dashboard</span>
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.classes.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.classes.index') || request()->routeIs('admin.classes.create') || request()->routeIs('admin.classes.show') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-chalkboard-teacher w-6 text-lg"></i>
                    <span class="ml-2.5">Kelas</span>
                    @php
                        $pendingKelas = \App\Models\Clas::where('status', 'pending')
                            ->where('user_id', \Illuminate\Support\Facades\Auth::id())->count();
                    @endphp
                    @if($pendingKelas > 0)
                    <span class="ml-auto bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-1 rounded-full">{{ $pendingKelas }}</span>
                    @endif
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.classes.showclas') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-green-50 hover:text-green-600 hover:translate-x-1 {{ request()->routeIs('admin.classes.showclas') ? 'bg-green-50 text-green-600 font-semibold' : '' }}">
                    <i class="fas fa-play-circle w-6 text-lg"></i>
                    <span class="ml-2.5">Kelas Berjalan</span>
                    @php
                        $activeClassCount = \App\Models\Clas::where('status', 'approved')->count();
                    @endphp
                    @if($activeClassCount > 0)
                    <span class="ml-auto bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">{{ $activeClassCount }}</span>
                    @endif
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.laporan.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.laporan.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-chart-line w-6 text-lg"></i>
                    <span class="ml-2.5">Laporan</span>
                </a>
            </li>
            <li class="mx-2.5 my-1 pt-2 border-t border-gray-200">
                <a href="{{ route('admin.trainings.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.trainings.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-graduation-cap w-6 text-lg"></i>
                    <span class="ml-2.5">Nama Pelatihan</span>
                </a>
            </li>

        @elseif(\Illuminate\Support\Facades\Auth::user()->role === 'akademik')
            <!-- Akademik Menu -->
            <li class="mx-2.5 my-1">
                <a href="{{ route('akademik.dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('akademik.dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-home w-6 text-lg"></i>
                    <span class="ml-2.5">Dashboard</span>
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.classes.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-green-50 hover:text-green-600 hover:translate-x-1 {{ request()->routeIs('admin.classes.*') ? 'bg-green-50 text-green-600 font-semibold' : '' }}">
                    <i class="fas fa-chalkboard-teacher w-6 text-lg"></i>
                    <span class="ml-2.5">Kelas</span>
                    @php
                        $pendingApproval = \App\Models\Clas::where('status', 'pending')->count();
                    @endphp
                    @if($pendingApproval > 0)
                    <span class="ml-auto bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-1 rounded-full">{{ $pendingApproval }}</span>
                    @endif
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.classes.showclas') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-green-50 hover:text-green-600 hover:translate-x-1 {{ request()->routeIs('admin.classes.showclas') ? 'bg-green-50 text-green-600 font-semibold' : '' }}">
                    <i class="fas fa-play-circle w-6 text-lg"></i>
                    <span class="ml-2.5">Kelas Berjalan</span>
                    @php
                        $activeClassCount = \App\Models\Clas::where('status', 'approved')->count();
                    @endphp
                    @if($activeClassCount > 0)
                    <span class="ml-auto bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">{{ $activeClassCount }}</span>
                    @endif
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.trainers.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.trainers.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-chalkboard-user w-6 text-lg"></i>
                    <span class="ml-2.5">Pengajar</span>
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.trainer-attendance.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-green-50 hover:text-green-600 hover:translate-x-1 {{ request()->routeIs('admin.trainer-attendance.*') ? 'bg-green-50 text-green-600 font-semibold' : '' }}">
                    <i class="fas fa-user-check w-6 text-lg"></i>
                    <span class="ml-2.5">Absen Pengajar</span>
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.laporan.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.laporan.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-chart-line w-6 text-lg"></i>
                    <span class="ml-2.5">Laporan</span>
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.payment-requests.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('admin.payment-requests.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-money-bill-wave w-6 text-lg"></i>
                    <span class="ml-2.5">Payment Request</span>
                    @php
                        $pendingPayments = \App\Models\PaymentRequest::where('status', 'pending')->whereNotNull('class_id')->count();
                    @endphp
                    @if($pendingPayments > 0)
                    <span class="ml-auto bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-1 rounded-full">{{ $pendingPayments }}</span>
                    @endif
                </a>
            </li>

        @elseif(\Illuminate\Support\Facades\Auth::user()->role === 'finance')
            <!-- Finance Menu -->
            <li class="mx-2.5 my-1">
                <a href="{{ route('finance.dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('finance.dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-home w-6 text-lg"></i>
                    <span class="ml-2.5">Dashboard</span>
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('finance.expenses.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('finance.expenses.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-receipt w-6 text-lg"></i>
                    <span class="ml-2.5">Expenses</span>
                    @php
                        $expenseNotifications = \App\Models\Notification::where('user_id', \Illuminate\Support\Facades\Auth::id())
                            ->whereNull('read_at')
                            ->where('type', 'expense_pending')
                            ->count();
                    @endphp
                    @if($expenseNotifications > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full animate-pulse">{{ $expenseNotifications }}</span>
                    @endif
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('finance.payment-requests.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('finance.payment-requests.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-money-bill-wave w-6 text-lg"></i>
                    <span class="ml-2.5">Payment Requests</span>
                    @php
                        $paymentNotifications = \App\Models\Notification::where('user_id', \Illuminate\Support\Facades\Auth::id())
                            ->whereNull('read_at')
                            ->where('type', 'payment_request_pending')
                            ->count();
                    @endphp
                    @if($paymentNotifications > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full animate-pulse">{{ $paymentNotifications }}</span>
                    @endif
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('admin.inventaris.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-purple-50 hover:text-purple-600 hover:translate-x-1 {{ request()->routeIs('admin.inventaris.*') ? 'bg-purple-50 text-purple-600 font-semibold' : '' }}">
                    <i class="fas fa-cube w-6 text-lg"></i>
                    <span class="ml-2.5">Inventaris Barang</span>
                </a>
            </li>
        @elseif(\Illuminate\Support\Facades\Auth::user()->role === 'trainer')
            <!-- Trainer Menu -->
            <li class="mx-2.5 my-1">
                <a href="{{ route('trainer.dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('trainer.dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-home w-6 text-lg"></i>
                    <span class="ml-2.5">Dashboard</span>
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('trainer.classes.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('trainer.classes.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-chalkboard-teacher w-6 text-lg"></i>
                    <span class="ml-2.5">Kelas Saya</span>
                    @php
                        $classNotifications = \App\Models\Notification::where('user_id', \Illuminate\Support\Facades\Auth::id())
                            ->whereNull('read_at')
                            ->whereIn('type', ['class_assignment', 'class_status', 'class_update'])
                            ->count();
                    @endphp
                    @if($classNotifications > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full animate-pulse">{{ $classNotifications }}</span>
                    @endif
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('trainer.attendance.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-green-50 hover:text-green-600 hover:translate-x-1 {{ request()->routeIs('trainer.attendance.*') ? 'bg-green-50 text-green-600 font-semibold' : '' }}">
                    <i class="fas fa-user-check w-6 text-lg"></i>
                    <span class="ml-2.5">Absensi Pengajar</span>
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('trainer.payment-requests.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('trainer.payment-requests.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-money-bill-wave w-6 text-lg"></i>
                    <span class="ml-2.5">Payment Requests</span>
                    @php
                        $paymentNotifications = \App\Models\Notification::where('user_id', \Illuminate\Support\Facades\Auth::id())
                            ->whereNull('read_at')
                            ->where('type', 'payment_request')
                            ->count();
                    @endphp
                    @if($paymentNotifications > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full animate-pulse">{{ $paymentNotifications }}</span>
                    @endif
                </a>
            </li>
        @elseif(\Illuminate\Support\Facades\Auth::user()->role === 'client')
            <!-- Client Menu -->
            <li class="mx-2.5 my-1">
                <a href="{{ route('client.dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('client.dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-home w-6 text-lg"></i>
                    <span class="ml-2.5">Dashboard</span>
                </a>
            </li>
            <li class="mx-2.5 my-1">
                <a href="{{ route('client.classes.index') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1 {{ request()->routeIs('client.classes.*') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-chalkboard-teacher w-6 text-lg"></i>
                    <span class="ml-2.5">Kelas Saya</span>
                    @php
                        $clientNotifications = \App\Models\Notification::where('user_id', \Illuminate\Support\Facades\Auth::id())
                            ->whereNull('read_at')
                            ->whereIn('type', ['class_enrollment', 'class_update'])
                            ->count();
                    @endphp
                    @if($clientNotifications > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full animate-pulse">{{ $clientNotifications }}</span>
                    @endif
                </a>
            </li>
        @else
            <!-- Default Menu (Legacy Support) -->
            <li class="mx-2.5 my-1">
                <a href="{{ route('client.dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 no-underline rounded-xl transition-all hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1">
                    <i class="fas fa-home w-6 text-lg"></i>
                    <span class="ml-2.5">Dashboard</span>
                </a>
            </li>
        @endif
        
        
        <li class="mx-2.5 my-1">
            <form action="{{ route('logout') }}" method="POST" id="logout-form">
                @csrf
                <button type="submit" class="w-full flex items-center px-4 py-3 text-gray-700 rounded-xl transition-all hover:bg-red-50 hover:text-red-600 hover:translate-x-1 bg-transparent border-0 text-left cursor-pointer">
                    <i class="fas fa-sign-out-alt w-6 text-lg"></i>
                    <span class="ml-2.5">Logout</span>
                </button>
            </form>
        </li>
    </ul>
</div>
</div>

<style>
/* Custom scrollbar untuk sidebar */
.fixed::-webkit-scrollbar {
    width: 6px;
}
.fixed::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.05);
}
.fixed::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.2);
    border-radius: 10px;
}
.fixed::-webkit-scrollbar-thumb:hover {
    background: rgba(0,0,0,0.3);
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Sidebar responsive adjustments */
@media (min-width: 1280px) {
    .lg\:ml-64 {
        margin-left: 256px;
    }
}

@media (min-width: 1536px) {
    .xl\:w-72 {
        width: 18rem;
    }
    .lg\:ml-64 {
        margin-left: 18rem;
    }
}
</style>