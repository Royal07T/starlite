@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<x-email.logo class="logo" alt="{{ setting('_site.name') ?? config('app.name', 'Starlite') }}" />
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
