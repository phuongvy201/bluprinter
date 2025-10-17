@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
@elseif (trim($slot) === 'Bluprinter' || trim($slot) === config('app.name'))
<img src="{{ asset('images/logo to.png') }}" class="logo" alt="Bluprinter Logo" style="height: 75px; max-height: 75px;">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
