@extends('layouts.app')

@section('content')
<div class="p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Pengaturan</h1>
            <p class="text-gray-600">Kelola logo dan informasi perusahaan</p>
        </div>

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Tabs (simplified - only company info now) -->
        <div class="mb-6">
            <!-- No tabs needed anymore, direct to content -->
        </div>

        @if(\Illuminate\Support\Facades\Auth::user()->isAdmin())
        <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Manajemen Pengguna</h2>
                    <p class="text-sm text-gray-600 mt-1">CRUD akun pengguna dikelola melalui menu Pengaturan.</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 transition font-medium">
                    <i class="fas fa-users"></i>
                    <span>Buka Manajemen Pengguna</span>
                </a>
            </div>
        </div>
        @endif

        <!-- Content: Info Perusahaan -->
        <div>
        <!-- Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="p-6 space-y-6">
                    <!-- Logo Perusahaan -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-image text-indigo-600 mr-2"></i>Logo Perusahaan (Sidebar)
                        </label>
                        
                        @if($settings['company_logo'])
                        <div class="mb-3">
                            <img src="@storageUrl($settings['company_logo'])?v={{ time() }}" alt="Company Logo" class="h-20 border border-gray-200 rounded p-2 bg-white">
                            <p class="text-xs text-gray-500 mt-1">Logo saat ini</p>
                        </div>
                        @endif
                        
                        <input type="file" name="company_logo" accept="image/png,image/jpeg,image/jpg"
                            class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                            Format: PNG, JPG, JPEG. Max 2MB. <strong>Rekomendasi:</strong> Logo horizontal 400x120px atau lebih besar dengan rasio 3:1 hingga 4:1. Logo akan otomatis di-resize menjadi max 400x120px dengan menjaga proporsi.
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 rounded-b-xl border-t border-gray-200 flex justify-end">
                    <button type="submit" id="submitBtn" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-2 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition font-semibold">
                        <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-1">Informasi:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Logo perusahaan akan ditampilkan di sidebar aplikasi</li>
                        <li>Pastikan gambar berformat PNG transparan untuk hasil terbaik</li>
                        <li>Rekomendasi ukuran: 400x400px (square)</li>
                    </ul>
                </div>
            </div>
        </div>
        </div>
        <!-- End Content: Info Perusahaan -->

    </div>
</div>

<script>
// Image preview on file select
document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const previewContainer = this.parentElement.querySelector('div.mb-3');
                
                if (previewContainer) {
                    const previewImg = previewContainer.querySelector('img');
                    
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        previewImg.src = event.target.result;
                        previewImg.classList.add('ring-2', 'ring-green-500');
                        
                        const text = previewContainer.querySelector('p');
                        if (text) {
                            text.textContent = 'Preview: ' + file.name + ' (belum disimpan)';
                            text.classList.add('text-green-600', 'font-semibold');
                        }
                    };
                    reader.readAsDataURL(file);
                } else {
                    const newPreview = document.createElement('div');
                    newPreview.className = 'mb-3';
                    newPreview.innerHTML = `
                        <img src="" alt="Preview" class="h-20 border border-gray-200 rounded p-2 bg-white ring-2 ring-green-500">
                        <p class="text-xs text-green-600 font-semibold mt-1">Preview: ${file.name} (belum disimpan)</p>
                    `;
                    
                    this.parentElement.insertBefore(newPreview, this);
                    
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        newPreview.querySelector('img').src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    });

    // Handle form submission with loading indicator
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submitBtn');
    
    if (form && submitBtn) {
        console.log('Form found:', form.action);
        console.log('Submit button found');
        
        form.addEventListener('submit', function(e) {
            console.log('Form submitting...');
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            
            console.log('Button disabled, form will submit now');
        });
    } else {
        console.error('Form or submit button not found!');
    }
});
</script>
@endsection
