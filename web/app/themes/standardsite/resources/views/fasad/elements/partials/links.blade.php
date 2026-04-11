@if($objectFacts['links'])
  <div class="col-span-12">
    <x-accordion title="Länkar" group="descriptions_facts" id="links">
      <ul class="media-links">
        @foreach($objectFacts['links'] as $links)
          <li><a href="{{ $links->url }}" target="_blank"><img src="@asset('images/document.svg')" alt=""><span>{{ $links->alias }}</span></a></li>
        @endforeach
      </ul>
    </x-accordion>
  </div>
@endif