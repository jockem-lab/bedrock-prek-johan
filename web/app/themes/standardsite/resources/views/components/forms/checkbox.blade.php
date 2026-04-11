@props([
    'label',
    'name',
    'value',
])
<label class="flex checkmark-wrapper w-auto items-start">
  <input
      type="checkbox"
      name="{{ $name }}"
      value="{{ $value }}"
      {{ $attributes }}
  />
  <span class="checkmark"></span>
  <span>{!! $label !!}</span>
</label>
