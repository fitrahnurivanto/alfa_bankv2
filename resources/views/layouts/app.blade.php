<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Alfa Bank - Sistem Management Kelas & Pelatihan">
    <title>@yield('title', 'Alfa Bank - Management Kelas')</title>
    
    <!-- Google Fonts - Instrument Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Responsive Fix CSS -->
    <link rel="stylesheet" href="{{ asset('css/responsive-fix.css') }}">
    
    <!-- Tailwind CSS CDN (Play CDN - Not for Production) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @stack('styles')
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Responsive scaling untuk semua ukuran laptop */
        @media (min-width: 2560px) {
            html {
                font-size: 18px;
            }
            .container-fluid {
                max-width: 2560px;
                margin: 0 auto;
            }
        }
        
        @media (min-width: 1920px) and (max-width: 2559px) {
            html {
                font-size: 16px;
            }
            .container-fluid {
                max-width: 1920px;
                margin: 0 auto;
            }
        }
        
        /* Fix untuk layar Thinkpad dan layar besar lainnya (1600px-1920px) */
        @media (min-width: 1600px) and (max-width: 1919px) {
            html {
                font-size: 15px;
            }
            .lg\:ml-64 {
                margin-left: 256px;
            }
        }
        
        /* Standard laptop (1366px-1599px) */
        @media (min-width: 1366px) and (max-width: 1599px) {
            html {
                font-size: 14px;
            }
        }
        
        /* Small laptop (1280px-1365px) */
        @media (min-width: 1280px) and (max-width: 1365px) {
            html {
                font-size: 13.5px;
            }
        }
        
        /* Compact laptop (1024px-1279px) */
        @media (min-width: 1024px) and (max-width: 1279px) {
            html {
                font-size: 13px;
            }
        }
        
        /* Responsive Table Wrapper */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0;
            width: 100%;
        }
        
        .table-responsive table {
            width: 100%;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .table-responsive table {
                min-width: 600px;
            }
            html {
                font-size: 14px;
            }
        }
        
        /* Prevent horizontal scroll */
        body {
            overflow-x: hidden;
            width: 100%;
        }
        
        /* Main content max width */
        .lg\:ml-64 {
            max-width: 100vw;
        }
        
        /* Responsive grid adjustments */
        .grid-responsive {
            display: grid;
            gap: 1rem;
        }
        
        @media (min-width: 640px) {
            .grid-responsive {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1024px) {
            .grid-responsive {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (min-width: 1536px) {
            .grid-responsive {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        /* Smooth zoom adjustment */
        * {
            box-sizing: border-box;
        }
        
        /* Card responsive padding */
        @media (min-width: 1024px) and (max-width: 1279px) {
            .card-padding {
                padding: 0.75rem !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    @include('components.sidebar')
    
    <!-- Main Content - Responsive -->
    <div class="lg:ml-64 min-h-screen max-w-full overflow-x-hidden">
        <!-- Header - Responsive -->
        <div class="sticky top-0 bg-white px-4 sm:px-6 lg:px-6 xl:px-8 py-3 sm:py-3.5 md:py-4 shadow-sm z-10 flex justify-between items-center flex-wrap gap-2">
            <!-- Page Title - Responsive -->
            <div class="ml-12 lg:ml-0 flex-1 min-w-0">
                <h5 class="text-base sm:text-lg lg:text-xl font-semibold text-gray-800 truncate">@yield('page-title', 'Dashboard')</h5>
            </div>
            <!-- User Info - Responsive -->
            <div class="flex items-center gap-2 sm:gap-4">
                <!-- Notification Bell -->
                <div x-data="notificationSystem" class="relative">
                    <button @click="toggleNotifications" class="relative text-gray-600 hover:text-gray-900 transition">
                        <i class="fas fa-bell text-xl"></i>
                        <span x-show="unreadCount > 0" x-text="unreadCount" 
                              class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center animate-pulse"></span>
                    </button>
                    
                    <!-- Notification Dropdown - Responsive -->
                    <div x-show="showNotifications" x-cloak @click.away="showNotifications = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-72 sm:w-80 lg:w-96 max-w-[calc(100vw-2rem)] bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="font-semibold text-gray-900">Notifications</h3>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <template x-if="notifications.length === 0">
                                <div class="p-6 text-center text-gray-500">
                                    <i class="fas fa-bell-slash text-3xl mb-2"></i>
                                    <p class="text-sm">No notifications</p>
                                </div>
                            </template>
                            <template x-for="notif in notifications" :key="notif.id">
                                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
                                     :class="{'bg-blue-50': !notif.read}"
                                     @click="markAsRead(notif.id)">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                                             :class="notif.type === 'warning' ? 'bg-orange-100 text-orange-600' : 
                                                     notif.type === 'success' ? 'bg-green-100 text-green-600' : 
                                                     notif.type === 'danger' ? 'bg-red-100 text-red-600' : 
                                                     'bg-blue-100 text-blue-600'">
                                            <i :class="notif.icon" class="text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-gray-900" x-text="notif.title"></p>
                                            <p class="text-xs text-gray-600" x-text="notif.message"></p>
                                            <p class="text-xs text-gray-400 mt-1" x-text="notif.time"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                
                <!-- User Name - Hidden on mobile -->
                <span class="hidden md:flex text-gray-600 items-center">
                    <i class="fas fa-user-circle mr-2"></i>
                    <span class="truncate max-w-[150px]">{{ \Illuminate\Support\Facades\Auth::user()->name }}</span>
                </span>
                <!-- Role Badge -->
                <span class="px-2 sm:px-3 py-1 bg-indigo-600 text-white text-xs sm:text-sm font-medium rounded-lg">{{ ucfirst(\Illuminate\Support\Facades\Auth::user()->role) }}</span>
            </div>
        </div>
        
        <!-- Main Content Area - Responsive Padding -->
        <div class="p-3 sm:p-4 md:p-5 lg:p-6 xl:p-8 max-w-full overflow-x-hidden">
            @yield('content')
        </div>
    </div>
    
    @stack('scripts')
    
    <!-- Global Alpine.js Components -->
    <script>
        // Notification System
        document.addEventListener('alpine:init', () => {
            Alpine.data('notificationSystem', () => ({
                notifications: [],
                showNotifications: false,
                unreadCount: 0,
                
                async init() {
                    // Fetch notifications from server
                    await this.fetchNotifications();
                    
                    // Poll for new notifications every 30 seconds
                    setInterval(() => {
                        this.fetchNotifications();
                    }, 30000);
                    
                    // Listen for custom notification events
                    window.addEventListener('notify', (event) => {
                        this.fetchNotifications(); // Refresh from server
                    });
                },
                
                async fetchNotifications() {
                    try {
                        const response = await fetch('{{ route('notifications.index') }}', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.notifications = data.notifications;
                            this.unreadCount = data.unread_count;
                        }
                    } catch (error) {
                        console.error('Failed to fetch notifications:', error);
                    }
                },
                
                toggleNotifications() {
                    this.showNotifications = !this.showNotifications;
                },
                
                async markAsRead(id, actionUrl = null) {
                    try {
                        const response = await fetch(`{{ url('/notifications') }}/${id}/read`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.unreadCount = data.unread_count;
                            
                            // Update local notification
                            const notif = this.notifications.find(n => n.id === id);
                            if (notif) {
                                notif.read = true;
                            }
                            
                            // Redirect if action_url exists
                            if (actionUrl && actionUrl !== '#') {
                                window.location.href = actionUrl;
                            }
                        }
                    } catch (error) {
                        console.error('Failed to mark notification as read:', error);
                    }
                },
                
                async markAllAsRead() {
                    try {
                        const response = await fetch('{{ route('notifications.mark-all-as-read') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (response.ok) {
                            await this.fetchNotifications();
                        }
                    } catch (error) {
                        console.error('Failed to mark all as read:', error);
                    }
                },
                
                showToast(notification) {
                    const toastContainer = document.getElementById('toast-container');
                    const toast = document.createElement('div');
                    toast.className = `mb-3 p-4 rounded-lg shadow-lg transform transition-all duration-300 ${
                        notification.type === 'payment_request' ? 'bg-green-500' :
                        notification.type === 'project' ? 'bg-blue-500' :
                        notification.type === 'order' ? 'bg-purple-500' :
                        'bg-indigo-500'
                    } text-white`;
                    toast.innerHTML = `
                        <div class="flex items-start gap-3">
                            <i class="fas fa-${notification.icon} text-lg mt-1"></i>
                            <div class="flex-1">
                                <p class="font-semibold">${notification.title}</p>
                                <p class="text-sm opacity-90">${notification.message}</p>
                            </div>
                            <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    toastContainer.appendChild(toast);
                    
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        setTimeout(() => toast.remove(), 300);
                    }, 5000);
                }
            }));
        });
        
        // Global Loading State for Forms
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.hasAttribute('data-no-loading')) {
                        submitBtn.disabled = true;
                        const originalHTML = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
                        submitBtn.setAttribute('data-original-html', originalHTML);
                        
                        // Reset after 10 seconds as fallback
                        setTimeout(() => {
                            if (submitBtn.disabled) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalHTML;
                            }
                        }, 10000);
                    }
                });
            });
        });
    </script>
    
    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 w-96"></div>
    
    <!-- Global Image Preview Modal -->
    <div id="globalImageModal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-[60]" onclick="closeImageModal()">
        <div class="relative max-w-6xl max-h-[95vh] w-full p-4" onclick="event.stopPropagation()">
            <!-- Close Button -->
            <button onclick="closeImageModal()" 
                    class="absolute top-6 right-6 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-3 z-10 transition-all hover:scale-110"
                    aria-label="Tutup preview">
                <i class="fas fa-times text-xl"></i>
            </button>
            
            <!-- Download Button -->
            <a id="globalImageDownload" 
               href="" 
               download 
               target="_blank"
               class="absolute top-6 right-24 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-3 z-10 transition-all hover:scale-110"
               aria-label="Download gambar">
                <i class="fas fa-download text-xl"></i>
            </a>
            
            <!-- Open in New Tab Button -->
            <a id="globalImageNewTab" 
               href="" 
               target="_blank"
               class="absolute top-6 right-[140px] text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-3 z-10 transition-all hover:scale-110"
               aria-label="Buka di tab baru">
                <i class="fas fa-external-link-alt text-xl"></i>
            </a>
            
            <!-- Image Title -->
            <div class="absolute top-6 left-6 bg-black bg-opacity-50 text-white px-4 py-2 rounded-lg z-10">
                <p class="font-semibold text-sm" id="globalImageTitle">Preview</p>
            </div>
            
            <!-- Loading Spinner -->
            <div id="globalImageLoading" class="absolute inset-0 flex items-center justify-center">
                <div class="text-white">
                    <i class="fas fa-spinner fa-spin text-4xl"></i>
                </div>
            </div>
            
            <!-- Image Container -->
            <div class="bg-white rounded-lg overflow-hidden shadow-2xl max-h-[90vh] flex items-center justify-center">
                <img id="globalImagePreview" 
                     src="" 
                     alt="Preview" 
                     class="hidden w-full h-full object-contain"
                     style="max-height: 90vh;"
                     onload="document.getElementById('globalImageLoading').style.display='none'; this.classList.remove('hidden');"
                     onerror="document.getElementById('globalImageLoading').innerHTML='<p class=\'text-red-500\'><i class=\'fas fa-exclamation-circle mr-2\'></i>Gagal memuat gambar</p>';">
            </div>
        </div>
    </div>

    <script>
    // Global Image Modal Functions
    function openImageModal(imageUrl, title = 'Preview') {
        const modal = document.getElementById('globalImageModal');
        const image = document.getElementById('globalImagePreview');
        const titleEl = document.getElementById('globalImageTitle');
        const downloadBtn = document.getElementById('globalImageDownload');
        const newTabBtn = document.getElementById('globalImageNewTab');
        const loading = document.getElementById('globalImageLoading');
        
        // Reset state
        loading.style.display = 'flex';
        image.classList.add('hidden');
        image.src = '';
        
        // Set new values
        titleEl.textContent = title;
        downloadBtn.href = imageUrl;
        newTabBtn.href = imageUrl;
        
        // Show modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Load image
        setTimeout(() => {
            image.src = imageUrl;
        }, 100);
    }

    function closeImageModal() {
        const modal = document.getElementById('globalImageModal');
        const image = document.getElementById('globalImagePreview');
        
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        // Clear image after animation
        setTimeout(() => {
            image.src = '';
        }, 300);
    }

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });

    // Global Rupiah Input Formatter
    (function() {
        function sanitizeDigits(value) {
            return (value || '').toString().replace(/[^\d]/g, '');
        }

        function formatDigitsToRupiah(value) {
            const digits = sanitizeDigits(value);
            if (!digits) {
                return '';
            }

            return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function resolveHiddenInput(displayInput) {
            let hiddenName = displayInput.dataset.rupiahTarget || '';

            if (!hiddenName) {
                const displayName = displayInput.getAttribute('name') || '';
                if (displayName.endsWith('_display')) {
                    hiddenName = displayName.slice(0, -8);
                }
            }

            if (!hiddenName) {
                return null;
            }

            const form = displayInput.closest('form') || document;
            let hiddenInput = form.querySelector(`input[type="hidden"][name="${hiddenName}"]`);

            if (!hiddenInput) {
                hiddenInput = document.getElementById(hiddenName);
            }

            if (!hiddenInput) {
                return null;
            }

            return hiddenInput;
        }

        function syncRupiahInput(displayInput) {
            const hiddenInput = resolveHiddenInput(displayInput);
            if (!hiddenInput) {
                return;
            }

            const rawDigits = sanitizeDigits(displayInput.value);
            displayInput.value = formatDigitsToRupiah(rawDigits);
            hiddenInput.value = rawDigits;
        }

        function bindRupiahInput(displayInput) {
            if (displayInput.dataset.rupiahBound === '1') {
                return;
            }

            displayInput.dataset.rupiahBound = '1';
            syncRupiahInput(displayInput);

            ['input', 'change', 'paste', 'blur'].forEach((eventName) => {
                displayInput.addEventListener(eventName, () => syncRupiahInput(displayInput));
            });
        }

        function initRupiahInputs(root = document) {
            const selector = 'input[type="text"][name$="_display"], input[data-rupiah-display="true"]';
            root.querySelectorAll(selector).forEach((input) => bindRupiahInput(input));
        }

        document.addEventListener('DOMContentLoaded', function() {
            initRupiahInputs();
        });

        window.initRupiahInputs = initRupiahInputs;
        window.globalFormatRupiah = formatDigitsToRupiah;
        window.globalUnformatRupiah = sanitizeDigits;
    })();
    </script>
</body>
</html>
