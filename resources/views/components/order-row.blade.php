@props(['order', 'user'])

@php
    $presenter = $order->presenter;
    $statusBadge = $presenter->getPaymentStatusBadge();
    $typeBadge = $presenter->getPaymentTypeBadge();
@endphp

<tr class="hover:bg-gray-50">
    <!-- Order Info -->
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm font-medium text-gray-900">{{ $order->order_number }}</div>
        <div class="text-xs text-gray-500">{{ $presenter->getOrderDate() }}</div>
    </td>

    <!-- Client Info - All details visible -->
    <td class="px-6 py-4">
        <div class="text-sm font-medium text-gray-900">{{ $presenter->getClientName() }}</div>
        <div class="text-xs text-gray-500">
            <i class="fab fa-whatsapp mr-1"></i>{{ $presenter->getClientPhoneDisplay() }}
        </div>
        <div class="text-xs text-gray-500 mt-1">
            <i class="fas fa-envelope mr-1"></i>{{ $presenter->getClientEmail() }}
        </div>
        @if($presenter->getCompanyName())
        <div class="text-xs text-gray-500 mt-1">
            <i class="fas fa-building mr-1"></i>{{ $presenter->getCompanyName() }}
        </div>
        @endif
    </td>

    <!-- Services - Show all details -->
    <td class="px-6 py-4">
        @if($presenter->isRegistration())
            @php $regBadge = $presenter->getRegistrationBadge(); @endphp
            <span class="px-3 py-1.5 text-sm font-semibold rounded-lg {{ $regBadge['class'] }}">
                <i class="fas {{ $regBadge['icon'] }} mr-1"></i>{{ $regBadge['text'] }}
            </span>
            
            <div class="mt-2 text-sm text-gray-700 space-y-1">
                @if($order->institution_name)
                <div class="text-xs">
                    <i class="fas fa-school text-gray-400 mr-1"></i>{{ $order->institution_name }}
                </div>
                @endif
                @if($order->participant_age)
                <div class="text-xs">
                    <i class="fas fa-birthday-cake text-gray-400 mr-1"></i>{{ $order->participant_age }} tahun
                </div>
                @endif
            </div>
        @else
            <div class="text-sm text-gray-900">
                <span class="font-medium">{{ $order->items->count() }} Layanan</span>
                @if($user->isSuperAdmin() && $order->items->first())
                    @php $division = $order->items->first()->service->category->division; @endphp
                    <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full {{ $division === 'agency' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800' }}">
                        <i class="fas {{ $division === 'agency' ? 'fa-briefcase' : 'fa-graduation-cap' }} mr-1"></i>{{ ucfirst($division) }}
                    </span>
                @endif
            </div>
            
            <div class="mt-2 space-y-2">
                @foreach($order->items as $item)
                <div class="text-sm">
                    <div class="flex items-start gap-2">
                        <div class="flex-1">
                            <span class="font-medium">{{ $item->service->name }}</span>
                            @if($item->servicePackage || $item->package_name)
                            <span class="text-xs text-gray-500 ml-1">
                                - Paket: {{ $item->servicePackage?->name ?? $item->package_name }}
                            </span>
                            @endif
                            <span class="text-gray-500 ml-1">({{ $item->quantity }}x)</span>
                        </div>
                    </div>
                </div>
                @endforeach
                
                @if($presenter->getTruncatedNotes())
                <div class="text-xs text-gray-500 italic">
                    <i class="fas fa-comment mr-1"></i>{{ $presenter->getTruncatedNotes() }}
                </div>
                @endif
            </div>
        @endif
    </td>

    <!-- Total Amount -->
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm font-bold text-gray-900">{{ $presenter->formatCurrency($order->total_amount) }}</div>
        @if($order->payment_type === 'installment')
        <div class="text-xs text-blue-600 mt-1">
            <i class="fas fa-wallet mr-1"></i>DP {{ $order->paid_installments }}/2
        </div>
        <div class="text-xs text-gray-500">
            Sisa: {{ $presenter->formatCurrency($order->remaining_amount) }}
        </div>
        @elseif($order->payment_type === 'not_yet_paid')
        <div class="text-xs text-orange-600 mt-1">
            <i class="fas fa-hourglass-half mr-1"></i>Belum Dibayar
        </div>
        @else
        <div class="text-xs text-green-600 mt-1">
            <i class="fas fa-check-circle mr-1"></i>Lunas
        </div>
        @endif
    </td>

    <!-- Payment Method & Proof -->
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900">{{ $order->payment_method }}</div>
        @if($order->payment_proof)
        @php
            $paymentProofUrl = strpos($order->payment_proof, 'http') === 0 
                ? $order->payment_proof 
                : \Storage::url($order->payment_proof);
        @endphp
        <button onclick="openImageModal('{{ $paymentProofUrl }}', 'Bukti Pembayaran - {{ $order->order_number }}')" 
                class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold transition">
            <i class="fas fa-file-image mr-1"></i>Lihat Bukti
        </button>
        @else
        <span class="text-xs text-gray-500">Belum upload</span>
        @endif
    </td>

    <!-- Status Badges -->
    <td class="px-6 py-4">
        <div class="space-y-2">
            <!-- Payment Type Badge -->
            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $typeBadge['class'] }}">
                <i class="fas {{ $typeBadge['icon'] }} mr-1"></i>{{ $typeBadge['text'] }}
            </span>
            
            <!-- Payment Status Badge -->
            <div>
                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusBadge['class'] }}">
                    <i class="fas {{ $statusBadge['icon'] }} mr-1"></i>{{ $statusBadge['text'] }}
                </span>
            </div>
        </div>
    </td>

    <!-- Actions -->
    <td class="px-6 py-4 whitespace-nowrap text-sm">
    </td>
</tr>
