<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">📝 Input Gaji Manual</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-6">
                    <h4 class="font-bold text-yellow-700 mb-1">⚠️ Input Manual</h4>
                    <p class="text-sm text-yellow-600">Data gaji diinput sesuai format slip gaji TVRI.</p>
                </div>

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('staff.keuangan.salaries.store.manual') }}" method="POST" id="salaryForm">
                    @csrf
                    
                    {{-- Info Karyawan & Periode --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Karyawan *</label>
                            <select name="user_id" id="user_select" required class="border rounded w-full py-2 px-3 text-gray-700 @error('user_id') border-red-500 @enderror">
                                <option value="">-- Pilih --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" 
                                            data-gaji="{{ $user->gaji_pokok ?? 0 }}"
                                            {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} @if($user->nip)({{ $user->nip }})@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Bulan *</label>
                            <select name="month" required class="border rounded w-full py-2 px-3 text-gray-700">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ old('month', $currentMonth) == $m ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tahun *</label>
                            <select name="year" required class="border rounded w-full py-2 px-3 text-gray-700">
                                @for($y = 2024; $y <= 2030; $y++)
                                    <option value="{{ $y }}" {{ old('year', $currentYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- PENERIMAAN --}}
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded">
                        <h4 class="font-bold text-green-700 mb-4">💰 PENERIMAAN</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Gaji Pokok (Rp) *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                                    <input type="text" id="base_salary_display" value="{{ old('base_salary', 0) }}"
                                           class="border rounded w-full py-2 pl-10 pr-3 text-gray-700 font-mono rupiah-input"
                                           placeholder="0" data-target="base_salary">
                                    <input type="hidden" name="base_salary" id="base_salary" value="{{ old('base_salary', 0) }}">
                                </div>
                                @error('base_salary')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Potongan KPPN (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                                    <input type="text" id="potongan_kppn_display" value="{{ old('potongan_kppn', 0) }}"
                                           class="border rounded w-full py-2 pl-10 pr-3 text-gray-700 font-mono rupiah-input"
                                           placeholder="0" data-target="potongan_kppn">
                                    <input type="hidden" name="potongan_kppn" id="potongan_kppn" value="{{ old('potongan_kppn', 0) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- POTONGAN INTERN --}}
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded">
                        <h4 class="font-bold text-red-700 mb-4">📋 POTONGAN INTERN</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Simpanan Wajib (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                                    <input type="text" id="simpanan_wajib_display" value="{{ old('simpanan_wajib', 0) }}"
                                           class="border rounded w-full py-2 pl-10 pr-3 text-gray-700 font-mono rupiah-input potongan-input"
                                           placeholder="0" data-target="simpanan_wajib">
                                    <input type="hidden" name="simpanan_wajib" id="simpanan_wajib" value="{{ old('simpanan_wajib', 0) }}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Kredit Uang (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                                    <input type="text" id="kredit_uang_display" value="{{ old('kredit_uang', 0) }}"
                                           class="border rounded w-full py-2 pl-10 pr-3 text-gray-700 font-mono rupiah-input potongan-input"
                                           placeholder="0" data-target="kredit_uang">
                                    <input type="hidden" name="kredit_uang" id="kredit_uang" value="{{ old('kredit_uang', 0) }}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Kredit Toko (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                                    <input type="text" id="kredit_toko_display" value="{{ old('kredit_toko', 0) }}"
                                           class="border rounded w-full py-2 pl-10 pr-3 text-gray-700 font-mono rupiah-input potongan-input"
                                           placeholder="0" data-target="kredit_toko">
                                    <input type="hidden" name="kredit_toko" id="kredit_toko" value="{{ old('kredit_toko', 0) }}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Dharma Wanita (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                                    <input type="text" id="dharma_wanita_display" value="{{ old('dharma_wanita', 0) }}"
                                           class="border rounded w-full py-2 pl-10 pr-3 text-gray-700 font-mono rupiah-input potongan-input"
                                           placeholder="0" data-target="dharma_wanita">
                                    <input type="hidden" name="dharma_wanita" id="dharma_wanita" value="{{ old('dharma_wanita', 0) }}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">BPJS (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                                    <input type="text" id="bpjs_display" value="{{ old('bpjs', 0) }}"
                                           class="border rounded w-full py-2 pl-10 pr-3 text-gray-700 font-mono rupiah-input potongan-input"
                                           placeholder="0" data-target="bpjs">
                                    <input type="hidden" name="bpjs" id="bpjs" value="{{ old('bpjs', 0) }}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Jumlah Potongan Intern</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-red-500 font-bold">Rp</span>
                                    <input type="text" id="total_potongan_intern_display" value="0"
                                           class="border rounded w-full py-2 pl-10 pr-3 text-gray-700 font-mono bg-gray-100 font-bold text-red-600"
                                           readonly>
                                    <input type="hidden" name="total_potongan_intern" id="total_potongan_intern" value="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TOTAL --}}
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded">
                        <h4 class="font-bold text-blue-700 mb-4">💵 GAJI DITERIMA</h4>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Jumlah Gaji Diterima (Rp) *</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3 text-green-600 font-bold text-lg">Rp</span>
                                <input type="text" id="final_salary_display" value="{{ old('final_salary', 0) }}"
                                       class="border-2 border-blue-300 rounded w-full py-3 pl-12 pr-4 text-gray-700 font-mono font-bold text-xl text-green-600 rupiah-input"
                                       placeholder="0" data-target="final_salary">
                                <input type="hidden" name="final_salary" id="final_salary" value="{{ old('final_salary', 0) }}">
                            </div>
                            @error('final_salary')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Catatan --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes" rows="2"
                                  class="border rounded w-full py-2 px-3 text-gray-700"
                                  placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex gap-3 pt-4 border-t">
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded">
                            💾 Simpan Data Gaji
                        </button>
                        <a href="{{ route('staff.keuangan.salaries') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Format number to Rupiah
        function formatRupiah(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Parse Rupiah string to number
        function parseRupiah(str) {
            return parseInt(str.replace(/\./g, '')) || 0;
        }

        // Calculate and update final salary
        function calculateFinalSalary() {
            const gajiPokok = parseInt(document.getElementById('base_salary').value) || 0;
            const potonganKppn = parseInt(document.getElementById('potongan_kppn').value) || 0;
            const totalPotonganIntern = parseInt(document.getElementById('total_potongan_intern').value) || 0;
            
            const finalSalary = gajiPokok - potonganKppn - totalPotonganIntern;
            
            document.getElementById('final_salary').value = finalSalary;
            document.getElementById('final_salary_display').value = formatRupiah(Math.max(0, finalSalary));
        }

        // Calculate total potongan intern
        function calculateTotalPotongan() {
            const fields = ['simpanan_wajib', 'kredit_uang', 'kredit_toko', 'dharma_wanita', 'bpjs'];
            let total = 0;
            
            fields.forEach(fieldId => {
                const hiddenInput = document.getElementById(fieldId);
                total += parseInt(hiddenInput.value) || 0;
            });
            
            document.getElementById('total_potongan_intern').value = total;
            document.getElementById('total_potongan_intern_display').value = formatRupiah(total);
            
            // Recalculate final salary
            calculateFinalSalary();
        }

        // Initialize all rupiah inputs
        document.querySelectorAll('.rupiah-input').forEach(input => {
            // Format initial value
            const targetId = input.dataset.target;
            const hiddenInput = document.getElementById(targetId);
            if (hiddenInput && hiddenInput.value) {
                input.value = formatRupiah(hiddenInput.value);
            }

            // Format on input
            input.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, '');
                const numValue = parseInt(value) || 0;
                
                // Update hidden input
                if (targetId) {
                    document.getElementById(targetId).value = numValue;
                }
                
                // Format display
                this.value = formatRupiah(numValue);

                // Recalculate totals
                if (this.classList.contains('potongan-input')) {
                    calculateTotalPotongan();
                } else if (targetId === 'base_salary' || targetId === 'potongan_kppn') {
                    calculateFinalSalary();
                }
            });
        });

        // Auto-fill gaji pokok dari data user
        document.getElementById('user_select').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const gaji = parseInt(selected.dataset.gaji) || 0;
            document.getElementById('base_salary').value = gaji;
            document.getElementById('base_salary_display').value = formatRupiah(gaji);
            calculateFinalSalary();
        });

        // Initialize totals on page load
        calculateTotalPotongan();
        calculateFinalSalary();
    </script>
</x-app-layout>
