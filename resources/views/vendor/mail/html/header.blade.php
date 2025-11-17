@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://img.freepik.com/premium-vector/vector-facebook-social-media-icon-illustration_534308-21672.jpg?semt=ais_incoming&w=740&q=80" class="logo" alt="Laravel Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
