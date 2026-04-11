@props([
'href'
])
@if($href)
  <a href="{{ $href }}" {{ $attributes->merge(['class' => 'block']) }}>
    {{ $slot }}
  </a>
@else
  {{ $slot }}
@endif