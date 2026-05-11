@extends('layouts.app')

@section('page-title', 'Edit Expense Kelas')

@section('content')
<div class="p-1 md:p-2">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.classes.show', $expense->clas_id) }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Expense</h1>
            <p class="text-gray-600">Perbarui data expense kelas</p>
        </div>
    </div>

    <form action="{{ route('admin.class-expenses.update', $expense) }}" method="POST" class="max-w-2xl">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-6">
            <!-- Kelas Info -->
            <div class="pb-6 border-b">
                <h3 class="font-semibold text-gray-900 mb-3">Informasi Kelas</h3>
                <div class="text-sm space-y-2">
                    <p><span class="text-gray-600">Kelas:</span> <span class="font-medium">{{ $expense->clas->name }}</span></p>
                    <p><span class="text-gray-600">Status:</span> <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $expense->approval_status === 'pending' ? 'bg-yellow-100 text-yellow-700' : ($expense->approval_status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">{{ ucfirst($expense->approval_status) }}</span></p>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-file-alt text-orange-600 mr-1"></i>Deskripsi Expense
                </label>
                <input 
                    type="text" 
                    id="description" 
                    name="description" 
                    value="{{ old('description', $expense->description) }}"
                    placeholder="Contoh: Konsumsi peserta, transport, dll"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('description') border-red-500 @enderror"
                    required>
                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category -->
            <div>
                <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-tag text-orange-600 mr-1"></i>Kategori Expense
                </label>
                <select 
                    id="category" 
                    name="category"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('category') border-red-500 @enderror"
                    required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($allowedCategories as $value => $label)
                        <option value="{{ $value }}" {{ old('category', $expense->category) === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('category')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    @php
                        $isHonor = in_array($expense->category, ['trainer_honor', 'honor']);
                        echo $isHonor ? 'Honor trainer - akan menunggu validasi Finance' : 'Biaya operasional - langsung disetujui';
                    @endphp
                </p>
            </div>

            <!-- Amount -->
            <div>
                <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-money-bill-wave text-orange-600 mr-1"></i>Jumlah (Rp)
                </label>
                <input 
                    type="number" 
                    id="amount" 
                    name="amount" 
                    value="{{ old('amount', $expense->amount) }}"
                    placeholder="0"
                    min="0"
                    step="500"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('amount') border-red-500 @enderror"
                    required>
                @error('amount')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Expense Date -->
            <div>
                <label for="expense_date" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar text-orange-600 mr-1"></i>Tanggal Expense
                </label>
                <input 
                    type="date" 
                    id="expense_date" 
                    name="expense_date" 
                    value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('expense_date') border-red-500 @enderror"
                    required>
                @error('expense_date')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Summary -->
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3">Ringkasan Perubahan</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Deskripsi Lama:</span>
                        <span class="font-medium">{{ $expense->description }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Jumlah Lama:</span>
                        <span class="font-medium">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-4 border-t">
                <a href="{{ route('admin.classes.show', $expense->clas_id) }}" class="flex-1 px-4 py-2 text-center bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 transition font-medium">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
                <button type="submit" class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition font-medium">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
