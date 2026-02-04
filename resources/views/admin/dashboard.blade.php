<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Sukses!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Attendance List -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Filter Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4">
                            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap gap-3 items-end">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal</label>
                                    <input type="date" name="filter_date" value="{{ request('filter_date') }}" 
                                           class="border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Karyawan</label>
                                    <select name="filter_user" class="border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                                        <option value="">Semua</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('filter_user') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium">
                                        🔍 Filter
                                    </button>
                                    <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm font-medium">
                                        Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Attendance Table -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-bold">📋 Riwayat Absensi</h3>
                                <span class="text-sm text-gray-500">Total: {{ $attendances->count() }} data</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Karyawan</th>
                                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Masuk</th>
                                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Pulang</th>
                                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Foto</th>
                                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @forelse($attendances as $attendance)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium text-gray-900">{{ $attendance->user->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $attendance->user->email }}</div>
                                                </td>
                                                <td class="px-3 py-3 text-center">
                                                    <div class="font-medium">{{ $attendance->check_in_time->format('d M Y') }}</div>
                                                    <div class="text-xs text-gray-400">{{ $attendance->check_in_time->translatedFormat('l') }}</div>
                                                </td>
                                                <td class="px-3 py-3 text-center">
                                                    <span class="inline-flex items-center px-2 py-1 rounded bg-green-50 text-green-700 font-medium">
                                                        🟢 {{ $attendance->check_in_time->format('H:i') }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3 text-center">
                                                    @if($attendance->check_out_time)
                                                        <span class="inline-flex items-center px-2 py-1 rounded bg-orange-50 text-orange-700 font-medium">
                                                            🔴 {{ $attendance->check_out_time->format('H:i') }}
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-3 text-center">
                                                    <div class="flex justify-center gap-1">
                                                        @if($attendance->photo_path && $attendance->photo_path !== 'manual/admin_input.png')
                                                            <a href="{{ asset('storage/' . $attendance->photo_path) }}" target="_blank" 
                                                               class="px-2 py-1 bg-blue-100 text-blue-600 rounded text-xs hover:bg-blue-200">In</a>
                                                        @else
                                                            <span class="px-2 py-1 bg-gray-100 text-gray-400 rounded text-xs">Manual</span>
                                                        @endif
                                                        @if($attendance->check_out_photo_path)
                                                            <a href="{{ asset('storage/' . $attendance->check_out_photo_path) }}" target="_blank" 
                                                               class="px-2 py-1 bg-orange-100 text-orange-600 rounded text-xs hover:bg-orange-200">Out</a>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3 text-center">
                                                    @if($attendance->status === 'late')
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                            ⚠️ Terlambat
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                            ✅ Hadir
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                                    <div class="text-4xl mb-2">📭</div>
                                                    <p>Belum ada data absensi.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                    <!-- Settings Sidebar -->
                <div class="space-y-6">
                    <!-- Location Settings -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-5 text-gray-900">
                            <h3 class="text-lg font-bold mb-4">📍 Lokasi Kantor</h3>
                            <form action="{{ route('admin.settings.update') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="block text-gray-700 text-xs font-bold mb-1">Latitude</label>
                                    <input type="text" name="office_latitude" value="{{ $settings['office_latitude'] ?? '' }}" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 text-sm">
                                </div>
                                <div class="mb-3">
                                    <label class="block text-gray-700 text-xs font-bold mb-1">Longitude</label>
                                    <input type="text" name="office_longitude" value="{{ $settings['office_longitude'] ?? '' }}" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 text-sm">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-xs font-bold mb-1">Radius (meter)</label>
                                    <input type="number" name="allowed_radius_meters" value="{{ $settings['allowed_radius_meters'] ?? '' }}" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 text-sm">
                                </div>
                                <button class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm" type="submit">
                                    Simpan Lokasi
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Shift Management -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-5 text-gray-900">
                            <h3 class="text-lg font-bold mb-4">⏰ Pengaturan Shift</h3>
                            
                            @foreach($shifts as $shift)
                            <div class="mb-4 p-3 rounded-lg {{ $shift->type === 'normal' ? 'bg-green-50 border border-green-200' : 'bg-purple-50 border border-purple-200' }}">
                                <form action="{{ route('admin.shift.update', $shift) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-bold text-sm {{ $shift->type === 'normal' ? 'text-green-700' : 'text-purple-700' }}">
                                            {{ $shift->type === 'normal' ? '👷' : '🛡️' }} {{ $shift->name }}
                                        </span>
                                        <span class="text-xs px-2 py-0.5 rounded {{ $shift->type === 'normal' ? 'bg-green-200 text-green-700' : 'bg-purple-200 text-purple-700' }}">
                                            {{ $shift->type === 'normal' ? 'OB' : 'Satpam' }}
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-2 mb-2">
                                        <div>
                                            <label class="block text-gray-600 text-xs mb-1">Jam Mulai</label>
                                            <input type="time" name="start_time" 
                                                   value="{{ $shift->start_time instanceof \Carbon\Carbon ? $shift->start_time->format('H:i') : substr($shift->start_time, 0, 5) }}" 
                                                   class="border rounded w-full py-1.5 px-2 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-gray-600 text-xs mb-1">Jam Selesai</label>
                                            <input type="time" name="end_time" 
                                                   value="{{ $shift->end_time instanceof \Carbon\Carbon ? $shift->end_time->format('H:i') : substr($shift->end_time, 0, 5) }}" 
                                                   class="border rounded w-full py-1.5 px-2 text-sm">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <label class="block text-gray-600 text-xs mb-1">Toleransi Terlambat (menit)</label>
                                        <input type="number" name="tolerance_minutes" value="{{ $shift->tolerance_minutes }}" 
                                               min="0" max="120"
                                               class="border rounded w-full py-1.5 px-2 text-sm">
                                    </div>
                                    
                                    <button class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-1.5 px-3 rounded text-xs" type="submit">
                                        Simpan
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Shift Change History -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-5 text-gray-900">
                            <h3 class="text-lg font-bold mb-3">📜 Riwayat Perubahan Shift</h3>
                            
                            @if($shiftLogs->count() > 0)
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @foreach($shiftLogs as $log)
                                <div class="p-2 bg-gray-50 rounded text-xs border-l-4 {{ $log->shift->type === 'normal' ? 'border-green-400' : 'border-purple-400' }}">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="font-bold text-gray-700">{{ $log->shift->name }}</span>
                                        <span class="text-gray-400">{{ $log->created_at->format('d/m H:i') }}</span>
                                    </div>
                                    <div class="text-gray-600">
                                        <span class="font-medium">{{ $log->field_label }}</span>: 
                                        <span class="text-red-500 line-through">{{ $log->old_value }}</span>
                                        →
                                        <span class="text-green-600 font-medium">{{ $log->new_value }}</span>
                                    </div>
                                    <div class="text-gray-400 mt-1">
                                        oleh: {{ $log->changedByUser->name ?? 'Unknown' }}
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-gray-400 text-sm text-center py-4">Belum ada perubahan tercatat</p>
                            @endif
                        </div>
                    </div>

                    <!-- Manual Check-in Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-5 text-gray-900">
                            <h3 class="text-lg font-bold mb-2">✍️ Absensi Manual</h3>
                            <p class="text-xs text-gray-500 mb-3">Untuk karyawan yang lupa absen.</p>
                            <form action="{{ route('admin.manualCheckIn') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="block text-gray-700 text-xs font-bold mb-1">Pilih Karyawan</label>
                                    <select name="user_id" class="border rounded w-full py-2 px-3 text-gray-700 text-sm" required>
                                        <option value="">-- Pilih --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="block text-gray-700 text-xs font-bold mb-1">Jam Masuk</label>
                                    <input type="time" name="check_in_time" value="08:00" class="border rounded w-full py-2 px-3 text-gray-700 text-sm" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-xs font-bold mb-1">Status</label>
                                    <select name="status" class="border rounded w-full py-2 px-3 text-gray-700 text-sm" required>
                                        <option value="present">Hadir</option>
                                        <option value="late">Terlambat</option>
                                    </select>
                                </div>
                                <button class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm" type="submit">
                                    Tambah Absensi Manual
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
