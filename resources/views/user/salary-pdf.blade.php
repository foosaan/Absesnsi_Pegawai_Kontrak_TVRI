<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $salary->period }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 14px;
            font-weight: normal;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        .info-label {
            display: table-cell;
            width: 120px;
        }
        .info-value {
            display: table-cell;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 4px 0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .border-top {
            border-top: 1px solid #333;
        }
        .border-bottom {
            border-bottom: 1px solid #333;
        }
        .total-row {
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
        .final-salary {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #333;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
        }
        .signature {
            margin-top: 60px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TVRI STASIUN D.I YOGYAKARTA</h1>
        <h2>SLIP PENERIMAAN GAJI DAN POTONGAN</h2>
    </div>

    <div class="section">
        <div class="info-row">
            <span class="info-label">BULAN</span>
            <span class="info-value">: {{ strtoupper($salary->period) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">NAMA</span>
            <span class="info-value">: {{ $salary->user->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">GOLONGAN</span>
            <span class="info-value">: {{ $salary->user->status_pegawai ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">NIP</span>
            <span class="info-value">: {{ $salary->user->nip ?? '-' }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">PENERIMAAN</div>
        <table>
            <tr>
                <td>Gaji Pokok</td>
                <td class="text-center">:</td>
                <td class="text-right">Rp</td>
                <td class="text-right" style="width: 100px;">{{ number_format($salary->base_salary, 0, ',', '.') }}</td>
            </tr>
            <tr class="border-top">
                <td class="bold">Gaji Bersih</td>
                <td class="text-center">:</td>
                <td class="text-right bold">Rp</td>
                <td class="text-right bold">{{ number_format($salary->base_salary, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Potongan KPPN</td>
                <td class="text-center">:</td>
                <td class="text-right">Rp</td>
                <td class="text-right">{{ number_format($salary->potongan_kppn, 0, ',', '.') }}</td>
            </tr>
            <tr class="border-top">
                <td class="bold">Gaji Bersih</td>
                <td class="text-center">:</td>
                <td class="text-right bold">Rp</td>
                <td class="text-right bold">{{ number_format($salary->base_salary - $salary->potongan_kppn, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">POTONGAN INTERN</div>
        <table>
            <tr>
                <td>Simpanan Wajib</td>
                <td class="text-center">:</td>
                <td class="text-right">Rp</td>
                <td class="text-right" style="width: 100px;">{{ number_format($salary->simpanan_wajib, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Kredit Uang</td>
                <td class="text-center">:</td>
                <td class="text-right">Rp</td>
                <td class="text-right">{{ number_format($salary->kredit_uang, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Kredit Toko</td>
                <td class="text-center">:</td>
                <td class="text-right">Rp</td>
                <td class="text-right">{{ number_format($salary->kredit_toko, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Dharma Wanita</td>
                <td class="text-center">:</td>
                <td class="text-right">Rp</td>
                <td class="text-right">{{ number_format($salary->dharma_wanita, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>BPJS</td>
                <td class="text-center">:</td>
                <td class="text-right">Rp</td>
                <td class="text-right">{{ number_format($salary->bpjs, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td class="bold">Jumlah Potongan Intern</td>
                <td class="text-center">:</td>
                <td class="text-right bold">Rp</td>
                <td class="text-right bold">{{ number_format($salary->total_potongan_intern, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="final-salary">
        <table>
            <tr>
                <td class="bold">Jumlah Gaji Di terima</td>
                <td class="text-center">:</td>
                <td class="text-right bold">-Rp</td>
                <td class="text-right bold" style="width: 100px;">{{ number_format($salary->final_salary, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Yogyakarta, {{ now()->format('d F Y') }}</p>
        <p>PPABP</p>
        <div class="signature">
            <p>____________________</p>
        </div>
    </div>
</body>
</html>
