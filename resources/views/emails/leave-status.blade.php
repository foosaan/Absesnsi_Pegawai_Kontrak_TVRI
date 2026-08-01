<x-mail::message>
    # Pengajuan Cuti {{ $statusText }}

    Halo **{{ $leave->user->name }}**,

    Pengajuan cuti Anda telah diproses dengan status:

    <x-mail::panel>
        <strong style="color: {{ $statusColor }}; font-size: 18px;">{{ $statusText }}</strong>
    </x-mail::panel>

    **Detail Cuti:**

    | | |
    |:---|:---|
    | **Tipe** | {{ ucfirst(str_replace('_', ' ', $leave->type)) }} |
    | **Tanggal** | {{ $leave->start_date->format('d M Y') }} — {{ $leave->end_date->format('d M Y') }} |
    | **Alasan** | {{ $leave->reason }} |
    @if($leave->status === 'rejected' && $leave->rejection_reason)
        | **Alasan Ditolak** | {{ $leave->rejection_reason }} |
    @endif

    @if($leave->status === 'approved')
        Cuti Anda telah dicatat di sistem.
    @else
        Jika Anda memiliki pertanyaan, silakan hubungi Staff SDM.
    @endif

    Terima kasih,<br>
    **{{ config('app.name') }}**
</x-mail::message>