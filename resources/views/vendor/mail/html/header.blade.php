@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel' || trim($slot) === config('app.name'))
<img src="{{ asset('assets/img/logo tvri.png') }}" class="logo" alt="Logo TVRI" style="height: 50px; width: auto;">
<span style="display: block; margin-top: 8px; font-size: 18px; font-weight: bold; color: #333;">TVRI Presensi</span>
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
