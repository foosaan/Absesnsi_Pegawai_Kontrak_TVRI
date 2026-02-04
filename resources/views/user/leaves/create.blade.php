<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">📝 Ajukan Cuti</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form action="{{ route('user.leaves.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Jenis Cuti</label>
                        <select name="type" required class="border rounded w-full py-2 px-3 text-gray-700 @error('type') border-red-500 @enderror">
                            <option value="">-- Pilih Jenis Cuti --</option>
                            <option value="cuti_tahunan" {{ old('type') === 'cuti_tahunan' ? 'selected' : '' }}>Cuti Tahunan</option>
                            <option value="sakit" {{ old('type') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="izin" {{ old('type') === 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="lainnya" {{ old('type') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Mulai</label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}" required
                                   min="{{ date('Y-m-d') }}"
                                   class="border rounded w-full py-2 px-3 text-gray-700 @error('start_date') border-red-500 @enderror">
                            @error('start_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Selesai</label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}" required
                                   min="{{ date('Y-m-d') }}"
                                   class="border rounded w-full py-2 px-3 text-gray-700 @error('end_date') border-red-500 @enderror">
                            @error('end_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Alasan</label>
                        <textarea name="reason" rows="4" required
                                  class="border rounded w-full py-2 px-3 text-gray-700 @error('reason') border-red-500 @enderror"
                                  placeholder="Jelaskan alasan pengajuan cuti...">{{ old('reason') }}</textarea>
                        @error('reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-6">
                        <h4 class="font-bold text-blue-700 mb-2">ℹ️ Informasi:</h4>
                        <ul class="text-sm text-blue-600 space-y-1">
                            <li>• Pengajuan cuti akan diproses oleh Staff PSDM</li>
                            <li>• Anda dapat membatalkan pengajuan selama status masih "Menunggu"</li>
                            <li>• Pastikan tanggal yang dipilih tidak melewati hari ini</li>
                        </ul>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded">
                            Ajukan Cuti
                        </button>
                        <a href="{{ route('user.leaves') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
