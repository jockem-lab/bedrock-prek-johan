<div class="accordion mb-2 pt-2 pb-4 accordion-pre-init" data-accordion-group="{{ $group }}" {{ $attributes }} data-expanded="{{ $expanded }}">
  <div class="accordion-toggle flex items-center justify-between" data-accordion-target="{{ $target }}">
    <h4 class="m-0 uppercase tracking-widest bold">{!! $title !!}</h4>
    <span class="chevron down"></span>
  </div>
  <div class="accordion-content wysiwyg pt-4" data-accordion-anchor="{{ $target }}">
    {!! $content ?? $slot !!}
  </div>
</div>