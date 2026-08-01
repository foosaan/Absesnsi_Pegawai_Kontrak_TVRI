# Activity Diagram — Revisi Lengkap PlantUML Swimlane
# Sistem Informasi Presensi dan Penggajian Pegawai Kontrak TVRI

> Revisi mengikuti panduan UML Activity Diagram: guard condition, while loop, swimlane tepat
> Format: PlantUML Activity Diagram dengan swimlane vertikal
> Render: https://www.plantuml.com/plantuml/uml/ atau plugin VS Code PlantUML

---

## Batch 1 — Fungsi 01 sampai 05

---

### AD 01 — Login Pegawai Kontrak

```plantuml
@startuml AD_01_Login_Pegawai_Kontrak
title AD 01 - Login Pegawai Kontrak

|Pegawai Kontrak|
start
:Membuka halaman Login Pegawai;

|Sistem|
:Menampilkan formulir login;

|Pegawai Kontrak|
:Memasukkan email dan password;
:Menekan tombol Login;

|Sistem|
:Memvalidasi credential;

while (Credential valid?) is ([Credential tidak valid])
  :Menampilkan pesan "Email atau kata sandi salah";
  |Pegawai Kontrak|
  :Memperbaiki email atau password;
  :Menekan tombol Login;
  |Sistem|
  :Memvalidasi credential;
endwhile ([Credential valid])

:Membuat sesi Pegawai Kontrak;
:Mengarahkan ke Dashboard Pegawai;

|Pegawai Kontrak|
:Melihat Dashboard Pegawai;
stop

@enduml
```

---

### AD 02 — Login Staff

```plantuml
@startuml AD_02_Login_Staff
title AD 02 - Login Staff

|Staff|
start
:Membuka halaman Login Staff;

|Sistem|
:Menampilkan formulir login;

|Staff|
:Memasukkan email dan password;
:Menekan tombol Login;

|Sistem|
:Memvalidasi credential;

while (Credential valid?) is ([Credential tidak valid])
  :Menampilkan pesan "Email atau kata sandi salah";
  |Staff|
  :Memperbaiki email atau password;
  :Menekan tombol Login;
  |Sistem|
  :Memvalidasi credential;
endwhile ([Credential valid])

:Memeriksa role pengguna;

if (Apakah role sesuai portal Staff?) then ([Role tidak sesuai])
  :Menolak akses ke portal Staff;
  :Menampilkan pesan "Akun tidak memiliki akses ke portal ini";
  stop
else ([Role sesuai])
  if (Role pengguna?) then ([Role staff_psdm])
    :Membuat sesi Staff PSDM;
    :Mengarahkan ke Dashboard Staff PSDM;
    |Staff|
    :Melihat Dashboard Staff PSDM;
    stop
  else ([Role staff_keuangan])
    |Sistem|
    :Membuat sesi Staff Keuangan;
    :Mengarahkan ke Dashboard Staff Keuangan;
    |Staff|
    :Melihat Dashboard Staff Keuangan;
    stop
  endif
endif

@enduml
```

---

### AD 03 — Login Admin

```plantuml
@startuml AD_03_Login_Admin
title AD 03 - Login Admin

|Admin|
start
:Membuka halaman Login Admin;

|Sistem|
:Menampilkan formulir login;

|Admin|
:Memasukkan email dan password;
:Menekan tombol Login;

|Sistem|
:Memvalidasi credential;

while (Credential valid?) is ([Credential tidak valid])
  :Menampilkan pesan "Email atau kata sandi salah";
  |Admin|
  :Memperbaiki email atau password;
  :Menekan tombol Login;
  |Sistem|
  :Memvalidasi credential;
endwhile ([Credential valid])

:Memeriksa status 2FA Admin;

if (Status 2FA?) then ([2FA belum aktif])
  :Mengarahkan ke halaman Setup 2FA;
  stop
else ([2FA sudah aktif])
  :Mengarahkan ke halaman Verifikasi 2FA;
  stop
endif

@enduml
```

---

### AD 04 — Setup Two-Factor Authentication Admin

```plantuml
@startuml AD_04_Setup_2FA_Admin
title AD 04 - Setup Two-Factor Authentication Admin

|Sistem|
start
:Membuat secret key dan QR Code;
:Menampilkan halaman Setup 2FA;

|Admin|
:Memindai QR Code menggunakan aplikasi Authenticator;
:Memasukkan kode enam digit dari aplikasi;
:Menekan tombol Aktifkan 2FA;

|Sistem|
:Memvalidasi kode 2FA;

while (Kode 2FA valid?) is ([Kode tidak valid])
  :Menampilkan pesan "Kode tidak valid, coba lagi";
  |Admin|
  :Memasukkan ulang kode enam digit;
  :Menekan tombol Aktifkan 2FA;
  |Sistem|
  :Memvalidasi kode 2FA;
endwhile ([Kode valid])

:Mengaktifkan 2FA pada akun Admin;
:Menyimpan konfigurasi 2FA;
:Membuat recovery codes;
:Menampilkan recovery codes kepada Admin;

|Admin|
:Menyimpan recovery codes di tempat aman;

|Sistem|
:Membuat sesi Admin terverifikasi;
:Mengarahkan ke Dashboard Admin;

|Admin|
:Melihat Dashboard Admin;
stop

@enduml
```

---

### AD 05 — Verifikasi Two-Factor Authentication Admin

```plantuml
@startuml AD_05_Verifikasi_2FA_Admin
title AD 05 - Verifikasi Two-Factor Authentication Admin

|Sistem|
start
:Menampilkan halaman Verifikasi 2FA;

|Admin|
:Membuka aplikasi Authenticator;
:Memasukkan kode TOTP enam digit;
:Menekan tombol Verifikasi;

|Sistem|
:Memverifikasi kode TOTP;

while (Kode TOTP valid?) is ([Kode tidak valid])
  :Menampilkan pesan "Kode TOTP tidak valid";
  |Admin|
  :Memasukkan ulang kode TOTP dari Authenticator;
  :Menekan tombol Verifikasi;
  |Sistem|
  :Memverifikasi kode TOTP;
endwhile ([Kode valid])

:Membuat sesi Admin 2FA terverifikasi;
:Mengarahkan ke Dashboard Admin;

|Admin|
:Melihat Dashboard Admin;
stop

@enduml
```

---

## Batch 2 — Fungsi 06 sampai 10

*(Menunggu instruksi untuk melanjutkan)*

---

## Batch 3 dan seterusnya

*(Menunggu instruksi untuk melanjutkan)*
