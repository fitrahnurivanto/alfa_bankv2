<div id="corporate-fields-wrapper" class="md:col-span-2 hidden rounded-lg border border-indigo-200 bg-indigo-50 p-4">
    <div class="mb-4 flex items-center gap-2">
        <i class="fas fa-file-signature text-indigo-600"></i>
        <h3 class="font-semibold text-gray-900">Detail Penawaran Corporate</h3>
        <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">Corporate</span>
    </div>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="letter_date" class="mb-2 block text-sm font-medium text-gray-900">Tanggal Surat</label>
            <input type="date" name="letter_date" id="letter_date"
                   value="{{ old('letter_date', isset($clas) && $clas->letter_date ? $clas->letter_date->format('Y-m-d') : '') }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2">
            @error('letter_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="letter_number" class="mb-2 block text-sm font-medium text-gray-900">Nomor Surat</label>
            <input type="text" name="letter_number" id="letter_number"
                   value="{{ old('letter_number', $clas->letter_number ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2" placeholder="Contoh: 001/PP/2026">
            @error('letter_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="institution_name" class="mb-2 block text-sm font-medium text-gray-900">Instansi / Perusahaan Tujuan</label>
            <input type="text" name="institution_name" id="institution_name"
                   value="{{ old('institution_name', $clas->institution_name ?? ($clas->instansi ?? '')) }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2" placeholder="Contoh: PT ABC Indonesia">
            @error('institution_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="institution_address" class="mb-2 block text-sm font-medium text-gray-900">Alamat Instansi</label>
            <textarea name="institution_address" id="institution_address" rows="2"
                      class="w-full rounded-lg border border-gray-300 px-4 py-2"
                      placeholder="Alamat lengkap instansi">{{ old('institution_address', $clas->institution_address ?? ($clas->alamat ?? '')) }}</textarea>
            @error('institution_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="pic_name" class="mb-2 block text-sm font-medium text-gray-900">Nama PIC</label>
            <input type="text" name="pic_name" id="pic_name"
                   value="{{ old('pic_name', $clas->pic_name ?? ($clas->no_pic ?? '')) }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2" placeholder="Nama penanggung jawab">
            @error('pic_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="pic_phone" class="mb-2 block text-sm font-medium text-gray-900">No. HP PIC</label>
            <input type="text" name="pic_phone" id="pic_phone"
                   value="{{ old('pic_phone', $clas->pic_phone ?? ($clas->no_kontak ?? '')) }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2" placeholder="08xxxxxxxxxx">
            @error('pic_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="offered_scheme" class="mb-2 block text-sm font-medium text-gray-900">Skema Pelatihan / Sertifikasi</label>
            <input type="text" name="offered_scheme" id="offered_scheme"
                   value="{{ old('offered_scheme', $clas->offered_scheme ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2" placeholder="Contoh: Leadership Training 3 Hari">
            @error('offered_scheme')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="corporate_status" class="mb-2 block text-sm font-medium text-gray-900">Status Penawaran</label>
            <select name="corporate_status" id="corporate_status" class="w-full rounded-lg border border-gray-300 px-4 py-2">
                <option value="submitted" {{ old('corporate_status', $clas->corporate_status ?? 'submitted') === 'submitted' ? 'selected' : '' }}>Menunggu Review</option>
                <option value="under_review" {{ old('corporate_status', $clas->corporate_status ?? '') === 'under_review' ? 'selected' : '' }}>Sedang Direview</option>
                <option value="deal" {{ old('corporate_status', $clas->corporate_status ?? '') === 'deal' ? 'selected' : '' }}>Deal</option>
                <option value="cancelled" {{ old('corporate_status', $clas->corporate_status ?? '') === 'cancelled' ? 'selected' : '' }}>Batal</option>
                <option value="running" {{ old('corporate_status', $clas->corporate_status ?? '') === 'running' ? 'selected' : '' }}>Project Berjalan</option>
                <option value="completed" {{ old('corporate_status', $clas->corporate_status ?? '') === 'completed' ? 'selected' : '' }}>Project Selesai</option>
            </select>
            @error('corporate_status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="training_date" class="mb-2 block text-sm font-medium text-gray-900">Tanggal Pelatihan</label>
            <input type="date" name="training_date" id="training_date"
                   value="{{ old('training_date', isset($clas) && $clas->training_date ? $clas->training_date->format('Y-m-d') : '') }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2">
        </div>
        <div>
            <label for="training_date_end" class="mb-2 block text-sm font-medium text-gray-900">Tanggal Pelatihan Selesai</label>
            <input type="date" name="training_date_end" id="training_date_end"
                   value="{{ old('training_date_end', isset($clas) && $clas->training_date_end ? $clas->training_date_end->format('Y-m-d') : '') }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2">
        </div>
        <div>
            <label for="certification_date_start" class="mb-2 block text-sm font-medium text-gray-900">Tanggal Sertifikasi</label>
            <input type="date" name="certification_date_start" id="certification_date_start"
                   value="{{ old('certification_date_start', isset($clas) && $clas->certification_date_start ? $clas->certification_date_start->format('Y-m-d') : '') }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2">
        </div>
        <div>
            <label for="certification_date_end" class="mb-2 block text-sm font-medium text-gray-900">Tanggal Sertifikasi Selesai</label>
            <input type="date" name="certification_date_end" id="certification_date_end"
                   value="{{ old('certification_date_end', isset($clas) && $clas->certification_date_end ? $clas->certification_date_end->format('Y-m-d') : '') }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2">
        </div>
        <div>
            <label for="training_scheme" class="mb-2 block text-sm font-medium text-gray-900">Skema / Materi Pelatihan</label>
            <input type="text" name="training_scheme" id="training_scheme"
                   value="{{ old('training_scheme', $clas->training_scheme ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2">
        </div>
        <div>
            <label for="assessor_name" class="mb-2 block text-sm font-medium text-gray-900">Asesor</label>
            <input type="text" name="assessor_name" id="assessor_name"
                   value="{{ old('assessor_name', $clas->assessor_name ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2">
        </div>
        <div>
            <label for="corporate_training_price" class="mb-2 block text-sm font-medium text-gray-900">Nilai Pelatihan</label>
            <input type="number" min="0" step="0.01" name="corporate_training_price" id="corporate_training_price"
                   value="{{ old('corporate_training_price', $clas->corporate_training_price ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2" placeholder="0">
        </div>
        <div>
            <label for="corporate_certification_price" class="mb-2 block text-sm font-medium text-gray-900">Nilai Sertifikasi</label>
            <input type="number" min="0" step="0.01" name="corporate_certification_price" id="corporate_certification_price"
                   value="{{ old('corporate_certification_price', $clas->corporate_certification_price ?? '') }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2" placeholder="0">
        </div>
        <div>
            <label for="payment_start_date" class="mb-2 block text-sm font-medium text-gray-900">Rencana Tanggal Pembayaran</label>
            <input type="date" name="payment_start_date" id="payment_start_date"
                   value="{{ old('payment_start_date', isset($clas) && $clas->payment_start_date ? $clas->payment_start_date->format('Y-m-d') : '') }}"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2">
        </div>
    </div>
</div>
