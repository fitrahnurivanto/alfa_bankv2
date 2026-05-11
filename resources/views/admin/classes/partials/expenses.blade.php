<!-- Add Expense Modal -->
<div id="addExpenseModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white p-6 rounded-t-xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus-circle text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Tambah Expense Baru</h3>
                        <p class="text-sm opacity-90">Catat biaya tambahan untuk kelas ini</p>
                    </div>
                </div>
                <button type="button" 
                        onclick="document.getElementById('addExpenseModal').classList.add('hidden')"
                        class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form action="{{ route('admin.classes.expenses.store', $class) }}" method="POST">
                @csrf
                <div class="space-y-5">
                    <!-- Description -->
                    <div>
                        <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-edit text-orange-600 mr-2"></i>
                            Deskripsi Expense <span class="text-red-500 ml-1">*</span>
                        </label>
                        <input type="text" name="description" required
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                               placeholder="Contoh: Transport trainer ke lokasi">
                    </div>

                    <!-- Amount & Date (2 columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Amount -->
                        <div>
                            <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-money-bill-wave text-orange-600 mr-2"></i>
                                Jumlah (Rp) <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                                <input type="text" name="amount_display" id="amount_display" required
                                       value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : '' }}"
                                       class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                                       placeholder="0">
                                <input type="hidden" name="amount" id="amount" value="{{ old('amount') }}">
                            </div>
                        </div>

                        <!-- Expense Date -->
                        <div>
                            <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-orange-600 mr-2"></i>
                                Tanggal Expense <span class="text-red-500 ml-1">*</span>
                            </label>
                            <input type="date" name="expense_date" required
                                   value="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                        </div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-tag text-orange-600 mr-2"></i>
                            Kategori
                        </label>
                        @php
                            $role = \Illuminate\Support\Facades\Auth::user()->role;
                        @endphp
                        <select name="category"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                            <option value="">-- Pilih Kategori --</option>
                            @if(in_array($role, ['admin', 'superadmin']))
                                <option value="honor">👨‍🏫 Honor</option>
                                <option value="transport">🚗 Transportasi</option>
                                <option value="meal">🍽️ Konsumsi</option>
                                <option value="accommodation">🏨 Akomodasi</option>
                                <option value="equipment">📚 Modul</option>
                                <option value="marketing">📣 Marketing</option>
                                <option value="venue_rent">🏢 Sewa Tempat</option>
                                <option value="electricity">💡 Listrik</option>
                                <option value="goodie_bag">🎁 Goodibag</option>
                                <option value="other">📦 Lainnya</option>
                            @elseif($role === 'marketing')
                                <option value="transport">🚗 Transportasi</option>
                                <option value="meal">🍽️ Konsumsi</option>
                                <option value="accommodation">🏨 Akomodasi</option>
                                <option value="equipment">📚 Modul</option>
                                <option value="marketing">📣 Marketing</option>
                                <option value="venue_rent">🏢 Sewa Tempat</option>
                                <option value="electricity">💡 Listrik</option>
                                <option value="goodie_bag">🎁 Goodibag</option>
                                <option value="other">📦 Lainnya</option>
                            @elseif($role === 'akademik')
                                <option value="honor">👨‍🏫 Honor</option>
                            @endif
                        </select>
                        @if($role === 'marketing')
                            <p class="text-xs text-orange-700 mt-2">Marketing hanya dapat menambahkan biaya operasional.</p>
                        @elseif($role === 'akademik')
                            <p class="text-xs text-purple-700 mt-2">Akademik hanya dapat menambahkan honor trainer untuk payment request.</p>
                        @endif
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-sticky-note text-orange-600 mr-2"></i>
                            Catatan (opsional)
                        </label>
                        <textarea name="notes" rows="3"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition resize-none"
                                  placeholder="Catatan tambahan..."></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        <button type="submit"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-orange-600 text-white rounded-lg font-semibold hover:bg-orange-700 transition shadow-lg hover:shadow-xl">
                            <i class="fas fa-check-circle"></i>
                            <span>Simpan Expense</span>
                        </button>
                        <button type="button"
                                onclick="document.getElementById('addExpenseModal').classList.add('hidden')"
                                class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
