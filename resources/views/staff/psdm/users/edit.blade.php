<x-app-layout title="Edit Karyawan">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('staff.psdm.users') }}" class="btn btn-secondary btn-icon">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title dark:page-title-dark">Edit Karyawan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="card dark:card-dark max-w-3xl">
        <form method="POST" action="{{ route('staff.psdm.users.update', $user) }}">
            @csrf
            @method('PUT')
            <div class="card-body space-y-6">
                {{-- Basic Info --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                               class="form-control @error('name') border-red-500 @enderror" required>
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">NIP <span class="text-red-500">*</span></label>
                        <input type="text" name="nip" value="{{ old('nip', $user->nip) }}" 
                               class="form-control @error('nip') border-red-500 @enderror" required>
                        @error('nip')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">NIK <span class="text-red-500">*</span></label>
                        <input type="text" name="nik" value="{{ old('nik', $user->nik) }}" 
                               class="form-control @error('nik') border-red-500 @enderror" placeholder="Nomor Induk Kependudukan" required>
                        @error('nik')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" rows="2" 
                               class="form-control" placeholder="Alamat lengkap">{{ old('alamat', $user->alamat) }}</textarea>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                               class="form-control @error('email') border-red-500 @enderror" required>
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" 
                               class="form-control" placeholder="Kosongkan jika tidak diubah">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipe Absensi <span class="text-red-500">*</span></label>
                        <select name="attendance_type" class="form-control" required>
                            <option value="normal" {{ old('attendance_type', $user->attendance_type) == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="shift" {{ old('attendance_type', $user->attendance_type) == 'shift' ? 'selected' : '' }}>Shift</option>
                        </select>
                    </div>
                </div>

                {{-- Master Data Fields --}}
                @if(isset($masterDataTypes) && $masterDataTypes->count() > 0)
                <hr class="dark:border-slate-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Data Kepegawaian</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach($masterDataTypes as $slug => $type)
                    @php
                        $currentValue = null;
                        if($slug == 'jabatan') $currentValue = $user->jabatan;
                        elseif($slug == 'bagian') $currentValue = $user->bagian;
                        elseif($slug == 'status-pegawai') $currentValue = $user->status_pegawai;
                        elseif($slug == 'status_oprasional') $currentValue = $user->status_operasional;
                    @endphp
                    <div class="form-group">
                        <label class="form-label">{{ $type->name }}</label>
                        <select name="master_data[{{ $slug }}]" class="form-control">
                            <option value="">-- Pilih {{ $type->name }} --</option>
                            @foreach($type->values as $value)
                                <option value="{{ $value->value }}" {{ strtolower(old("master_data.{$slug}", $currentValue ?? '')) == strtolower($value->value) ? 'selected' : '' }}>
                                    {{ $value->value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="card-body border-t dark:border-slate-700 flex justify-end gap-3">
                <a href="{{ route('staff.psdm.users') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
