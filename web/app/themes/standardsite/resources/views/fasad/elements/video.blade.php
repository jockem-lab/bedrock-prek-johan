@if($video)
  <div class="{{ isset($video['class']) ? implode(' ', $video['class']) : '' }}">
    @if($video['type'] === 'embed')
      <iframe class="max-w-full fixed-ratio" width="1280" height="720"
              src="{{ $video['src'] }}"
              allow="autoplay; fullscreen"
              allowfullscreen="" frameborder="0">
      </iframe>
    @endif
    @if($video['type'] === 'selfhosted')
      <x-video poster="{{ $video['poster'] }}" :sources="$video['sources']" videoAttributes="controls"/>
    @endif
  </div>
@endif