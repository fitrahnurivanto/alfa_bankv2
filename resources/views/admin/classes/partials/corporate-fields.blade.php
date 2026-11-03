@push('styles')
<style>
    .cf-section {
        margin-top: 1rem;
        overflow: hidden;
        border: 1px solid #d9e0eb;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
    }
    .cf-section-header {
        display: flex;
        align-items: center;
        gap: .55rem;
        padding: .8rem 1.25rem;
        border-bottom: 1px solid #d9e0eb;
        background: #f3f5fc;
    }
    .cf-section-title {
        color: #455a64;
        font-size: .8rem;
        font-weight: 600;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .cf-section-body { padding: 1.25rem; }
    .cf-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
    .cf-span2 { grid-column: span 2 / span 2; }
    .cf-label { display: block; margin-bottom: .3rem; color: #455a64; font-size: .78rem; font-weight: 500; }
    .cf-input, .cf-select, .cf-textarea {
        box-sizing: border-box;
        width: 100%;
        border: 1px solid #d9e0eb;
        border-radius: 8px;
        padding: .55rem .8rem;
        color: #263238;
        font-size: .85rem;
        outline: none;
    }
    .cf-input:focus, .cf-select:focus, .cf-textarea:focus {
        border-color: #4e65ff;
        box-shadow: 0 0 0 3px rgba(78, 101, 255, .15);
    }
    .cf-help { margin-top: .25rem; color: #607d8b; font-size: .72rem; }
    .cf-badge { border-radius: 999px; background: #d6dbfb; padding: .15rem .5rem; color: #4e65ff; font-size: .65rem; font-weight: 600; }
    @media (max-width: 640px) {
        .cf-grid { grid-template-columns: 1fr; }
        .cf-span2 { grid-column: span 1; }
    }
</style>
@endpush

<div id="corporate-fields-wrapper" class="hidden md:col-span-2">
    <section class="cf-section" id="corporate-section">
        <div class="cf-section-header">
            <i class="fas fa-file-signature text-indigo-600"></i>
            <span class="cf-section-title">Step 1 - Detail Penawaran</span>
            <span class="cf-badge">Corporate</span>
        </div>
        <div class="cf-section-body">
            <div class="cf-grid">
                <div>
                    <label class="cf-label" for="letter_date">Tanggal Surat</label>
                    <input type="date" name="letter_date" id="letter_date"
                           value="{{ old('letter_date', isset($clas) && $clas->letter_date ? $clas->letter_date->format('Y-m-d') : '') }}"
                           class="cf-input">
                </div>
                <div>
                    <label class="cf-label" for="letter_number">Nomor Surat</label>
                    <input type="text" name="letter_number" id="letter_number"
                           value="{{ old('letter_number', $clas->letter_number ?? '') }}"
                           class="cf-input" placeholder="Contoh: 001/PP/2026">
                </div>
                <div class="cf-span2">
                    <label class="cf-label" for="institution_name">Instansi / Perusahaan Tujuan</label>
                    <input type="text" name="institution_name" id="institution_name"
                           value="{{ old('institution_name', $clas->institution_name ?? ($clas->instansi ?? '')) }}"
                           class="cf-input" placeholder="Contoh: PT ABC Indonesia">
                </div>
                <div class="cf-span2">
                    <label class="cf-label" for="institution_address">Alamat Instansi</label>
                    <textarea name="institution_address" id="institution_address" rows="2" class="cf-textarea"
                              placeholder="Alamat lengkap instansi">{{ old('institution_address', $clas->institution_address ?? ($clas->alamat ?? '')) }}</textarea>
                </div>
                <div>
                    <label class="cf-label" for="pic_name">Nama PIC</label>
                    <input type="text" name="pic_name" id="pic_name"
                           value="{{ old('pic_name', $clas->pic_name ?? ($clas->no_pic ?? '')) }}"
                           class="cf-input" placeholder="Nama penanggung jawab">
                </div>
                <div>
                    <label class="cf-label" for="pic_phone">No. HP PIC</label>
                    <input type="text" name="pic_phone" id="pic_phone"
                           value="{{ old('pic_phone', $clas->pic_phone ?? ($clas->no_kontak ?? '')) }}"
                           class="cf-input" placeholder="08xxxxxxxxxx">
                </div>
                <div class="cf-span2">
                    <label class="cf-label" for="offered_scheme">Skema Pelatihan / Sertifikasi</label>
                    <input type="text" name="offered_scheme" id="offered_scheme"
                           value="{{ old('offered_scheme', $clas->offered_scheme ?? '') }}"
                           class="cf-input" placeholder="Contoh: Leadership Training 3 Hari">
                </div>
                <div>
                    <label class="cf-label" for="corporate_training_price">Nilai Pelatihan</label>
                    <input type="number" min="0" step="0.01" name="corporate_training_price" id="corporate_training_price"
                           value="{{ old('corporate_training_price', $clas->corporate_training_price ?? '') }}"
                           class="cf-input" placeholder="0">
                </div>
                <div>
                    <label class="cf-label" for="corporate_certification_price">Nilai Sertifikasi</label>
                    <input type="number" min="0" step="0.01" name="corporate_certification_price" id="corporate_certification_price"
                           value="{{ old('corporate_certification_price', $clas->corporate_certification_price ?? '') }}"
                           class="cf-input" placeholder="0">
                </div>
            </div>
        </div>
    </section>

    @if(isset($clas) && request('corporate_step') === '2')
    <section class="cf-section" id="corporate-decision-section">
        <div class="cf-section-header">
            <i class="fas fa-gavel text-indigo-600"></i>
            <span class="cf-section-title">Step 2 - Review Penawaran / Deal</span>
            <span class="cf-badge">Corporate</span>
        </div>
        <div class="cf-section-body">
            <div class="cf-grid">
                @php
                    $corporateStatusValue = isset($clas)
                        ? ($clas->corporate_status ?: ($clas->corporateProposal?->status ?? 'submitted'))
                        : 'submitted';
                @endphp
                <div class="cf-span2">
                    <label class="cf-label" for="corporate_status">Status Penawaran</label>
                    <select name="corporate_status" id="corporate_status" class="cf-select">
                        <option value="submitted" {{ old('corporate_status', $corporateStatusValue) === 'submitted' ? 'selected' : '' }}>Menunggu Review</option>
                        <option value="under_review" {{ old('corporate_status', $corporateStatusValue) === 'under_review' ? 'selected' : '' }}>Sedang Direview</option>
                        <option value="deal" {{ old('corporate_status', $corporateStatusValue) === 'deal' ? 'selected' : '' }}>Deal</option>
                        <option value="cancelled" {{ old('corporate_status', $corporateStatusValue) === 'cancelled' ? 'selected' : '' }}>Batal</option>
                        <option value="running" {{ old('corporate_status', $corporateStatusValue) === 'running' ? 'selected' : '' }}>Project Berjalan</option>
                        <option value="completed" {{ old('corporate_status', $corporateStatusValue) === 'completed' ? 'selected' : '' }}>Project Selesai</option>
                    </select>
                </div>
                <div>
                    <label class="cf-label" for="training_date">Tanggal Pelatihan Mulai</label>
                    <input type="date" name="training_date" id="training_date"
                           value="{{ old('training_date', isset($clas) && $clas->training_date ? $clas->training_date->format('Y-m-d') : '') }}"
                           class="cf-input">
                </div>
                <div>
                    <label class="cf-label" for="training_date_end">Tanggal Pelatihan Selesai</label>
                    <input type="date" name="training_date_end" id="training_date_end"
                           value="{{ old('training_date_end', isset($clas) && $clas->training_date_end ? $clas->training_date_end->format('Y-m-d') : '') }}"
                           class="cf-input">
                </div>
                <div>
                    <label class="cf-label" for="certification_date_start">Tanggal Sertifikasi Mulai</label>
                    <input type="date" name="certification_date_start" id="certification_date_start"
                           value="{{ old('certification_date_start', isset($clas) && $clas->certification_date_start ? $clas->certification_date_start->format('Y-m-d') : '') }}"
                           class="cf-input">
                </div>
                <div>
                    <label class="cf-label" for="certification_date_end">Tanggal Sertifikasi Selesai</label>
                    <input type="date" name="certification_date_end" id="certification_date_end"
                           value="{{ old('certification_date_end', isset($clas) && $clas->certification_date_end ? $clas->certification_date_end->format('Y-m-d') : '') }}"
                           class="cf-input">
                </div>
                <div>
                    <label class="cf-label" for="training_scheme">Skema / Materi Pelatihan</label>
                    <input type="text" name="training_scheme" id="training_scheme"
                           value="{{ old('training_scheme', $clas->training_scheme ?? '') }}"
                           class="cf-input">
                </div>
                <div>
                    <label class="cf-label" for="assessor_name">Asesor</label>
                    <input type="text" name="assessor_name" id="assessor_name"
                           value="{{ old('assessor_name', $clas->assessor_name ?? '') }}"
                           class="cf-input">
                </div>
                <div class="cf-span2">
                    <label class="cf-label" for="corporate_trainer_ids">Trainer</label>
                    <select name="trainer_ids[]" id="corporate_trainer_ids" class="cf-select" multiple>
                        @php
                            $selectedCorporateTrainerIds = isset($clas)
                                ? $clas->trainers->pluck('id')->map(fn ($id) => (string) $id)->all()
                                : [];
                            $oldCorporateTrainerIds = old('trainer_ids', $selectedCorporateTrainerIds);
                        @endphp
                        @foreach($trainers as $trainer)
                            <option value="{{ $trainer->id }}"
                                {{ in_array((string) $trainer->id, array_map('strval', (array) $oldCorporateTrainerIds), true) ? 'selected' : '' }}>
                                {{ $trainer->name }}{{ $trainer->email ? ' - ' . $trainer->email : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="cf-help">Trainer ditentukan saat proposal masuk tahap Review / Deal.</p>
                    @error('trainer_ids')<p class="cf-help text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="cf-label" for="payment_start_date">Rencana Tanggal Pembayaran</label>
                    <input type="date" name="payment_start_date" id="payment_start_date"
                           value="{{ old('payment_start_date', isset($clas) && $clas->payment_start_date ? $clas->payment_start_date->format('Y-m-d') : '') }}"
                           class="cf-input">
                </div>
            </div>
            <p class="cf-help">Status Deal/Running dapat dilengkapi dengan skema, tanggal, dan rencana pembayaran. Konfirmasi uang masuk tetap dilakukan melalui Step 4 di detail kelas.</p>
        </div>
    </section>
    @endif
</div>
