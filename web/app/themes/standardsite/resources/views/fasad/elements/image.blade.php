@if($image)
  <div class="{{ isset($image['class']) ? implode(' ', $image['class']) : '' }}">
    <img
        src="{{ $image['src'] }}"
        srcset="{{ $image['srcset'] }}"
        sizes="{{ $image['sizes'] }}"
        loading="lazy"
    >
  </div>
@endif